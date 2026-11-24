package id.ac.its.mybook;

import static id.ac.its.mybook.Database.*;

import java.sql.*;
import java.time.*;
import java.util.*;

public final class LibraryService {

    public record Actor(long id, String name, String role) {
        public boolean admin() {
            return role.equals("admin");
        }

        public boolean member() {
            return role.equals("member");
        }
    }

    public static final class Validation extends RuntimeException {

        public Validation(String message) {
            super(message);
        }
    }

    public static boolean allowed(Actor a, Module m) {
        return (
            a.admin() ||
            (!a.member() && !m.account()) ||
            (a.member() && Set.of("books", "reservations", "loans").contains(m.key()))
        );
    }

    public static boolean writable(Actor a, Module m) {
        return allowed(a, m) && (!a.member() || m.key().equals("reservations"));
    }

    public static List<Map<String, Object>> list(Module m, Actor a, String q, int page) throws SQLException {
        try (var c = open()) {
            String sql = m.select();
            List<Object> args = new ArrayList<>();
            String where = m.account() ? " AND " : " WHERE ";
            if (a.member() && !m.key().equals("books")) {
                sql += where + (m.key().equals("loans") ? "l" : "r") + ".member_id=?";
                args.add(a.id());
                where = " AND ";
            }
            if (q != null && !q.isBlank()) {
                sql += where + "LOWER(" + m.search() + ") LIKE ? ESCAPE '!'";
                args.add(
                    "%" +
                        q.toLowerCase(Locale.ROOT).replace("!", "!!").replace("%", "!%").replace("_", "!_") +
                        "%"
                );
            }
            sql +=
                " ORDER BY " +
                (m.key().equals("books")
                    ? "b."
                    : m.key().equals("loans")
                      ? "l."
                      : m.key().equals("reservations")
                        ? "r."
                        : "") +
                "id DESC LIMIT 13 OFFSET ?";
            args.add((Math.max(1, page) - 1) * 12);
            return query(c, sql, args.toArray());
        }
    }

    public static Map<String, Object> get(Connection c, Module m, long id, Actor a) throws SQLException {
        String sql = "SELECT * FROM " + m.table() + " WHERE id=?";
        if (m.account()) sql += " AND role='" + m.role() + "'";
        var rows = query(c, sql, id);
        if (rows.isEmpty()) throw new Validation("Record not found.");
        var row = rows.get(0);
        if (
            a.member() && !m.key().equals("books") && ((Number) row.get("member_id")).longValue() != a.id()
        ) throw new Validation("You do not have access to this record.");
        row.remove("password_hash");
        return row;
    }

    public static Map<String, Object> get(Module m, long id, Actor a) throws SQLException {
        try (var c = open()) {
            return get(c, m, id, a);
        }
    }

    public static Map<String, List<Map<String, Object>>> options(Module m) throws SQLException {
        var result = new HashMap<String, List<Map<String, Object>>>();
        try (var c = open()) {
            for (var f : m.fields())
                if (f.type().equals("select")) {
                    String source = f.options();
                    if (source.contains(",")) {
                        result.put(
                            f.name(),
                            Arrays.stream(source.split(","))
                                .map(s -> Map.<String, Object>of("id", s, "name", label(s)))
                                .toList()
                        );
                        continue;
                    }
                    String sql = switch (source) {
                        case "members" -> "SELECT id,name FROM users WHERE role='member' ORDER BY name";
                        case "books" -> "SELECT id,title AS name FROM books ORDER BY title";
                        case "ready_reservations" -> "SELECT r.id,b.title || ' · ' || u.name AS name FROM reservations r JOIN books b ON b.id=r.book_id JOIN users u ON u.id=r.member_id WHERE r.status='ready' ORDER BY r.id DESC";
                        default -> "SELECT id,name FROM " + source + " ORDER BY name";
                    };
                    result.put(f.name(), query(c, sql));
                }
        }
        return result;
    }

    public static String label(Object value) {
        return switch (String.valueOf(value)) {
            case "pending" -> "Pending";
            case "ready" -> "Ready for pickup";
            case "picked_up" -> "Collected";
            case "cancelled" -> "Cancelled";
            case "admin" -> "Administrator";
            case "librarian" -> "Librarian";
            case "member" -> "Member";
            default -> String.valueOf(value);
        };
    }

