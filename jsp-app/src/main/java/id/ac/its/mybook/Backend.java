package id.ac.its.mybook;

import com.google.gson.*;
import id.ac.its.mybook.LibraryService.Actor;
import jakarta.servlet.http.HttpSession;
import java.net.*;
import java.net.http.*;
import java.nio.charset.StandardCharsets;
import java.sql.SQLException;
import java.time.Duration;
import java.util.*;

/** JSP's server-side API adapter. Browser sessions never receive Laravel tokens. */
public final class Backend {

    private Backend() {}

    public static final boolean LARAVEL = !"java".equalsIgnoreCase(
        System.getenv().getOrDefault("MYBOOK_BACKEND", "laravel")
    );
    private static final String API = System.getenv()
        .getOrDefault("MYBOOK_API_URL", "http://127.0.0.1:8000/api/jsp")
        .replaceAll("/+$", "");
    private static final HttpClient HTTP = HttpClient.newBuilder()
        .connectTimeout(Duration.ofSeconds(5))
        .build();
    private static final Gson JSON = new GsonBuilder()
        .setObjectToNumberStrategy(ToNumberPolicy.LONG_OR_DOUBLE)
        .create();
    private static final ThreadLocal<String> TOKEN = new ThreadLocal<>();

    public static final class Failure extends SQLException {

        public final int status;

        Failure(int status, String message) {
            super(message);
            this.status = status;
        }
    }

    public static void bind(HttpSession session) {
        TOKEN.set((String) session.getAttribute("apiToken"));
    }

    public static void clear() {
        TOKEN.remove();
    }

    private static JsonElement call(String method, String path, Object body) throws SQLException {
        try {
            var builder = HttpRequest.newBuilder(URI.create(API + path))
                .timeout(Duration.ofSeconds(20))
                .header("Accept", "application/json");
            if (TOKEN.get() != null) builder.header("Authorization", "Bearer " + TOKEN.get());
            var publisher =
                body == null
                    ? HttpRequest.BodyPublishers.noBody()
                    : HttpRequest.BodyPublishers.ofString(JSON.toJson(body));
            if (body != null) builder.header("Content-Type", "application/json");
            var response = HTTP.send(
                builder.method(method, publisher).build(),
                HttpResponse.BodyHandlers.ofString()
            );
            JsonElement json;
            try {
                json = JsonParser.parseString(response.body());
            } catch (JsonParseException e) {
                throw new Failure(502, "The Laravel backend returned an invalid JSON response.");
            }
            if (response.statusCode() >= 400) {
                String message = "The Laravel backend could not process the request.";
                if (json.isJsonObject() && json.getAsJsonObject().has("message")) message = json
                    .getAsJsonObject()
                    .get("message")
                    .getAsString();
                if (response.statusCode() == 422) throw new LibraryService.Validation(message);
                throw new Failure(response.statusCode(), message);
            }
            return json;
        } catch (java.io.IOException e) {
            throw new Failure(503, "The Laravel backend is unavailable. Start Laravel on port 8000.");
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
            throw new Failure(503, "The backend connection was interrupted.");
        }
    }

    @SuppressWarnings("unchecked")
    private static Map<String, Object> object(JsonElement json) {
        return JSON.fromJson(json, Map.class);
    }

    @SuppressWarnings("unchecked")
    private static List<Map<String, Object>> array(JsonElement json) {
        return JSON.fromJson(json, List.class);
    }

    private static Actor identity(Map<String, Object> row) {
        return new Actor(
            ((Number) row.get("id")).longValue(),
            row.get("name").toString(),
            row.get("role").toString()
        );
    }

    public static Actor login(String email, String password, HttpSession session) throws SQLException {
        if (LARAVEL) {
            try {
                var result = call(
                    "POST",
                    "/login",
                    Map.of("email", email, "password", password)
                ).getAsJsonObject();
                session.setAttribute("apiToken", result.get("token").getAsString());
                return identity(object(result.get("user")));
            } catch (Failure e) {
                if (e.status == 401) return null;
                throw e;
            }
        }
        try (var c = Database.open()) {
            var rows = Database.query(c, "SELECT * FROM users WHERE email=?", email);
            if (
                rows.isEmpty() || !Security.verify(password, rows.get(0).get("password_hash").toString())
            ) return null;
            return identity(rows.get(0));
        }
    }

    public static Actor current(Actor actor) throws SQLException {
        if (LARAVEL) return identity(object(call("GET", "/me", null)));
        try (var c = Database.open()) {
            var rows = Database.query(c, "SELECT id,name,role FROM users WHERE id=?", actor.id());
            return rows.isEmpty() ? null : identity(rows.get(0));
        }
    }

    public static boolean allowed(Actor a, Module m) {
        return LibraryService.allowed(a, m);
    }

    public static boolean writable(Actor a, Module m) {
        return LibraryService.writable(a, m);
    }

    public static Map<String, Object> dashboard(Actor a) throws SQLException {
        return LARAVEL ? object(call("GET", "/dashboard", null)) : LibraryService.dashboard(a);
    }

    public static List<Map<String, Object>> list(Module m, Actor a, String q, int page) throws SQLException {
        return LARAVEL
            ? array(
                  call(
                      "GET",
                      "/" + m.key() + "?page=" + page + "&q=" + URLEncoder.encode(q, StandardCharsets.UTF_8),
                      null
                  )
              )
            : LibraryService.list(m, a, q, page);
    }

    public static Map<String, Object> get(Module m, long id, Actor a) throws SQLException {
        if (!LARAVEL) return LibraryService.get(m, id, a);
        try {
            return object(call("GET", "/" + m.key() + "/" + id, null));
        } catch (Failure e) {
            if (e.status == 404) throw new LibraryService.Validation(
                "The record was not found or does not belong to you."
            );
            throw e;
        }
    }

    @SuppressWarnings("unchecked")
    public static Map<String, List<Map<String, Object>>> options(Module m) throws SQLException {
        return LARAVEL
            ? JSON.fromJson(call("GET", "/" + m.key() + "/options", null), Map.class)
            : LibraryService.options(m);
    }

    public static void save(Module m, Long id, Map<String, String> input, Actor a) throws SQLException {
        if (LARAVEL) call(id == null ? "POST" : "PUT", "/" + m.key() + (id == null ? "" : "/" + id), input);
        else LibraryService.save(m, id, input, a);
    }

    public static void delete(Module m, long id, Actor a) throws SQLException {
        if (LARAVEL) call("DELETE", "/" + m.key() + "/" + id, null);
        else LibraryService.delete(m, id, a);
    }

    public static Map<String, Object> community(long book, int page) throws SQLException {
        return object(call("GET", "/books/" + book + "/community?page=" + page, null));
    }

    public static List<Map<String, Object>> favorites(int page) throws SQLException {
        return array(call("GET", "/favorites?page=" + page, null));
    }

    public static String favorite(long book, boolean remove) throws SQLException {
        return object(call(remove ? "DELETE" : "POST", "/books/" + book + "/favorite", null))
            .get("message")
            .toString();
    }

    public static String review(long book, String method, Map<String, String> data) throws SQLException {
        return object(call(method, "/books/" + book + "/review", data))
            .get("message")
            .toString();
    }
}
