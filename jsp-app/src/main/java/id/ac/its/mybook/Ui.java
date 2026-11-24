package id.ac.its.mybook;

public final class Ui {

    private Ui() {}

    public static String icon(String key) {
        String p = switch (key) {
            case "heart" -> "<path d='M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z'/>";
            case "star" -> "<path d='m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9Z'/>";
            case "book" -> "<path d='M4 4h6a3 3 0 0 1 3 3v14a4 4 0 0 0-4-2H4zM20 4h-4a3 3 0 0 0-3 3M20 4v15h-3'/>";
            case "grid" -> "<rect x='3' y='3' width='7' height='7' rx='2'/><rect x='14' y='3' width='7' height='7' rx='2'/><rect x='3' y='14' width='7' height='7' rx='2'/><rect x='14' y='14' width='7' height='7' rx='2'/>";
            case "users" -> "<circle cx='9' cy='8' r='3'/><path d='M3 21v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6M21 21v-3a6 6 0 0 0-4-6'/>";
            case "calendar" -> "<rect x='3' y='5' width='18' height='16' rx='3'/><path d='M7 3v4M17 3v4M3 11h18M8 15h3M8 18h7'/>";
            case "exchange" -> "<path d='M4 7h16M16 3l4 4-4 4M20 17H4M8 13l-4 4 4 4'/>";
            case "pen" -> "<path d='m4 16 12-12a2 2 0 0 1 4 4L8 20l-5 1zM14 6l4 4'/>";
            case "building" -> "<path d='M4 21V4h16v17M2 21h20M8 8h2M14 8h2M8 12h2M14 12h2M10 21v-5h4v5'/>";
            case "search" -> "<circle cx='10.5' cy='10.5' r='6.5'/><path d='m16 16 5 5'/>";
            case "chart" -> "<path d='M4 3v18h17M9 16v-5M14 16V7M19 16v-9'/>";
            case "logout" -> "<path d='M10 4H4v16h6M9 12h12M17 8l4 4-4 4'/>";
            case "plus" -> "<path d='M12 5v14M5 12h14'/>";
            case "clock" -> "<circle cx='12' cy='12' r='9'/><path d='M12 7v5l3 2'/>";
            case "check" -> "<path d='m5 12 4 4L19 6'/>";
            case "eye" -> "<path d='M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z'/><circle cx='12' cy='12' r='3'/>";
            case "edit" -> "<path d='m14 4 6 6M4 16 16 4a3 3 0 0 1 4 4L8 20H4z'/>";
            case "trash" -> "<path d='M3 6h18M9 6V3h6v3M6 6l1 15h10l1-15M10 10v7M14 10v7'/>";
            default -> "<circle cx='12' cy='12' r='9'/><path d='M12 8v5M12 16h.01'/>";
        };
        return (
            "<svg class='icon' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1.7' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>" +
            p +
            "</svg>"
        );
    }

    public static String status(java.util.Map<String, Object> row) {
        if (row.containsKey("status")) return String.valueOf(row.get("status"));
        if (row.get("returned_on") != null) return "returned";
        if (
            row.get("due_on") != null &&
            java.time.LocalDate.parse(row.get("due_on").toString()).isBefore(java.time.LocalDate.now())
        ) return "overdue";
        return "borrowed";
    }

    public static String statusLabel(String s) {
        return switch (s) {
            case "returned" -> "Returned";
            case "overdue" -> "Overdue";
            case "borrowed" -> "Borrowed";
            default -> LibraryService.label(s);
        };
    }
}
