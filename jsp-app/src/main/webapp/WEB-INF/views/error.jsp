<%@ include file="header.jspf" %>
<div class="panel empty-state"><%= icon("search") %><h1>This page could not be displayed.</h1>
    <p><%= escape(request.getAttribute("error")) %></p>
    <a class="button primary" href="<%= base %>/app/dashboard">Back to dashboard</a>
</div>
<%@ include file="footer.jspf" %>
