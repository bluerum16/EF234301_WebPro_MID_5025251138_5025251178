<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" import="id.ac.its.mybook.Security,id.ac.its.mybook.Ui" %>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0066b3">
    <title>Register · myBook</title>
    <link rel="icon" href="<%= request.getContextPath() %>/assets/favicon.svg">
    <link rel="stylesheet" href="<%= request.getContextPath() %>/assets/app.css">
</head>
<body class="login-body">
    <main class="login-layout">
        <section class="login-card" aria-labelledby="login-title">
            <a class="login-brand" href="<%= request.getContextPath() %>/">
                <span class="brand-icon"><%= Ui.icon("book") %></span>
                <span>myBook</span>
            </a>
            <div class="login-heading">
                <h1 id="login-title">Create your account</h1>
                <p>Register as a library member.</p>
            </div>
            <form method="post" action="<%= request.getContextPath() %>/auth/register" class="login-form">
                <% if (request.getAttribute("error") != null) { %>
                <div class="alert error" role="alert"><%= Security.escape(request.getAttribute("error")) %></div>
                <% } %>
                <input type="hidden" name="csrf" value="<%= Security.escape(session.getAttribute("csrf")) %>">
                <div class="field">
                    <label for="name">Full name</label>
                    <input id="name" name="name" autocomplete="name" placeholder="Enter your full name" maxlength="100" value="<%= Security.escape(request.getAttribute("name")) %>" required>
                </div>
                <div class="field">
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" placeholder="you@example.com" maxlength="180" value="<%= Security.escape(request.getAttribute("email")) %>" required>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" placeholder="At least 8 characters" minlength="8" maxlength="160" required>
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Re-enter your password" minlength="8" maxlength="160" required>
                </div>
                <button class="button primary full-width">Create account</button>
            </form>
            <p class="login-help">Already have an account? <a href="<%= request.getContextPath() %>/auth/login">Sign in</a></p>
        </section>
        <p class="login-credit">Farrel Satria Mukti & Fazli Irham Ramadhan Abdillah</p>
    </main>
</body>
</html>
