package id.ac.its.mybook;

import static id.ac.its.mybook.Backend.*;

import id.ac.its.mybook.LibraryService.Actor;
import id.ac.its.mybook.LibraryService.Validation;
import jakarta.servlet.*;
import jakarta.servlet.http.*;
import java.io.*;
import java.sql.*;
import java.util.*;
import java.util.logging.*;

public final class AppServlet extends HttpServlet {

    private static final Logger LOG = Logger.getLogger(AppServlet.class.getName());
    private static final Map<String, long[]> ATTEMPTS = new java.util.concurrent.ConcurrentHashMap<>();

    private static String path(HttpServletRequest req) {
        return Optional.ofNullable(req.getPathInfo()).orElse("/dashboard");
    }

    private static String p(HttpServletRequest req, String key) {
        return Optional.ofNullable(req.getParameter(key)).orElse("");
    }

    private static Actor actor(HttpServletRequest req) {
        return (Actor) req.getSession().getAttribute("actor");
    }

    private void view(HttpServletRequest req, HttpServletResponse res, String name)
        throws ServletException, IOException
    {
        req.getRequestDispatcher("/WEB-INF/views/" + name + ".jsp").forward(req, res);
    }

    @Override
    protected void service(HttpServletRequest req, HttpServletResponse res)
        throws ServletException, IOException
    {
        req.setCharacterEncoding("UTF-8");
        res.setCharacterEncoding("UTF-8");
        res.setHeader("Cache-Control", "no-store");
        res.setHeader("X-Content-Type-Options", "nosniff");
        res.setHeader("X-Frame-Options", "DENY");
        res.setHeader(
            "Content-Security-Policy",
            "default-src 'self'; style-src 'self'; img-src 'self' data:; script-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'"
        );
        req.getSession().setMaxInactiveInterval(1800);
        if (req.getSession().getAttribute("csrf") == null) req.getSession().setAttribute(
            "csrf",
            Security.token()
        );
        if (
            req.getMethod().equals("POST") &&
            !Objects.equals(req.getSession().getAttribute("csrf"), req.getParameter("csrf"))
        ) {
            res.sendError(403, "The form session has expired. Reload the page.");
            return;
        }
        if (!req.getServletPath().equals("/auth") && actor(req) == null) {
            res.sendRedirect(req.getContextPath() + "/auth/login");
            return;
        }
        Backend.bind(req.getSession());
        try {
            if (actor(req) != null) {
                try {
                    Actor user = Backend.current(actor(req));
                    if (user == null) {
                        req.getSession().invalidate();
                        res.sendRedirect(req.getContextPath() + "/auth/login");
                        return;
                    }
                    req.getSession().setAttribute("actor", user);
                } catch (Backend.Failure e) {
                    if (e.status == 401) {
                        req.getSession().invalidate();
                        res.sendRedirect(req.getContextPath() + "/auth/login");
                        return;
                    }
                    req.setAttribute("error", e.getMessage());
                    res.setStatus(503);
                    view(req, res, "error");
                    return;
                } catch (SQLException e) {
                    throw new ServletException(e);
                }
            }
            super.service(req, res);
        } finally {
            Backend.clear();
        }
    }

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse res)
        throws ServletException, IOException
    {
        try {
            if (req.getServletPath().equals("/auth")) {
                if (!path(req).equals("/login")) {
                    res.sendError(404);
                    return;
                }
                if (actor(req) != null) {
                    res.sendRedirect(req.getContextPath() + "/app/dashboard");
                    return;
                }
                view(req, res, "login");
                return;
            }
            String path = path(req);
            if (path.equals("/favorites")) {
                if (!Backend.LARAVEL || !actor(req).member()) {
                    res.sendError(403);
                    return;
                }
                int page = pageNumber(p(req, "page"));
                var rows = Backend.favorites(page);
                req.setAttribute("page", "favorites");
                req.setAttribute("pageNumber", page);
                req.setAttribute("hasNext", rows.size() > 12);
                req.setAttribute("rows", rows.subList(0, Math.min(12, rows.size())));
                view(req, res, "favorites");
                return;
            }
            if (path.equals("/dashboard") || path.equals("/reports")) {
                req.setAttribute("data", dashboard(actor(req)));
                req.setAttribute("page", path.substring(1));
                view(req, res, path.equals("/reports") ? "reports" : "dashboard");
                return;
            }
            String[] parts = path.substring(1).split("/");
            var m = Module.ALL.get(parts[0]);
            if (
                m == null ||
                parts.length > 2 ||
                (parts.length == 2 && !Set.of("new", "edit", "detail").contains(parts[1]))
            ) {
                res.sendError(404);
                return;
            }
            if (!allowed(actor(req), m)) {
                res.sendError(403);
                return;
            }
            req.setAttribute("module", m);
            req.setAttribute("page", m.key());
            if (parts.length == 2) {
                String action = parts[1];
                if (!action.equals("detail") && !writable(actor(req), m)) {
                    res.sendError(403);
                    return;
                }
                var row = action.equals("new")
                    ? new LinkedHashMap<String, Object>()
                    : get(m, Long.parseLong(p(req, "id")), actor(req));
                if (action.equals("new")) {
                    row.put("borrowed_on", java.time.LocalDate.now());
                    row.put("due_on", java.time.LocalDate.now().plusDays(14));
                    row.put("reserved_on", java.time.LocalDate.now());
                    row.put("status", "pending");
                    if (actor(req).member()) row.put("member_id", actor(req).id());
                    if (m.key().equals("reservations") && p(req, "book_id").matches("[0-9]+")) row.put(
                        "book_id",
                        Long.parseLong(p(req, "book_id"))
                    );
                }
                if (Backend.LARAVEL && action.equals("detail") && m.key().equals("books")) req.setAttribute(
                    "community",
                    Backend.community(
                        Long.parseLong(row.get("id").toString()),
                        pageNumber(p(req, "review_page"))
                    )
                );
                req.setAttribute("row", row);
                req.setAttribute("detail", action.equals("detail"));
                req.setAttribute("options", options(m));
                view(req, res, "form");
            } else {
                int page = 1;
                try {
                    page = Math.max(1, Math.min(100000, Integer.parseInt(p(req, "page"))));
                } catch (NumberFormatException ignored) {}
                var rows = list(m, actor(req), p(req, "q"), page);
                req.setAttribute("hasNext", rows.size() > 12);
                req.setAttribute("rows", rows.subList(0, Math.min(12, rows.size())));
                req.setAttribute("pageNumber", page);
                view(req, res, "list");
            }
        } catch (Validation | NumberFormatException e) {
            req.setAttribute("error", e instanceof Validation ? e.getMessage() : "Invalid record ID.");
            res.setStatus(404);
            view(req, res, "error");
        } catch (SQLException e) {
            LOG.log(Level.SEVERE, "Database read failed", e);
            res.setStatus(500);
            req.setAttribute("error", "Unable to load the data. Please try again.");
            view(req, res, "error");
        }
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse res)
        throws ServletException, IOException
    {
        if (req.getServletPath().equals("/auth")) {
            if (path(req).equals("/logout")) {
                req.getSession().invalidate();
                res.sendRedirect(req.getContextPath() + "/auth/login");
                return;
            }
            if (!path(req).equals("/login")) {
                res.sendError(404);
                return;
            }
            try {
                String ip = req.getRemoteAddr();
                long now = System.currentTimeMillis();
                if (ATTEMPTS.size() > 10000) ATTEMPTS.entrySet().removeIf(
                    e -> now - e.getValue()[1] > 900000
                );
                long[] attempts = ATTEMPTS.compute(ip, (k, v) ->
                    v == null || now - v[1] > 900000 ? new long[] { 0, now } : v
                );
                synchronized (attempts) {
                    if (attempts[0] >= 10) {
                        res.setStatus(429);
                        req.setAttribute("error", "Too many login attempts. Try again in 15 minutes.");
                        view(req, res, "login");
                        return;
                    }
                    attempts[0]++;
                }
                Actor user = Backend.login(
                    p(req, "email").trim().toLowerCase(Locale.ROOT),
                    p(req, "password"),
                    req.getSession()
                );
                if (user == null) {
                    res.setStatus(401);
                    req.setAttribute("error", "The email address or password is incorrect.");
                    view(req, res, "login");
                    return;
                }
                ATTEMPTS.remove(ip);
                req.changeSessionId();
                req.getSession().setAttribute("csrf", Security.token());
                req.getSession().setAttribute("actor", user);
                res.sendRedirect(req.getContextPath() + "/app/dashboard");
            } catch (SQLException | Validation e) {
                res.setStatus(503);
                req.setAttribute(
                    "error",
                    e instanceof Backend.Failure
                        ? e.getMessage()
                        : "Unable to sign in. Check the backend connection and your credentials."
                );
                view(req, res, "login");
            }
            return;
        }
        if (path(req).startsWith("/community/")) {
            communityPost(req, res);
            return;
        }
        String[] parts = path(req).substring(1).split("/");
        var m = Module.ALL.get(parts[0]);
        if (m == null || parts.length != 2 || !Set.of("save", "delete").contains(parts[1])) {
            res.sendError(404);
            return;
        }
        if (!writable(actor(req), m)) {
            res.sendError(403);
            return;
        }
        Map<String, String> input = new LinkedHashMap<>();
        req.getParameterMap().forEach((k, v) -> input.put(k, v[0]));
        Long id = null;
        try {
            if (!p(req, "id").isBlank()) id = Long.valueOf(p(req, "id"));
            if (parts[1].equals("delete")) {
                if (id == null) throw new Validation("A record ID is required.");
                delete(m, id, actor(req));
            } else save(m, id, input, actor(req));
            req.getSession().setAttribute(
                "flash",
                m.singular() +
                    " successfully " +
                    (parts[1].equals("delete") ? "deleted" : id == null ? "created" : "updated") +
                    "."
            );
            res.sendRedirect(req.getContextPath() + "/app/" + m.key());
        } catch (SQLException | Validation | NumberFormatException e) {
            String message =
                e instanceof Validation
                    ? e.getMessage()
                    : e instanceof SQLException sql &&
                        sql.getSQLState() != null &&
                        sql.getSQLState().startsWith("23")
                      ? "Duplicate or referenced data. Check the email address, ISBN, name, and related records."
                      : "Unable to save the record. Check the fields and try again.";
            if (e instanceof SQLException) LOG.log(Level.WARNING, "Database write rejected", e);
            if (parts[1].equals("delete")) {
                req.getSession().setAttribute("flashError", message);
                res.sendRedirect(req.getContextPath() + "/app/" + m.key());
                return;
            }
            input.remove("password");
            input.remove("csrf");
            Map<String, Object> row = new LinkedHashMap<>(input);
            if (id != null) row.put("id", id);
            if (m.key().equals("loans") && id != null) {
                try {
                    var existing = get(m, id, actor(req));
                    row.put("reservation_id", existing.get("reservation_id"));
                } catch (Exception ignored) {}
            }
            req.setAttribute("row", row);
            req.setAttribute("error", message);
            req.setAttribute("module", m);
            req.setAttribute("page", m.key());
            try {
                req.setAttribute("options", options(m));
            } catch (SQLException ex) {
                throw new ServletException(ex);
            }
            res.setStatus(422);
            view(req, res, "form");
        }
    }

    private static int pageNumber(String raw) {
        try {
            return Math.max(1, Math.min(100000, Integer.parseInt(raw)));
        } catch (NumberFormatException e) {
            return 1;
        }
    }

    private void communityPost(HttpServletRequest req, HttpServletResponse res) throws IOException {
        if (!Backend.LARAVEL || !actor(req).member()) {
            res.sendError(403);
            return;
        }
        if (!p(req, "book_id").matches("[1-9][0-9]{0,17}")) {
            res.sendError(400);
            return;
        }
        long book = Long.parseLong(p(req, "book_id"));
        String action = path(req);
        if (
            !Set.of("/community/favorite", "/community/review", "/community/delete-review").contains(action)
        ) {
            res.sendError(404);
            return;
        }
        String destination =
            req.getContextPath() +
            (p(req, "return_to").equals("favorites")
                ? "/app/favorites"
                : "/app/books/detail?id=" + book + "#community");
        try {
            String message;
            if (action.equals("/community/favorite")) {
                if (!Set.of("add", "remove").contains(p(req, "intent"))) {
                    res.sendError(400);
                    return;
                }
                message = Backend.favorite(book, p(req, "intent").equals("remove"));
            } else {
                boolean deleting = action.equals("/community/delete-review");
                if (!deleting && !Set.of("create", "update").contains(p(req, "mode"))) {
                    res.sendError(400);
                    return;
                }
                String method = deleting ? "DELETE" : p(req, "mode").equals("update") ? "PUT" : "POST";
                message = Backend.review(
                    book,
                    method,
                    Map.of("rating", p(req, "rating"), "comment", p(req, "comment"))
                );
                req.getSession().removeAttribute("reviewDraft");
            }
            req.getSession().setAttribute("flash", message);
        } catch (SQLException | Validation e) {
            req.getSession().setAttribute(
                "flashError",
                e instanceof Backend.Failure failure && failure.status >= 500
                    ? "The backend is unavailable. Please try again."
                    : e.getMessage()
            );
            if (action.equals("/community/review")) req.getSession().setAttribute(
                "reviewDraft",
                Map.of(
                    "book_id",
                    book,
                    "rating",
                    p(req, "rating"),
                    "comment",
                    p(req, "comment").substring(0, Math.min(1000, p(req, "comment").length()))
                )
            );
        }
        res.sendRedirect(destination);
    }
}
