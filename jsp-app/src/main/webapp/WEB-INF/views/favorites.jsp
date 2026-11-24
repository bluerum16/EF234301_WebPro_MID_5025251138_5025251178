<%@ page pageEncoding="UTF-8" %>

<%@ include file="header.jspf" %>

<%
    List<Map<String, Object>> favoriteBooks = (List<Map<String, Object>>) request.getAttribute("rows");
    int favoritePage = (Integer) request.getAttribute("pageNumber");
%>

<div class="page-heading">
    <div>
        <h1>Favorite Books</h1>
        <p>Books you have saved to read later.</p>
    </div>
    <a class="button secondary" href="<%= base %>/app/books"><%= icon("book") %>Browse collection</a>
</div>
<section class="panel collection-panel">
    <div class="collection-toolbar">
        <div>
            <h2>Your saved books</h2>
            <span class="muted">Page <%= favoritePage %> · <%= favoriteBooks.size() %> books shown</span>
        </div>
        <span class="favorite-heading-icon"><%= icon("heart") %></span>
    </div>

    <% if (favoriteBooks.isEmpty()) { %>
    <div class="empty-state">
        <span class="empty-icon"><%= icon("heart") %></span>
        <h2>Start your reading list</h2>
        <p>Open a book and select “Save to favorites”.</p>
        <a class="button primary" href="<%= base %>/app/books">Browse books</a>
    </div>
    <% } else { %>
    <div class="book-grid">
        <%
            for (var book : favoriteBooks) {
                int tone = (((Number) book.get("id")).intValue() - 1) % 8;
        %>
        <article class="book-card">
            <a class="book-art art-tone-<%= tone %>"
                href="<%= base %>/app/books/detail?id=<%= book.get("id") %>">
                <div class="cover cover-<%= tone %>">
                    <small><%= escape(book.get("author")) %></small>
                    <strong><%= escape(book.get("title")) %></strong>
                    <span>THE MYBOOK COLLECTION</span>
                </div>
            </a>
            <div class="book-info">
                <div class="book-meta">
                    <span class="category-pill"><%= escape(book.get("category")) %></span>
                    <span class="availability"><%= escape(book.get("available")) %> available</span>
                </div>
                <a class="book-title" href="<%= base %>/app/books/detail?id=<%= book.get("id") %>">
                    <%= escape(book.get("title")) %></a>
                <p><%= escape(book.get("author")) %></p>
                <div class="favorite-card-actions">
                    <a class="text-link" href="<%= base %>/app/books/detail?id=<%= book.get("id") %>">View book
                    </a>
                    <form method="post" action="<%= base %>/app/community/favorite">
                        <input type="hidden" name="csrf" value="<%= escape(session.getAttribute("csrf")) %>">
                        <input type="hidden" name="book_id" value="<%= book.get("id") %>">
                        <input type="hidden" name="intent" value="remove">
                        <input type="hidden" name="return_to" value="favorites">
                        <button class="icon-button danger" title="Remove from favorites"
                            aria-label="Delete <%= escape(book.get("title")) %> from favorites"><%= icon("heart") %></button>
                    </form>
                </div>
            </div>
        </article>
        <% } %>
    </div>
    <% } %>
    <div class="pagination">
        <span>Your favorites are only visible to you.</span>
        <div>
            <% if (favoritePage > 1) { %>
            <a class="button secondary compact" href="?page=<%= favoritePage - 1 %>">Previous</a>
            <% } %>
            <span class="page-number"><%= favoritePage %></span>
            <% if (Boolean.TRUE.equals(request.getAttribute("hasNext"))) { %>
            <a class="button secondary compact" href="?page=<%= favoritePage + 1 %>">Next</a>
            <% } %>
        </div>
    </div>
</section>

<%@ include file="footer.jspf" %>