    public static void save(Module m, Long id, Map<String, String> input, Actor a) throws SQLException {
        if (!writable(a, m)) throw new Validation("You do not have permission to edit this record.");
        try (var c = open()) {
            c.setAutoCommit(false);
            try {
                // All inventory writes acquire the same locks in the same order. This also
                // protects edits that move a transaction from one book to another.
                if (Set.of("books", "loans", "reservations").contains(m.key())) query(
                    c,
                    "SELECT id FROM books ORDER BY id FOR UPDATE"
                );
                Map<String, Object> old = id == null ? Map.of() : get(c, m, id, a);
                Map<String, Object> values = new LinkedHashMap<>();
                for (var f : m.fields()) {
                    String raw = input.getOrDefault(f.name(), "").trim();
                    if (a.member() && f.name().equals("member_id")) raw = String.valueOf(a.id());
                    if (a.member() && f.name().equals("status")) raw = id == null ? "pending" : "cancelled";
                    if (f.required() && raw.isEmpty()) throw new Validation(f.label() + " is required.");
                    int max = switch (f.name()) {
                        case "description" -> m.key().equals("books") ? 2000 : 1000;
                        case "biography", "notes" -> 1000;
                        case "address" -> 500;
                        case "email" -> 180;
                        case "title" -> 200;
                        case "phone", "isbn" -> 30;
                        case "shelf" -> 40;
                        case "city", "country" -> 100;
                        default -> 160;
                    };
                    if (raw.length() > max) throw new Validation(
                        f.label() + " is too long (maximum " + max + " characters)."
                    );
                    if (f.type().equals("password")) {
                        if (raw.isEmpty() && id != null) continue;
                        if (raw.length() < 12) throw new Validation(
                            "The password must contain at least 12 characters."
                        );
                        values.put("password_hash", Security.hash(raw));
                        continue;
                    }
                    if (f.type().equals("email")) {
                        raw = raw.toLowerCase(Locale.ROOT);
                        if (!raw.matches("[^\\s@]+@[^\\s@]+\\.[^\\s@]+")) throw new Validation(
                            "Enter a valid email address."
                        );
                    }
                    Object value = raw;
                    try {
                        if (f.type().equals("date")) value = raw.isEmpty() ? null : LocalDate.parse(raw);
                        if (f.type().equals("number")) value = Integer.valueOf(raw);
                        if (f.type().equals("select")) {
                            if (f.options().contains(",")) {
                                if (
                                    !Arrays.asList(f.options().split(",")).contains(raw)
                                ) throw new Validation("Invalid status.");
                            } else value = raw.isEmpty() ? null : Long.valueOf(raw);
                        }
                    } catch (IllegalArgumentException | java.time.format.DateTimeParseException e) {
                        throw new Validation(f.label() + " is invalid.");
                    }
                    values.put(f.name(), value);
                }
                if (m.account()) values.put("role", m.role());
                if (a.member() && id != null) {
                    if (!Set.of("pending", "ready").contains(old.get("status"))) throw new Validation(
                        "This reservation cannot be cancelled."
                    );
                    values.clear();
                    values.put("status", "cancelled");
                }
                if (m.key().equals("books")) {
                    int stock = (Integer) values.get("stock"),
                        year = (Integer) values.get("published_year");
                    if (
                        stock < 0 || stock > 100000 || year < 1000 || year > Year.now().getValue() + 1
                    ) throw new Validation("The stock or publication year is outside the allowed range.");
                }
                if (Set.of("loans", "reservations").contains(m.key())) {
                    Object member = values.getOrDefault("member_id", old.get("member_id"));
                    if (
                        number(c, "SELECT COUNT(*) FROM users WHERE id=? AND role='member'", member) != 1
                    ) throw new Validation("Select a valid member account.");
                }
                if (m.key().equals("reservations")) {
                    if (id != null && old.get("status").equals("picked_up")) throw new Validation(
                        "Collected reservations are locked by their loan records."
                    );
                    if (
                        values.get("reserved_on") instanceof LocalDate date && date.isAfter(LocalDate.now())
                    ) throw new Validation("The reservation date cannot be in the future.");
                    if (
                        id == null &&
                        number(
                            c,
                            "SELECT COUNT(*) FROM reservations WHERE member_id=? AND book_id=? AND status IN ('pending','ready')",
                            values.get("member_id"),
                            values.get("book_id")
                        ) > 0
                    ) throw new Validation("This member already has an active reservation for this book.");
                }
                if (m.key().equals("loans")) {
                    var borrowed = (LocalDate) values.get("borrowed_on");
                    var due = (LocalDate) values.get("due_on");
                    var returned = (LocalDate) values.get("returned_on");
                    if (
                        borrowed.isAfter(LocalDate.now()) ||
                        due.isBefore(borrowed) ||
                        (returned != null &&
                            (returned.isBefore(borrowed) || returned.isAfter(LocalDate.now())))
                    ) throw new Validation("Check the borrowing, due, and return dates.");
                    Object reservation = values.get("reservation_id");
                    if (
                        id != null && !Objects.equals(old.get("reservation_id"), reservation)
                    ) throw new Validation("The reservation linked to this loan cannot be changed.");
                    if (reservation != null) {
                        var found = query(c, "SELECT * FROM reservations WHERE id=? FOR UPDATE", reservation);
                        if (found.isEmpty()) throw new Validation("Reservation not found.");
                        var r = found.get(0);
                        if (
                            !Objects.equals(r.get("member_id"), values.get("member_id")) ||
                            !Objects.equals(r.get("book_id"), values.get("book_id")) ||
                            !(id == null ? "ready" : "picked_up").equals(r.get("status"))
                        ) throw new Validation(
                            "The reservation must be ready for pickup and match the member and book."
                        );
                        if (
                            borrowed.isBefore(((java.sql.Date) r.get("reserved_on")).toLocalDate())
                        ) throw new Validation(
                            "The borrowing date cannot be earlier than the reservation date."
                        );
                        if (id == null) execute(
                            c,
                            "UPDATE reservations SET status='picked_up' WHERE id=?",
                            reservation
                        );
                    }
                }
                String columns = String.join(",", values.keySet());
                List<Object> args = new ArrayList<>(values.values());
                if (id == null) execute(
                    c,
                    "INSERT INTO " +
                        m.table() +
                        " (" +
                        columns +
                        ") VALUES (" +
                        String.join(",", Collections.nCopies(values.size(), "?")) +
                        ")",
                    args.toArray()
                );
                else {
                    args.add(id);
                    if (
                        execute(
                            c,
                            "UPDATE " +
                                m.table() +
                                " SET " +
                                String.join(
                                    ",",
                                    values
                                        .keySet()
                                        .stream()
                                        .map(k -> k + "=?")
                                        .toList()
                                ) +
                                " WHERE id=?",
                            args.toArray()
                        ) != 1
                    ) throw new Validation("This record has been deleted. Reload the page.");
                }
                checkStock(c);
                c.commit();
            } catch (SQLException | RuntimeException e) {
                c.rollback();
                throw e;
            }
        }
    }

