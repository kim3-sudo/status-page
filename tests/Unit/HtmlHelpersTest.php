<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HtmlHelpersTest extends TestCase
{
    public function testEscapesHtmlSpecialCharacters(): void
    {
        $this->assertSame('&lt;b&gt;&amp;&quot;&#039;', e('<b>&"\''));
    }

    public function testNullEscapesToEmptyString(): void
    {
        $this->assertSame('', e(null));
    }

    public function testAcceptsWebAndMailAndRelativeLinks(): void
    {
        $this->assertSame('https://example.org/a', safeUrl('https://example.org/a'));
        $this->assertSame('HTTP://example.org', safeUrl('HTTP://example.org'));
        $this->assertSame('mailto:help@example.org', safeUrl('mailto:help@example.org'));
        $this->assertSame('/status/page', safeUrl('/status/page'));
        $this->assertSame('#section', safeUrl('#section'));
    }

    public function testEscapesQuotesInsideAcceptedUrls(): void
    {
        $this->assertSame('https://example.org/?q=&quot;x&quot;', safeUrl('https://example.org/?q="x"'));
    }

    #[DataProvider('unsafeUrls')]
    public function testRejectsNonWebSchemes(string $url): void
    {
        $this->assertNull(safeUrl($url));
    }

    public static function unsafeUrls(): array
    {
        return [
            'javascript' => ['javascript:alert(1)'],
            'javascript uppercase' => ['JAVASCRIPT:alert(1)'],
            'leading space' => [' javascript:alert(1)'],
            'tab inside scheme' => ["java\tscript:alert(1)"],
            'data' => ['data:text/html,<script>alert(1)</script>'],
            'vbscript' => ['vbscript:msgbox(1)'],
            'bare word' => ['example.org'],
            'empty' => [''],
        ];
    }

    public function testNullUrlIsRejected(): void
    {
        $this->assertNull(safeUrl(null));
    }
}
