package id.ac.its.mybook;

import java.nio.charset.StandardCharsets;
import java.sql.*;
import java.util.*;

public final class Database {

    private Database() {}

    public static Connection open() throws SQLException {
        return DriverManager.getConnection(
            System.getProperty(
                "mybook.db",
                System.getenv().getOrDefault(
                    "JDBC_URL",
                    "jdbc:h2:./data/mybook;MODE=PostgreSQL;DATABASE_TO_LOWER=TRUE"
                )
            ),
            System.getenv().getOrDefault("DB_USER", "sa"),
            System.getenv().getOrDefault("DB_PASSWORD", "")
        );
    }

    public static List<Map<String, Object>> query(
        Connection c,
        String sql,
        Object... params
    ) throws SQLException {
        try (var s = c.prepareStatement(sql)) {
            bind(s, params);
            try (var r = s.executeQuery()) {
                List<Map<String, Object>> rows = new ArrayList<>();
                while (r.next()) {
                    Map<String, Object> row = new LinkedHashMap<>();
                    for (int i = 1; i <= r.getMetaData().getColumnCount(); i++) row.put(
                        r.getMetaData().getColumnLabel(i).toLowerCase(Locale.ROOT),
                        r.getObject(i)
                    );
                    rows.add(row);
                }
                return rows;
            }
        }
    }

    public static int execute(Connection c, String sql, Object... params) throws SQLException {
        try (var s = c.prepareStatement(sql)) {
            bind(s, params);
            return s.executeUpdate();
        }
    }

    private static void bind(PreparedStatement s, Object[] params) throws SQLException {
        for (int i = 0; i < params.length; i++) s.setObject(i + 1, params[i]);
    }

    public static long number(Connection c, String sql, Object... params) throws SQLException {
        return ((Number) query(c, sql, params).get(0).values().iterator().next()).longValue();
    }

    public static void initialize() throws Exception {
        try (var c = open(); var in = Database.class.getResourceAsStream("/schema.sql")) {
            if (in == null) throw new IllegalStateException("schema.sql missing");
            for (String sql : new String(in.readAllBytes(), StandardCharsets.UTF_8).split(";"))
                if (!sql.isBlank()) execute(c, sql);
            if (number(c, "SELECT COUNT(*) FROM users") == 0) {
                boolean demo = "true".equalsIgnoreCase(System.getenv().getOrDefault("SEED_DEMO", "false"));
                String password = System.getenv("ADMIN_PASSWORD");
                if (password == null || password.length() < 12) {
                    if (!demo) throw new IllegalStateException(
                        "Set ADMIN_PASSWORD (at least 12 characters), or SEED_DEMO=true for local demonstration."
                    );
                    password = "AdminDemo123!";
                }
                c.setAutoCommit(false);
                try {
                    execute(
                        c,
                        "INSERT INTO users(name,email,password_hash,role) VALUES (?,?,?,?)",
                        "Administrator",
                        System.getenv().getOrDefault("ADMIN_EMAIL", "admin@mybook.test"),
                        Security.hash(password),
                        "admin"
                    );
                    if (demo) seed(c);
                    c.commit();
                } catch (Exception e) {
                    c.rollback();
                    throw e;
                }
            }
        }
    }

