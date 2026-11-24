package id.ac.its.mybook;

import static id.ac.its.mybook.Database.*;
import static id.ac.its.mybook.LibraryService.*;
import static org.junit.jupiter.api.Assertions.*;

import java.sql.*;
import java.time.*;
import java.util.*;
import java.util.concurrent.*;
import org.junit.jupiter.api.*;

class LibraryServiceTest {

    private final Actor admin = new Actor(1, "Admin", "admin");
    private final Actor member = new Actor(3, "Member", "member");

    @BeforeEach
    void setup() throws Exception {
        System.setProperty(
            "mybook.db",
            "jdbc:h2:mem:" + UUID.randomUUID() + ";MODE=PostgreSQL;DATABASE_TO_LOWER=TRUE;DB_CLOSE_DELAY=-1"
        );
        try (var c = open(); var stream = getClass().getResourceAsStream("/schema.sql")) {
            for (String sql : new String(
                stream.readAllBytes(),
                java.nio.charset.StandardCharsets.UTF_8
            ).split(";"))
                if (!sql.isBlank()) execute(c, sql);
            for (int i = 1; i <= 4; i++) execute(
                c,
                "INSERT INTO users(name,email,password_hash,role) VALUES (?,?,?,?)",
                "User " + i,
                "user" + i + "@test.com",
                Security.hash("ExamplePass123!"),
                i == 1 ? "admin" : i == 2 ? "librarian" : "member"
            );
            execute(c, "INSERT INTO categories(name) VALUES ('Fiksi')");
            execute(c, "INSERT INTO authors(name,country) VALUES ('Penulis','Indonesia')");
            execute(
                c,
                "INSERT INTO publishers(name,city,email) VALUES ('Penerbit','Surabaya','publisher@test.com')"
            );
            execute(
                c,
                "INSERT INTO books(title,isbn,category_id,author_id,publisher_id,published_year,stock,shelf) VALUES ('Buku Uji','9781234567890',1,1,1,2020,1,'A-1')"
            );
        }
    }

    private Map<String, String> loan(String member, String book) {
        return new HashMap<>(
            Map.of(
                "member_id",
                member,
                "book_id",
                book,
                "borrowed_on",
                LocalDate.now().toString(),
                "due_on",
                LocalDate.now().plusDays(14).toString(),
                "returned_on",
                "",
                "notes",
                ""
            )
        );
    }

    private Map<String, String> reservation(String member, String status) {
        return new HashMap<>(
            Map.of(
                "member_id",
                member,
                "book_id",
                "1",
                "reserved_on",
                LocalDate.now().toString(),
                "status",
                status,
                "notes",
                ""
            )
        );
    }

    private long last(String table) throws SQLException {
        try (var c = open()) {
            return number(c, "SELECT MAX(id) FROM " + table);
        }
    }

    @Test
    void allEightModulesHaveWorkingCrud() throws Exception {
        Map<String, Map<String, String>> inputs = new LinkedHashMap<>();
        inputs.put("categories", new HashMap<>(Map.of("name", "Baru", "description", "Deskripsi")));
        inputs.put(
            "authors",
            new HashMap<>(Map.of("name", "Penulis Baru", "country", "Indonesia", "biography", "Bio"))
        );
        inputs.put(
            "publishers",
            new HashMap<>(Map.of("name", "Penerbit Baru", "city", "Jakarta", "email", "new@test.com"))
        );
        inputs.put(
            "books",
            new HashMap<>(
                Map.of(
                    "title",
                    "Buku Baru",
                    "isbn",
                    "9781234567899",
                    "category_id",
                    "1",
                    "author_id",
                    "1",
                    "publisher_id",
                    "1",
                    "published_year",
                    "2021",
                    "stock",
                    "3",
                    "shelf",
                    "B-1",
                    "description",
                    "Sinopsis"
                )
            )
        );
        for (String key : List.of("members", "librarians"))
            inputs.put(
                key,
                new HashMap<>(
                    Map.of(
                        "name",
                        "Akun " + key,
                        "email",
                        key + "@test.com",
                        "phone",
                        "0812345",
                        "address",
                        "Surabaya",
                        "password",
                        "ExamplePass123!"
                    )
                )
            );
        inputs.put("reservations", reservation("3", "pending"));
        var returned = loan("3", "1");
        returned.put("returned_on", LocalDate.now().toString());
        inputs.put("loans", returned);
        for (var entry : inputs.entrySet()) {
            Module m = Module.ALL.get(entry.getKey());
            var input = entry.getValue();
            save(m, null, input, admin);
            long id = last(m.table());
            assertEquals(id, ((Number) get(m, id, admin).get("id")).longValue());
            assertFalse(list(m, admin, "", 1).isEmpty());
            if (m.account()) input.put("password", "");
            String changed = input.containsKey("name") ? "name" : m.key().equals("books") ? "title" : "notes";
            input.put(changed, "Diperbarui");
            save(m, id, input, admin);
            assertEquals("Diperbarui", get(m, id, admin).get(changed));
            delete(m, id, admin);
            assertThrows(Validation.class, () -> get(m, id, admin));
        }
    }

    @Test
    void inventoryCannotBeOverbookedAndReturnReleasesStock() throws Exception {
        var m = Module.ALL.get("loans");
        var first = loan("3", "1");
        save(m, null, first, admin);
        long id = last("loans");
        assertThrows(Validation.class, () -> save(m, null, loan("4", "1"), admin));
        assertThrows(Validation.class, () -> delete(m, id, admin));
        first.put("returned_on", LocalDate.now().toString());
        save(m, id, first, admin);
        save(m, null, loan("4", "1"), admin);
        try (var c = open()) {
            assertEquals(1, number(c, "SELECT COUNT(*) FROM loans WHERE returned_on IS NULL"));
        }
    }

