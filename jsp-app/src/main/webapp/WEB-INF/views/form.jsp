<%@ page pageEncoding="UTF-8" %>

<%@ include file="header.jspf" %>

<%
    Map<String, Object> row = (Map<String, Object>) request.getAttribute("row");
    Map<String, List<Map<String, Object>>> fieldOptions = (Map<
        String,
        List<Map<String, Object>>
    >) request.getAttribute("options");
    boolean detail = Boolean.TRUE.equals(request.getAttribute("detail"));
    boolean edit = row.get("id") != null;
    boolean cancellation = me.member() && edit && !detail;
%>

<a class="back-link" href="<%= base %>/app/<%= currentModule.key() %>">Back to <%= currentModule.title().toLowerCase(Locale.ROOT) %></a>
<div class="page-heading">
    <div>
        <h1><%= detail ? currentModule.singular() + " Details" : (cancellation ? "Cancel" : edit ? "Edit" : "Add") + " " + currentModule.singular() %></h1>
        <p><%= detail ? "View the complete record details." : "Complete the fields below. Fields marked * are required." %></p>
    </div>
    <%
        if (
            detail &&
            LibraryService.writable(me, currentModule) &&
            (!me.member() || Set.of("pending", "ready").contains(row.get("status")))
        ) {
    %>
    <a class="button primary" href="<%= base %>/app/<%= currentModule.key() %>/edit?id=<%= row.get("id") %>">
        <%= icon("edit") %><%= me.member() ? "Cancel reservation" : "Edit record" %></a>
    <% } %>
