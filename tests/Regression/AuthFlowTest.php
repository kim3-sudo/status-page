<?php

namespace Tests\Regression;

use PHPUnit\Framework\TestCase;

/**
 * Guards against two specific bugs found in the login flow:
 *  - authenticate.php reflected $_POST['email'] and other fields into hidden
 *    form inputs without escaping (XSS).
 *  - twofactorauth.php trusted client-POSTed id/firstname/lastname/suflag for
 *    the session instead of re-reading them from the DB row the TOTP secret
 *    was actually verified against — letting a user self-promote to
 *    superuser by tampering with their own login form.
 */
final class AuthFlowTest extends TestCase
{
    public function testAuthenticateEscapesReflectedFormValues(): void
    {
        $contents = file_get_contents(PROJECT_ROOT . '/authenticate.php');

        // Every hidden-input value built from $_POST/$id/$firstname/etc. in
        // the TOTP hand-off form must go through htmlspecialchars().
        $this->assertMatchesRegularExpression(
            "/name=\"email\" value=\"' \\. htmlspecialchars\\(\\\$_POST\\['email'\\]\\)/",
            $contents
        );
        $this->assertMatchesRegularExpression(
            "/name=\"suflag\" value=\"' \\. htmlspecialchars\\(\\\$suflag\\)/",
            $contents
        );
    }

    public function testAuthenticateRegeneratesSessionDestroyingOldOne(): void
    {
        $contents = file_get_contents(PROJECT_ROOT . '/authenticate.php');
        $this->assertStringContainsString('session_regenerate_id(true)', $contents);
    }

    public function testTwoFactorAuthDoesNotTrustPostedIdentityForSession(): void
    {
        $contents = file_get_contents(PROJECT_ROOT . '/twofactorauth.php');

        // These four assignments must come from $row (the DB record the TOTP
        // secret was verified against), never from $_POST.
        foreach (['id', 'firstname', 'lastname', 'suflag'] as $field) {
            $this->assertDoesNotMatchRegularExpression(
                "/\\\$_SESSION\\['" . $field . "'\\]\\s*=\\s*\\\$_POST/",
                $contents,
                "\$_SESSION['$field'] should be set from the verified DB row, not \$_POST"
            );
        }

        $this->assertStringContainsString("\$_SESSION['id'] = \$row['user_id']", $contents);
        $this->assertStringContainsString("\$_SESSION['suflag'] = \$row['user_issuperuser']", $contents);
        $this->assertStringContainsString('session_regenerate_id(true)', $contents);
    }
}
