<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SanitizeTest extends TestCase
{
    public function testEmptyInputGivesEmptyOutput(): void
    {
        $this->assertSame('', sanitizeUpdateBody("   \n  "));
    }

    public function testPlainTextIsEscapedAndSplitIntoParagraphs(): void
    {
        $out = sanitizeUpdateBody("First <line> & more\nSecond line");
        $this->assertSame('<p>First &lt;line&gt; &amp; more</p><p>Second line</p>', $out);
    }

    public function testPlainTextScriptIsInert(): void
    {
        $out = sanitizeUpdateBody('<script>alert(1)</script>');
        $this->assertStringNotContainsString('<script', $out);
    }

    public function testKeepsTheEditorsFormatting(): void
    {
        $out = sanitizeUpdateBody('<p>We are <strong>investigating</strong>.</p><ul><li>Step one</li></ul>');
        $this->assertStringContainsString('<strong>investigating</strong>', $out);
        $this->assertStringContainsString('<li>Step one</li>', $out);
    }

    public function testRemovesScriptsAndEventHandlers(): void
    {
        $out = sanitizeUpdateBody('<p onclick="x()">Hi</p><script>steal()</script><img src=x onerror="y()">');
        $this->assertStringNotContainsString('onclick', $out);
        $this->assertStringNotContainsString('<script', $out);
        $this->assertStringNotContainsString('onerror', $out);
        $this->assertStringNotContainsString('<img', $out);
        $this->assertStringContainsString('Hi', $out);
    }

    #[DataProvider('dangerousLinks')]
    public function testRejectsDangerousLinkSchemes(string $href): void
    {
        $out = sanitizeUpdateBody('<p><a href="' . $href . '">click</a></p>');
        $this->assertStringNotContainsString('javascript', strtolower($out));
        $this->assertStringNotContainsString('data:', strtolower($out));
        $this->assertStringNotContainsString('vbscript', strtolower($out));
    }

    public static function dangerousLinks(): array
    {
        return [
            'javascript' => ['javascript:alert(1)'],
            'javascript mixed case' => ['JaVaScRiPt:alert(1)'],
            'javascript with tab' => ["java\tscript:alert(1)"],
            'data html' => ['data:text/html,<script>alert(1)</script>'],
            'vbscript' => ['vbscript:msgbox(1)'],
        ];
    }

    public function testKeepsWebLinksAndMarksThemNofollow(): void
    {
        $out = sanitizeUpdateBody('<p><a href="https://example.org/status">status</a></p>');
        $this->assertStringContainsString('href="https://example.org/status"', $out);
        $this->assertStringContainsString('nofollow', $out);
    }

    public function testSanitizingTwiceChangesNothing(): void
    {
        $once = sanitizeUpdateBody('<p>Fixed <em>now</em></p><script>x</script>');
        $this->assertSame($once, sanitizeUpdateBody($once));
    }
}
