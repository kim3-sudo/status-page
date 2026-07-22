<?php

namespace Tests\Regression;

use PHPUnit\Framework\TestCase;

/**
 * Guards against the SQL-injection class of bug found in admin/updatetotpadmin.php
 * (unescaped $_POST concatenated directly into a query string) and its less-severe
 * siblings (escaped-but-still-concatenated queries) across the rest of admin/.
 * These are static source checks, not execution tests — there is no live database
 * in this environment, but a regex match on "$sql = ... $_POST" or a leftover
 * mysqli_real_escape_string() call is a reliable signal that a prepared-statement
 * conversion was missed or reverted.
 */
final class AdminSqlInjectionTest extends TestCase
{
    private function adminPhpFiles(): array
    {
        return glob(PROJECT_ROOT . '/admin/*.php');
    }

    public function testNoMysqliRealEscapeStringRemainsInAdmin(): void
    {
        foreach ($this->adminPhpFiles() as $file) {
            $contents = file_get_contents($file);
            $this->assertStringNotContainsString(
                'mysqli_real_escape_string',
                $contents,
                basename($file) . ' should use a prepared statement instead of manual escaping'
            );
        }
    }

    public function testNoStringConcatenatedSqlWithUserInput(): void
    {
        // A $sql/$stmt-ish variable built via string concatenation that also
        // mentions $_POST or $_SESSION on the same or a directly preceding
        // statement is exactly the pattern that caused the original SQLi.
        $pattern = '/\$[a-zA-Z_]*sql[a-zA-Z_]*\s*=\s*"[^"]*"\s*\.\s*\$_(POST|SESSION|GET)/';

        foreach ($this->adminPhpFiles() as $file) {
            $contents = file_get_contents($file);
            $this->assertDoesNotMatchRegularExpression(
                $pattern,
                $contents,
                basename($file) . ' appears to concatenate request/session data directly into SQL'
            );
        }
    }
}
