package id.ac.its.mybook;

import java.security.*;
import java.util.*;
import javax.crypto.SecretKeyFactory;
import javax.crypto.spec.PBEKeySpec;

public final class Security {

    private Security() {}

    public static String token() {
        byte[] b = new byte[24];
        new SecureRandom().nextBytes(b);
        return Base64.getUrlEncoder().withoutPadding().encodeToString(b);
    }

    public static String hash(String password) {
        String salt = token();
        return "180000:" + salt + ":" + derive(password, salt, 180000);
    }

    private static String derive(String password, String salt, int rounds) {
        try {
            return Base64.getEncoder().encodeToString(
                SecretKeyFactory.getInstance("PBKDF2WithHmacSHA256")
                    .generateSecret(
                        new PBEKeySpec(
                            password.toCharArray(),
                            salt.getBytes(java.nio.charset.StandardCharsets.UTF_8),
                            rounds,
                            256
                        )
                    )
                    .getEncoded()
            );
        } catch (GeneralSecurityException e) {
            throw new IllegalStateException(e);
        }
    }

    public static boolean verify(String password, String hash) {
        try {
            String[] p = hash.split(":");
            return MessageDigest.isEqual(
                derive(password, p[1], Integer.parseInt(p[0])).getBytes(
                    java.nio.charset.StandardCharsets.UTF_8
                ),
                p[2].getBytes(java.nio.charset.StandardCharsets.UTF_8)
            );
        } catch (RuntimeException e) {
            return false;
        }
    }

    public static String escape(Object value) {
        return value == null
            ? ""
            : value
                  .toString()
                  .replace("&", "&amp;")
                  .replace("<", "&lt;")
                  .replace(">", "&gt;")
                  .replace("\"", "&quot;")
                  .replace("'", "&#39;");
    }
}
