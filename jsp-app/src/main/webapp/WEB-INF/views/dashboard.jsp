<%@ page pageEncoding="UTF-8" %>

<%@ include file="header.jspf" %>

<%
    Map<String, Object> data = (Map<String, Object>) request.getAttribute("data");
    List<Map<String, Object>> recent = (List<Map<String, Object>>) data.get("recent");
    List<Map<String, Object>> popular = (List<Map<String, Object>>) data.get("popular");
%>

<div class="page-heading">
    <div>
        <h1>Dashboard</h1>
        <p>An overview of the library collection and activity.</p>
    </div>
    <a class="button secondary" href="<%= base %>/app/reports"><%= icon("chart") %>View reports</a>
</div>
<section class="overview-banner">
    <div>
        <h2><%= me.member() ? "Your borrowing activity" : "Today’s overview" %></h2>
        <p><strong><%= data.get("active") %></strong> active loans<span>·</span><strong><%= data.get("pending") %></strong> pending reservations<span>·</span><strong><%= data.get("overdue") %></strong> overdue</p>
    </div>
    <a class="button white" href="<%= base %>/app/<%= me.member() ? "books" : "loans/new" %>"><%= icon(me.member() ? "book" : "plus") %><%= me.member() ? "View catalog" : "Record loan" %></a>
</section>
<section class="stats-grid" aria-label="Library overview">
    <article class="stat-card">
        <span class="stat-icon blue"><%= icon("book") %></span>
        <span class="stat-label">Book Titles</span>
        <strong><%= data.get("books") %><small>titles</small>
        </strong>
        <span class="stat-foot"><%= data.get("copies") %> copies in the collection</span>
    </article>
    <article class="stat-card">
        <span class="stat-icon blue"><%= icon("exchange") %></span>
        <span class="stat-label">Active Loans</span>
        <strong><%= data.get("active") %><small>books</small>
        </strong>
        <span class="stat-foot">Books not yet returned</span>
    </article>
    <article class="stat-card">
        <span class="stat-icon orange"><%= icon("calendar") %></span>
        <span class="stat-label">Pending Reservations</span>
        <strong><%= data.get("pending") %><small>reservations</small>
        </strong>
        <span class="stat-foot">Awaiting librarian approval</span>
    </article>
    <article class="stat-card">
        <span class="stat-icon green"><%= icon(me.member() ? "check" : "users") %></span>
        <span class="stat-label"><%= me.member() ? "Completed Loans" : "Library Members" %></span>
        <strong><%= data.get(me.member() ? "returned" : "members") %><small><%= me.member() ? "books" : "members" %></small>
        </strong>
        <span class="stat-foot"><%= me.member() ? "Returned books" : "Registered library members" %></span>
    </article>
</section>
<div class="dashboard-columns">
    <section class="panel recent-panel">
        <div class="panel-heading">
            <div>
                <h2>Recent loans</h2>
                <p>The latest recorded borrowing transactions.</p>
            </div>
            <a class="text-link" href="<%= base %>/app/loans">View all</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>BOOK & BORROWER</th>
                        <th>DUE DATE</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <%
                        for (var item : recent) {
                            String status = Ui.status(item);
                    %>
                    <tr>
                        <td>
                            <div class="table-book">
                                <span class="mini-cover tone-<%= ((Number) item.get("id")).intValue() % 4 %>"><%= icon("book") %></span>
                                <div>
                                    <strong><%= escape(item.get("book")) %></strong>
                                    <small><%= escape(item.get("member")) %></small>
                                </div>
                            </div>
                        </td>
                        <td><%= escape(item.get("due_on")) %></td>
                        <td>
                            <span class="badge <%= status %>"><%= Ui.statusLabel(status) %></span>
                        </td>
                    </tr>
                    <%
                        }
                        if (recent.isEmpty()) {
                    %>
                    <tr>
                        <td colspan="3" class="empty-cell">No loans have been recorded.</td>
                    </tr>
                    <% } %>
                </tbody>
            </table>
        </div>
        <div class="panel-bottom"><%= icon("clock") %><span>
                <strong><%= data.get("overdue") %> loans</strong> past the return deadline.
            </span>
        </div>
    </section>
    <section class="panel popular-panel">
        <div class="panel-heading">
            <div>
                <h2>Popular books</h2>
                <p>The most frequently borrowed books.</p>
            </div>
            <span class="subtle-icon"><%= icon("book") %></span>
        </div>
        <%
            int rank = 0;
            for (var item : popular) {
        %>
        <a class="popular-row" href="<%= base %>/app/books/detail?id=<%= item.get("id") %>">
            <div class="small-cover tone-<%= rank % 4 %>">
                <span><%= escape(item.get("book")) %></span><%= icon("book") %>
            </div>
            <div>
                <strong><%= escape(item.get("title")) %></strong>
                <small><%= escape(item.get("author")) %></small>
                <span class="category-pill"><%= escape(item.get("category")) %></span>
            </div>
            <span class="rank"><%= String.format("%02d", ++rank) %></span>
        </a>
        <% } %>
        <a class="popular-footer" href="<%= base %>/app/books">View all books</a>
    </section>
</div>

<%@ include file="footer.jspf" %>
