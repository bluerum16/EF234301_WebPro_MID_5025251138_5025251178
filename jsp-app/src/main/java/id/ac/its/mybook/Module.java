package id.ac.its.mybook;

import java.util.*;

public record Module(
    String key,
    String table,
    String title,
    String singular,
    String icon,
    String description,
    List<Field> fields,
    String select,
    String search
) {
    public record Field(String name, String label, String type, boolean required, String options) {}

    static Field f(String name, String label, String type) {
        return new Field(name, label, type, true, "");
    }

    static Field optional(String name, String label, String type) {
        return new Field(name, label, type, false, "");
    }

    static Field ref(String name, String label, String table) {
        return new Field(name, label, "select", true, table);
    }

    public boolean account() {
        return key.equals("members") || key.equals("librarians");
    }

    public String role() {
        return key.equals("members") ? "member" : "librarian";
    }

    public static final Map<String, Module> ALL = new LinkedHashMap<>();

    static {
        add(
            new Module(
                "books",
                "books",
                "Books",
                "Book",
                "book",
                "Manage the book collection, stock, and shelf locations.",
                List.of(
                    f("title", "Book title", "text"),
                    f("isbn", "ISBN", "text"),
                    ref("category_id", "Category", "categories"),
                    ref("author_id", "Author", "authors"),
                    ref("publisher_id", "Publisher", "publishers"),
                    f("published_year", "Publication year", "number"),
                    f("stock", "Total copies", "number"),
                    f("shelf", "Shelf location", "text"),
                    optional("description", "Synopsis", "textarea")
                ),
                "SELECT b.*,a.name AS author,c.name AS category,p.name AS publisher,(b.stock-(SELECT COUNT(*) FROM loans l WHERE l.book_id=b.id AND l.returned_on IS NULL)-(SELECT COUNT(*) FROM reservations r WHERE r.book_id=b.id AND r.status='ready')) AS available FROM books b JOIN authors a ON a.id=b.author_id JOIN categories c ON c.id=b.category_id JOIN publishers p ON p.id=b.publisher_id",
                "b.title || ' ' || b.isbn || ' ' || a.name"
            )
        );
        add(
            new Module(
                "categories",
                "categories",
                "Categories",
                "Category",
                "grid",
                "Organize books by category.",
                List.of(
                    f("name", "Category name", "text"),
                    optional("description", "Description", "textarea")
                ),
                "SELECT * FROM categories",
                "name"
            )
        );
        add(
            new Module(
                "authors",
                "authors",
                "Authors",
                "Author",
                "pen",
                "Manage authors and their biographies.",
                List.of(
                    f("name", "Author name", "text"),
                    f("country", "Country", "text"),
                    optional("biography", "Biography", "textarea")
                ),
                "SELECT * FROM authors",
                "name"
            )
        );
        add(
            new Module(
                "publishers",
                "publishers",
                "Publishers",
                "Publisher",
                "building",
                "Manage publishers and their contact details.",
                List.of(
                    f("name", "Publisher name", "text"),
                    f("city", "City", "text"),
                    f("email", "Email", "email")
                ),
                "SELECT * FROM publishers",
                "name"
            )
        );
        for (String key : List.of("members", "librarians"))
            add(
                new Module(
                    key,
                    "users",
                    key.equals("members") ? "Members" : "Librarians",
                    key.equals("members") ? "Member" : "Librarian",
                    "users",
                    "Manage accounts and contact details.",
                    List.of(
                        f("name", "Full name", "text"),
                        f("email", "Email", "email"),
                        optional("phone", "Phone number", "tel"),
                        optional("address", "Address", "textarea"),
                        optional("password", "Password (at least 12 characters)", "password")
                    ),
                    "SELECT id,name,email,phone,address,role FROM users WHERE role='" +
                        (key.equals("members") ? "member" : "librarian") +
                        "'",
                    "name || ' ' || email"
                )
            );
        add(
            new Module(
                "reservations",
                "reservations",
                "Reservations",
                "Reservation",
                "calendar",
                "Manage reservation requests and book pickups.",
                List.of(
                    ref("member_id", "Member", "members"),
                    ref("book_id", "Book", "books"),
                    f("reserved_on", "Reservation date", "date"),
                    new Field("status", "Status", "select", true, "pending,ready,cancelled"),
                    optional("notes", "Notes", "textarea")
                ),
                "SELECT r.*,u.name AS member,b.title AS book FROM reservations r JOIN users u ON u.id=r.member_id JOIN books b ON b.id=r.book_id",
                "u.name || ' ' || b.title"
            )
        );
        add(
            new Module(
                "loans",
                "loans",
                "Loans",
                "Loan",
                "exchange",
                "Record loans, due dates, and returns.",
                List.of(
                    ref("member_id", "Member", "members"),
                    ref("book_id", "Book", "books"),
                    new Field(
                        "reservation_id",
                        "Ready-for-pickup reservation (optional)",
                        "select",
                        false,
                        "ready_reservations"
                    ),
                    f("borrowed_on", "Borrowing date", "date"),
                    f("due_on", "Due date", "date"),
                    optional("returned_on", "Return date", "date"),
                    optional("notes", "Notes", "textarea")
                ),
                "SELECT l.*,u.name AS member,b.title AS book FROM loans l JOIN users u ON u.id=l.member_id JOIN books b ON b.id=l.book_id",
                "u.name || ' ' || b.title"
            )
        );
    }

    private static void add(Module m) {
        ALL.put(m.key, m);
    }
}