    private static void checkStock(Connection c) throws SQLException {
        if (
            number(
                c,
                "SELECT COUNT(*) FROM books b WHERE b.stock < (SELECT COUNT(*) FROM loans l WHERE l.book_id=b.id AND l.returned_on IS NULL)+(SELECT COUNT(*) FROM reservations r WHERE r.book_id=b.id AND r.status='ready')"
            ) > 0
        ) throw new Validation(
            "Not enough copies are available. Return a book or cancel a ready-for-pickup reservation first."
        );
    }

    public static void delete(Module m, long id, Actor a) throws SQLException {
        if (!writable(a, m) || a.member()) throw new Validation(
            "You do not have permission to delete this record."
        );
        try (var c = open()) {
            c.setAutoCommit(false);
            try {
                if (Set.of("books", "loans", "reservations").contains(m.key())) query(
                    c,
                    "SELECT id FROM books ORDER BY id FOR UPDATE"
                );
                var row = get(c, m, id, a);
                if (m.key().equals("loans") && row.get("returned_on") == null) throw new Validation(
                    "Record the return before deleting this loan."
                );
                execute(c, "DELETE FROM " + m.table() + " WHERE id=?", id);
                c.commit();
            } catch (SQLException | RuntimeException e) {
                c.rollback();
                throw e;
            }
        }
    }

    public static Map<String, Object> dashboard(Actor a) throws SQLException {
        try (var c = open()) {
            Map<String, Object> d = new HashMap<>();
            d.put("books", number(c, "SELECT COUNT(*) FROM books"));
            d.put("copies", number(c, "SELECT COALESCE(SUM(stock),0) FROM books"));
            d.put("members", number(c, "SELECT COUNT(*) FROM users WHERE role='member'"));
            String own = a.member() ? " AND member_id=" + a.id() : "";
            d.put("active", number(c, "SELECT COUNT(*) FROM loans WHERE returned_on IS NULL" + own));
            d.put(
                "overdue",
                number(
                    c,
                    "SELECT COUNT(*) FROM loans WHERE returned_on IS NULL AND due_on<CURRENT_DATE" + own
                )
            );
            d.put("pending", number(c, "SELECT COUNT(*) FROM reservations WHERE status='pending'" + own));
            d.put("returned", number(c, "SELECT COUNT(*) FROM loans WHERE returned_on IS NOT NULL" + own));
            d.put(
                "recent",
                query(
                    c,
                    "SELECT l.id,u.name AS member,b.title AS book,l.borrowed_on,l.due_on,l.returned_on FROM loans l JOIN books b ON b.id=l.book_id JOIN users u ON u.id=l.member_id" +
                        (a.member() ? " WHERE l.member_id=" + a.id() : "") +
                        " ORDER BY l.id DESC LIMIT 5"
                )
            );
            d.put(
                "popular",
                query(
                    c,
                    "SELECT b.id,b.title,a.name AS author,c.name AS category,COUNT(l.id) AS total FROM books b JOIN authors a ON a.id=b.author_id JOIN categories c ON c.id=b.category_id LEFT JOIN loans l ON l.book_id=b.id GROUP BY b.id,b.title,a.name,c.name ORDER BY total DESC,b.id LIMIT 4"
                )
            );
            d.put(
                "categories",
                query(
                    c,
                    "SELECT c.name,COUNT(b.id) AS total FROM categories c LEFT JOIN books b ON b.category_id=c.id GROUP BY c.id,c.name ORDER BY total DESC"
                )
            );
            return d;
        }
    }
}
