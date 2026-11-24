package id.ac.its.mybook;

import java.nio.file.*;
import org.apache.catalina.startup.Tomcat;

public final class Main {

    public static void main(String[] args) throws Exception {
        if (!Backend.LARAVEL) Database.initialize();
        Tomcat tomcat = new Tomcat();
        tomcat.setBaseDir("work/tomcat");
        tomcat.setPort(Integer.parseInt(System.getenv().getOrDefault("PORT", "8080")));
        tomcat.getConnector().setURIEncoding("UTF-8");
        var context = tomcat.addWebapp("", Path.of("src/main/webapp").toAbsolutePath().toString());
        context.setParentClassLoader(Main.class.getClassLoader());
        context.setUseHttpOnly(true);
        var cookieProcessor = new org.apache.tomcat.util.http.Rfc6265CookieProcessor();
        cookieProcessor.setSameSiteCookies("lax");
        context.setCookieProcessor(cookieProcessor);
        Tomcat.addServlet(context, "app", new AppServlet());
        context.addServletMappingDecoded("/app/*", "app");
        context.addServletMappingDecoded("/auth/*", "app");
        tomcat.start();
        System.out.println("myBook JSP ready at http://localhost:" + tomcat.getConnector().getLocalPort());
        tomcat.getServer().await();
    }
}