    @Test
    void reservationPickupMovesHoldToLoanAtomically() throws Exception {
        var r = Module.ALL.get("reservations");
        var l = Module.ALL.get("loans");
        save(r, null, reservation("3", "ready"), admin);
        long id = last("reservations");
        assertThrows(Validation.class, () -> save(l, null, loan("4", "1"), admin));
        var wrong = loan("4", "1");
        wrong.put("reservation_id", String.valueOf(id));
        assertThrows(Validation.class, () -> save(l, null, wrong, admin));
        assertEquals("ready", get(r, id, admin).get("status"));
        var pickup = loan("3", "1");
        pickup.put("reservation_id", String.valueOf(id));
        save(l, null, pickup, admin);
        assertEquals("picked_up", get(r, id, admin).get("status"));
        assertThrows(SQLException.class, () -> delete(r, id, admin));
        assertThrows(Validation.class, () -> save(r, id, reservation("3", "cancelled"), admin));
    }

    @Test
    void memberIsolationAndRoleAuthorization() throws Exception {
        save(Module.ALL.get("reservations"), null, reservation("4", "ready"), member);
        long id = last("reservations");
        var own = get(Module.ALL.get("reservations"), id, member);
        assertEquals(3L, own.get("member_id"));
        assertEquals("pending", own.get("status"));
        Actor other = new Actor(4, "Other", "member");
        assertTrue(list(Module.ALL.get("reservations"), other, "", 1).isEmpty());
        assertThrows(Validation.class, () -> get(Module.ALL.get("reservations"), id, other));
        assertThrows(Validation.class, () -> save(Module.ALL.get("loans"), null, loan("3", "1"), member));
        assertFalse(allowed(new Actor(2, "Librarian", "librarian"), Module.ALL.get("members")));
        assertThrows(Validation.class, () -> delete(Module.ALL.get("reservations"), id, member));
        save(Module.ALL.get("reservations"), id, reservation("4", "ready"), member);
        assertEquals("cancelled", get(Module.ALL.get("reservations"), id, member).get("status"));
    }

    @Test
    void foreignKeysDuplicatesAndDatesAreValidated() throws Exception {
        assertThrows(SQLException.class, () -> delete(Module.ALL.get("categories"), 1, admin));
        assertThrows(SQLException.class, () ->
            save(Module.ALL.get("categories"), null, Map.of("name", "Fiksi", "description", ""), admin)
        );
        var invalid = loan("3", "1");
        invalid.put("due_on", LocalDate.now().minusDays(1).toString());
        assertThrows(Validation.class, () -> save(Module.ALL.get("loans"), null, invalid, admin));
        assertThrows(Validation.class, () -> save(Module.ALL.get("loans"), null, loan("1", "1"), admin));
        assertTrue(list(Module.ALL.get("books"), admin, "' OR 1=1 --", 1).isEmpty());
        assertTrue(list(Module.ALL.get("books"), admin, "%", 1).isEmpty());
    }

    @Test
    void concurrentLoansCannotConsumeSameCopy() throws Exception {
        var pool = Executors.newFixedThreadPool(2);
        var start = new CountDownLatch(1);
        try {
            List<Future<Boolean>> futures = new ArrayList<>();
            for (String memberId : List.of("3", "4"))
                futures.add(
                    pool.submit(() -> {
                        start.await();
                        try {
                            save(Module.ALL.get("loans"), null, loan(memberId, "1"), admin);
                            return true;
                        } catch (Validation e) {
                            return false;
                        }
                    })
                );
            start.countDown();
            int successes = 0;
            for (var f : futures) if (f.get(10, TimeUnit.SECONDS)) successes++;
            assertEquals(1, successes);
        } finally {
            pool.shutdownNow();
        }
    }

    @Test
    void readyHoldCancellationAndStockReduction() throws Exception {
        var reservations = Module.ALL.get("reservations");
        var pending = reservation("3", "pending");
        save(reservations, null, pending, admin);
        long reservationId = last("reservations");
        save(Module.ALL.get("loans"), null, loan("4", "1"), admin);
        long loanId = last("loans");
        pending.put("status", "ready");
        assertThrows(Validation.class, () -> save(reservations, reservationId, pending, admin));
        assertEquals("pending", get(reservations, reservationId, admin).get("status"));
        var returned = loan("4", "1");
        returned.put("returned_on", LocalDate.now().toString());
        save(Module.ALL.get("loans"), loanId, returned, admin);
        save(reservations, reservationId, pending, admin);
        var book = new HashMap<>(
            Map.of(
                "title",
                "Buku Uji",
                "isbn",
                "9781234567890",
                "category_id",
                "1",
                "author_id",
                "1",
                "publisher_id",
                "1",
                "published_year",
                "2020",
                "stock",
                "0",
                "shelf",
                "A-1",
                "description",
                ""
            )
        );
        assertThrows(Validation.class, () -> save(Module.ALL.get("books"), 1L, book, admin));
        pending.put("status", "cancelled");
        save(reservations, reservationId, pending, admin);
        save(Module.ALL.get("books"), 1L, book, admin);
        assertEquals(0, get(Module.ALL.get("books"), 1, admin).get("stock"));
    }

    @Test
    void passwordHashAndEscaping() {
        String hash = Security.hash("ExamplePass123!");
        assertTrue(Security.verify("ExamplePass123!", hash));
        assertFalse(Security.verify("wrong", hash));
        assertNotEquals(hash, Security.hash("ExamplePass123!"));
        assertEquals("&lt;script&gt;&amp;&quot;&#39;", Security.escape("<script>&\"'"));
    }
}
