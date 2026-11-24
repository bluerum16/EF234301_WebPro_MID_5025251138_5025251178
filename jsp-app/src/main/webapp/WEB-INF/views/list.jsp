<%@ page pageEncoding="UTF-8" %>

<%@ include file="header.jspf" %>

<%
    List<Map<String, Object>> rows = (List<Map<String, Object>>) request.getAttribute("rows");
    int pageNumber = (Integer) request.getAttribute("pageNumber");
    boolean writable = LibraryService.writable(me, currentModule);
    String query = Optional.ofNullable(request.getParameter("q")).orElse("");
    String encoded = java.net.URLEncoder.encode(query, java.nio.charset.StandardCharsets.UTF_8);
    boolean books = currentModule.key().equals("books"),
        transactions = Set.of("loans", "reservations").contains(currentModule.key());
%>

<div class="page-heading">
    <div>
        <h1><%= escape(me.member() && books ? "Book Catalog" : currentModule.title()) %></h1>
        <p><%= escape(currentModule.description()) %></p>
    </div>
    <% if (writable) { %>
    <a class="button primary" href="<%= base %>/app/<%= currentModule.key() %>/new"><%= icon("plus") %>Add
        <%= currentModule.singular() %></a>
    <% } %>
</div>
<section class="panel collection-panel">
    <div class="collection-toolbar">
        <div>
            <h2><%= books ? "All books" : "Daftar " + currentModule.title().toLowerCase(Locale.ROOT) %></h2>
            <span class="muted">Page <%= pageNumber %> · <%= rows.size() %> records shown</span>
        </div>
        <form class="search-form" method="get" role="search">
            <label class="sr-only" for="search">Search <%= currentModule.singular() %></label><%= icon("search") %>
            <input id="search" name="q" value="<%= escape(query) %>" placeholder="Search <%= currentModule.singular().toLowerCase(Locale.ROOT) %>…"
                maxlength="200">
            <button class="button secondary compact">Search</button>
            <% if (!query.isBlank()) { %>
            <a class="text-link" href="<%= base %>/app/<%= currentModule.key() %>">Reset</a>
            <% } %>
        </form>
    </div>

    <% if (rows.isEmpty()) { %>
    <div class="empty-state">
        <span class="empty-icon"><%= icon(currentModule.icon()) %></span>
        <h2><%= query.isBlank() ? "No records yet" : "No matching results" %></h2>
        <p><%= query.isBlank() ? "Add a record to get started." : "Try a different search term or clear the search." %></p>
        <% if (writable && query.isBlank()) { %>
        <a class="button primary" href="<%= base %>/app/<%= currentModule.key() %>/new">Add <%= currentModule.singular() %></a>
        <% } %>
    </div>
    <% } else if (books) { %>
    <div class="book-grid">
        <%
            for (var row : rows) {
                int tone = (((Number) row.get("id")).intValue() - 1) % 8;
        %>
        <article class="book-card">
            <a class="book-art art-tone-<%= tone %>"
                href="<%= base %>/app/books/detail?id=<%= row.get("id") %>">
                <div class="cover cover-<%= tone %>">
                    <small><%= escape(row.get("author")) %></small>
                    <strong><%= escape(row.get("title")) %></strong>
                    <span>THE MYBOOK COLLECTION</span>
                </div>
            </a>
            <div class="book-info">
                <div class="book-meta">
                    <span class="category-pill"><%= escape(row.get("category")) %></span>
                    <span class="availability <%= ((Number) row.get("available")).longValue() > 0 ? "" : "unavailable" %>"><%= row.get("available") %> available</span>
                </div>
                <a class="book-title" href="<%= base %>/app/books/detail?id=<%= row.get("id") %>">
                    <%= escape(row.get("title")) %></a>
                <p><%= escape(row.get("author")) %></p>
                <div class="book-card-bottom">
                    <span>Shelf <%= escape(row.get("shelf")) %></span>
                    <a class="text-link" href="<%= base %>/app/books/<%= writable ? "edit" : "detail" %>?id=<%= row.get("id") %>">
                        <%= writable ? "Manage book" : "View book" %></a>
                </div>
            </div>
        </article>
        <% } %>
    </div>
    <% } else { %>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th><%= transactions ? "BOOK" : "NAME" %></th>
                    <th><%= transactions ? "MEMBER" : currentModule.account() ? "EMAIL" : currentModule.key().equals("authors") ? "COUNTRY" : currentModule.key().equals("publishers") ? "CITY" : "DESCRIPTION" %></th>
                    <% if (transactions) { %>
                    <th>DATE</th>
                    <th>STATUS</th>
                    <% } else if (currentModule.account()) { %>
                    <th>PHONE</th>
                    <% } %>
                    <th class="text-right">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <% for (var row : rows) { %>
                <tr>
                    <td>
                        <strong>
                            <a class="row-title"
                                href="<%= base %>/app/<%= currentModule.key() %>/detail?id=<%= row.get("id") %>">
                                <%= escape(row.get(transactions ? "book" : "name")) %></a>
                        </strong>
                        <small class="record-id">ID #<%= row.get("id") %></small>
                    </td>
                    <td class="description-cell"><%= escape( row.get( transactions ? "member" : currentModule.account() ? "email" : currentModule.key().equals("authors") ? "country" : currentModule.key().equals("publishers") ? "city" : "description" ) ) %></td>
                    <%
                        if (transactions) {
                            String status = Ui.status(row);
                    %>
                    <td><%= escape(row.get(currentModule.key().equals("loans") ? "due_on" : "reserved_on")) %><small class="record-id"><%= currentModule.key().equals("loans") ? "Due date" : "Reservation date" %></small>
                    </td>
                    <td>
                        <span class="badge <%= status %>"><%= Ui.statusLabel(status) %></span>
                    </td>
                    <% } else if (currentModule.account()) { %>
                    <td><%= escape(row.get("phone")) %></td>
                    <% } %>
                    <td>
                        <div class="row-actions">
                            <a class="icon-button" aria-label="View details" title="View details"
                                href="<%= base %>/app/<%= currentModule.key() %>/detail?id=<%= row.get("id") %>">
                                <%= icon("eye") %></a>
                            <% if (writable && (!me.member() || Set.of("pending", "ready").contains(row.get("status")))) { %>
                            <a class="icon-button" aria-label="<%= me.member() ? "Cancel reservation" : "Edit" %>" title="<%= me.member() ? "Cancel reservation" : "Edit" %>"
                                href="<%= base %>/app/<%= currentModule.key() %>/edit?id=<%= row.get("id") %>">
                                <%= icon("edit") %></a>
                            <% if (!me.member()) { %>
                            <form method="post" action="<%= base %>/app/<%= currentModule.key() %>/delete"
                                data-confirm="Delete this record? Records used by existing transactions cannot be deleted.">
                                <input type="hidden" name="csrf" value="<%= escape(session.getAttribute("csrf")) %>">
                                <input type="hidden" name="id" value="<%= row.get("id") %>">
                                <button class="icon-button danger" aria-label="Delete" title="Delete">
                                    <%= icon("trash") %></button>
                            </form>
                            <%
                                }
                                }
                            %>
                        </div>
                    </td>
                </tr>
                <% } %>
            </tbody>
        </table>
    </div>
    <% } %>

    <div class="pagination">
        <span>Up to 12 records per page</span>
        <div>
            <% if (pageNumber > 1) { %>
            <a class="button secondary compact" href="?page=<%= pageNumber - 1 %>&amp;q=<%= escape(encoded) %>">Previous
            </a>
            <% } %>
            <span class="page-number"><%= pageNumber %></span>
            <% if (Boolean.TRUE.equals(request.getAttribute("hasNext"))) { %>
            <a class="button secondary compact" href="?page=<%= pageNumber + 1 %>&amp;q=<%= escape(encoded) %>">
                Next</a>
            <% } %>
        </div>
    </div>
</section>

<%@ include file="footer.jspf" %>