    private static void seed(Connection c) throws SQLException {
        execute(
            c,
            "INSERT INTO users(name,email,password_hash,role,phone,address) VALUES (?,?,?,?,?,?)",
            "Nadia Putri",
            "librarian@mybook.test",
            Security.hash("Librarian123!"),
            "librarian",
            "081234567890",
            "Surabaya"
        );
        String[] names = {
            "Alya Ramadhani",
            "Bima Pratama",
            "Citra Maharani",
            "Dimas Saputra",
            "Eva Kusuma",
        };
        for (int i = 0; i < names.length; i++) execute(
            c,
            "INSERT INTO users(name,email,password_hash,role,phone,address) VALUES (?,?,?,?,?,?)",
            names[i],
            "member" + (i + 1) + "@mybook.test",
            Security.hash("MemberDemo123!"),
            "member",
            "08123456000" + i,
            "Surabaya"
        );
        String[] cats = { "Fiction", "Self Improvement", "Technology", "Science", "History" };
        for (String name : cats)
            execute(
                c,
                "INSERT INTO categories(name,description) VALUES (?,?)",
                name,
                "Collection: books " + name.toLowerCase(Locale.ROOT)
            );
        String[][] authors = {
            { "Andrea Hirata", "Indonesia" },
            { "James Clear", "United States" },
            { "Robert C. Martin", "United States" },
            { "Morgan Housel", "United States" },
            { "Yuval Noah Harari", "Israel" },
            { "Dee Lestari", "Indonesia" },
            { "Cal Newport", "United States" },
            { "Matt Haig", "United Kingdom" },
        };
        for (String[] a : authors)
            execute(
                c,
                "INSERT INTO authors(name,country,biography) VALUES (?,?,?)",
                a[0],
                a[1],
                "Author " + a[0]
            );
        String[][] publishers = {
            { "Bentang Pustaka", "Yogyakarta", "info@bentang.test" },
            { "Gramedia Pustaka Utama", "Jakarta", "info@gramedia.test" },
            { "Prentice Hall", "Boston", "info@prentice.test" },
        };
        for (String[] p : publishers)
            execute(c, "INSERT INTO publishers(name,city,email) VALUES (?,?,?)", (Object[]) p);
        String[][] books = {
            {
                "Laskar Pelangi",
                "9789793062792",
                "1",
                "1",
                "1",
                "2005",
                "8",
                "A-01",
                "The friendship and educational aspirations of children in Belitung.",
            },
            {
                "Atomic Habits",
                "9780735211292",
                "2",
                "2",
                "2",
                "2018",
                "6",
                "B-02",
                "Small changes and remarkable results. A guide to building better habits.",
            },
            {
                "Clean Code",
                "9780132350884",
                "3",
                "3",
                "3",
                "2008",
                "5",
                "C-01",
                "A guide to writing readable and maintainable software.",
            },
            {
                "The Psychology of Money",
                "9780857197689",
                "2",
                "4",
                "2",
                "2020",
                "7",
                "B-03",
                "Lessons about behavior, wealth, and financial decisions.",
            },
            {
                "Sapiens",
                "9780062316097",
                "5",
                "5",
                "2",
                "2015",
                "4",
                "E-01",
                "The history of humanity from prehistoric times to the modern world.",
            },
            {
                "Filosofi Kopi",
                "9789799625701",
                "1",
                "6",
                "1",
                "2006",
                "6",
                "A-03",
                "Stories about life, discovery, and a cup of coffee.",
            },
            {
                "Deep Work",
                "9781455586691",
                "2",
                "7",
                "2",
                "2016",
                "5",
                "B-04",
                "Building focus to produce meaningful work.",
            },
            {
                "The Midnight Library",
                "9780525559474",
                "1",
                "8",
                "2",
                "2020",
                "4",
                "A-05",
                "A library between life and its possibilities.",
            },
        };
        for (String[] b : books)
            execute(
                c,
                "INSERT INTO books(title,isbn,category_id,author_id,publisher_id,published_year,stock,shelf,description) VALUES (?,?,?,?,?,?,?,?,?)",
                b[0],
                b[1],
                Long.valueOf(b[2]),
                Long.valueOf(b[3]),
                Long.valueOf(b[4]),
                Integer.valueOf(b[5]),
                Integer.valueOf(b[6]),
                b[7],
                b[8]
            );
        var today = java.time.LocalDate.now();
        execute(
            c,
            "INSERT INTO loans(member_id,book_id,borrowed_on,due_on,notes) VALUES (?,?,?,?,?)",
            3L,
            1L,
            today.minusDays(3),
            today.plusDays(11),
            "Loan langsung"
        );
        execute(
            c,
            "INSERT INTO loans(member_id,book_id,borrowed_on,due_on,notes) VALUES (?,?,?,?,?)",
            4L,
            3L,
            today.minusDays(17),
            today.minusDays(3),
            "Please return the book to the service desk"
        );
        execute(
            c,
            "INSERT INTO loans(member_id,book_id,borrowed_on,due_on,returned_on) VALUES (?,?,?,?,?)",
            5L,
            2L,
            today.minusDays(10),
            today.plusDays(4),
            today.minusDays(1)
        );
        execute(
            c,
            "INSERT INTO reservations(member_id,book_id,reserved_on,status) VALUES (?,?,?,?)",
            6L,
            4L,
            today,
            "pending"
        );
        execute(
            c,
            "INSERT INTO reservations(member_id,book_id,reserved_on,status) VALUES (?,?,?,?)",
            7L,
            5L,
            today,
            "ready"
        );
    }
}