</div>
<div class="form-layout">
    <section class="panel form-panel">
        <% if (request.getAttribute("error") != null) { %>
        <div class="alert error" role="alert"><%= escape(request.getAttribute("error")) %></div>
        <% } %>
        <form method="post" action="<%= base %>/app/<%= currentModule.key() %>/save">
            <input type="hidden" name="csrf" value="<%= escape(session.getAttribute("csrf")) %>">
            <% if (edit) { %>
            <input type="hidden" name="id" value="<%= escape(row.get("id")) %>">
            <% } %>
            <div class="form-section-title"><%= icon(currentModule.icon()) %><h2><%= currentModule.singular() %> information</h2>
                <% if (edit) { %>
                <span class="record-number">#<%= escape(row.get("id")) %></span>
                <% } %>
            </div>
            <div class="form-grid">
                <%
                    for (var field : currentModule.fields()) {
                        if (detail && field.type().equals("password")) continue;
                        Object value = row.get(field.name());
                        String valueText = value == null ? "" : value.toString();
                        boolean hiddenMember = me.member() && field.name().equals("member_id");
                        boolean lockedReservation = edit && field.name().equals("reservation_id");
                        if (hiddenMember && !detail) {
                %>
                <input type="hidden" name="member_id" value="<%= me.id() %>">
                <%
                    continue;
                    }
                    if (me.member() && field.name().equals("status") && !detail) {
                %>
                <input type="hidden" name="status" value="<%= cancellation ? "cancelled" : "pending" %>">
                <%
                    continue;
                    }
                %>
                <div class="field <%= field.type().equals("textarea") ? "span-two" : "" %>">
                    <label for="<%= field.name() %>"><%= escape(field.label()) %><%= !detail && field.required() ? " *" : "" %></label>
                    <%
                        if (detail || cancellation || lockedReservation) {
                            String display = valueText;
                            if (field.type().equals("select")) {
                                for (var option : fieldOptions.getOrDefault(field.name(), List.of()))
                                    if (option.get("id").toString().equals(valueText)) display = option
                                        .get("name")
                                        .toString();
                                if (field.name().equals("status")) display = LibraryService.label(valueText);
                            }
                    %>
                    <div class="detail-value"><%= escape(display.isBlank() ? "—" : display) %></div>
                    <% if (!detail) { %>
                    <input type="hidden" name="<%= field.name() %>" value="<%= escape(valueText) %>">
                    <% } %>

                    <% } else if (field.type().equals("textarea")) { %>
                    <textarea id="<%= field.name() %>" name="<%= field.name() %>" rows="4" maxlength="<%= field.name().equals("address") ? 500 : currentModule.key().equals("books") ? 2000 : 1000 %>"><%= escape(valueText) %></textarea>
                    <% } else if (field.type().equals("select")) { %>
                    <select id="<%= field.name() %>" name="<%= field.name() %>" <%= field.required() ? "required" : "" %>>
                        <option value="">Select <%= field.label().toLowerCase(Locale.ROOT) %></option>
                        <% for (var option : fieldOptions.getOrDefault(field.name(), List.of())) { %>
                        <option value="<%= escape(option.get("id")) %>" <%= option.get("id").toString().equals(valueText) ? "selected" : "" %>><%= escape(option.get("name")) %></option>
                        <% } %>
                    </select>
                    <% } else { %>
                    <input id="<%= field.name() %>" name="<%= field.name() %>" type="<%= field.type() %>"
                        value="<%= field.type().equals("password") ? "" : escape(valueText) %>" <%= field.required() || field.type().equals("password")&&!edit?"required":"" %> <%= field.type().equals("password") ? "autocomplete='new-password' minlength='12'" : field.type().equals("number") ? "min='0' step='1'" : "" %> maxlength="<%= field.name().equals("email") ? 180 : field.name().equals("title") ? 200 : field.name().equals("phone") || field.name().equals("isbn") ? 30 : field.name().equals("shelf") ? 40 : field.name().equals("city") || field.name().equals("country") ? 100 : 160 %>">
                    <% if (field.type().equals("password") && edit) { %>
                    <small class="field-hint">Leave blank to keep the current password.</small>
                    <%
                        }
                        }
                    %>
                </div>
                <% } %>
            </div>
            <% if (!detail) { %>
            <div class="form-actions">
                <a class="button secondary" href="<%= base %>/app/<%= currentModule.key() %>">Cancel</a>
                <button class="button <%= cancellation ? "danger-button" : "primary" %>"><%= icon("check") %><%= cancellation ? "Cancel reservation" : "Save " + currentModule.singular().toLowerCase(Locale.ROOT) %></button>
            </div>
            <% } %>
        </form>
        <% if (detail && currentModule.key().equals("books") && me.member()) { %>
        <div class="form-actions">
            <a class="button primary" href="<%= base %>/app/reservations/new?book_id=<%= escape(row.get("id")) %>">
                <%= icon("calendar") %>Reserve this book</a>
        </div>
        <%
            }
            if (detail && currentModule.key().equals("books") && !me.member()) {
        %>
        <form class="form-actions" method="post" action="<%= base %>/app/books/delete"
            data-confirm="Delete this book? Books with existing transactions cannot be deleted.">
            <input type="hidden" name="csrf" value="<%= escape(session.getAttribute("csrf")) %>">
            <input type="hidden" name="id" value="<%= escape(row.get("id")) %>">
            <button class="button danger-button"><%= icon("trash") %>Delete books</button>
        </form>
        <% } %>
    </section>
    <aside class="form-aside">
        <span class="note-icon"><%= icon("book") %></span>
        <h3>Form guidance</h3>
        <p><%= currentModule.key().equals("loans") ? "Available copies are checked automatically. Enter the return date when a book is received. For a reservation pickup, select the matching reservation, member, and book." : currentModule.key().equals("reservations") ? "New reservations require confirmation. Ready-for-pickup status holds one copy. Creating the linked loan marks the reservation as collected." : "Use the information shown on the book or account. Fields marked * are required." %></p>
        <div class="aside-divider"></div>
        <small>Check the details before saving.</small>
    </aside>
</div>

<% if (request.getAttribute("community") != null) { %>

<%@ include file="community.jspf" %>

<% } %>

<%@ include file="footer.jspf" %>
