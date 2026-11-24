<%@ page pageEncoding="UTF-8" %>

<%@ include file="header.jspf" %>

<%
    Map<String, Object> data = (Map<String, Object>) request.getAttribute("data");
    List<Map<String, Object>> distribution = (List<Map<String, Object>>) data.get("categories");
    long total = ((Number) data.get("books")).longValue();
%>
<div class="page-heading">
    <div>
        <h1>Library Reports</h1>
        <p><%= me.member() ? "Your borrowing activity and the library collection." : "Current collection and circulation statistics." %></p>
    </div>
    <button class="button secondary" data-print><%= icon("chart") %>Print / save PDF</button>
</div>
<section class="stats-grid">
    <% String[][] metrics={{"books","Book title","book","blue"},{"active","Active loans","exchange","blue"},{"returned","Returned","check","green"},{"overdue","Overdue","clock","orange"}};for(String[] metric:metrics){ %>
    <article class="stat-card">
        <span class="stat-icon <%= metric[3] %>"><%= icon(metric[2]) %></span>
        <span class="stat-label"><%= metric[1] %></span>
        <strong><%= data.get(metric[0]) %></strong>
        <span class="stat-foot">As of <%= java.time.LocalDate.now() %></span>
    </article>
    <% } %>
</section>
<section class="panel report-panel">
    <div class="panel-heading">
        <div>
            <h2>Collection by category</h2>
            <p>Book titles grouped by category.</p>
        </div>
        <span class="category-pill"><%= total %> titles</span>
    </div>
    <% for(var item:distribution){long count=((Number)item.get("total")).longValue();int percent=total==0?0:(int)Math.round(count*100.0/total); %>
    <div class="distribution-row">
        <strong><%= escape(item.get("name")) %></strong>
        <progress value="<%= count %>" max="<%= Math.max(1, total) %>" aria-label="<%= escape(item.get("name")) %>"></progress>
        <span><%= count %> titles · <%= percent %>%</span>
    </div>
    <% } %>
    <div class="panel-bottom">Calculated from current records. Overdue status is based on due and return dates.</div>
</section>
<section class="panel report-panel">
    <div class="panel-heading">
        <div>
            <h2>Operational notes</h2>
            <p>Items that need attention.</p>
        </div>
    </div>
    <div class="report-notes">
        <p>
            <strong><%= data.get("pending") %> reservations</strong> awaiting approval.
        </p>
        <p>
            <strong><%= data.get("overdue") %> loans</strong> currently overdue.
        </p>
        <p>
            <strong><%= data.get("copies") %> copies</strong> recorded across the collection.
        </p>
    </div>
</section>

<%@ include file="footer.jspf" %>
