<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ApiKeyTest extends TestCase
{
    public function testStoredHashIsSha256Hex(): void
    {
        $this->assertSame(hash('sha256', 'abc'), apiKeyHash('abc'));
        $this->assertSame(64, strlen(apiKeyHash('abc')));
    }

    public function testFingerprintNeverContainsTheKey(): void
    {
        $key = str_repeat('k', 128);
        $fingerprint = apiKeyFingerprint($key);

        $this->assertStringStartsWith('key:', $fingerprint);
        $this->assertStringNotContainsString(substr($key, 0, 8), $fingerprint);
        $this->assertSame(16, strlen($fingerprint));
    }

    public function testBearerHeaderIsRead(): void
    {
        $found = apiKeyFromRequest(['HTTP_AUTHORIZATION' => 'Bearer abc123'], [], []);
        $this->assertSame(['key' => 'abc123', 'source' => 'bearer'], $found);
    }

    public function testBearerSchemeIsCaseInsensitiveAndTrimmed(): void
    {
        $found = apiKeyFromRequest(['HTTP_AUTHORIZATION' => 'bearer abc123  '], [], []);
        $this->assertSame('abc123', $found['key']);
    }

    public function testRedirectedAuthorizationHeaderIsRead(): void
    {
        // Some CGI setups expose the header under REDIRECT_HTTP_AUTHORIZATION.
        $found = apiKeyFromRequest(['REDIRECT_HTTP_AUTHORIZATION' => 'Bearer xyz'], [], []);
        $this->assertSame('xyz', $found['key']);
    }

    public function testBearerWinsOverTheLegacyParameters(): void
    {
        $found = apiKeyFromRequest(
            ['HTTP_AUTHORIZATION' => 'Bearer from-header'],
            ['authkey' => 'from-url'],
            ['authkey' => 'from-form']
        );
        $this->assertSame(['key' => 'from-header', 'source' => 'bearer'], $found);
    }

    public function testFormFieldIsUsedBeforeTheUrlParameter(): void
    {
        $found = apiKeyFromRequest([], ['authkey' => 'from-url'], ['authkey' => 'from-form']);
        $this->assertSame(['key' => 'from-form', 'source' => 'form'], $found);
    }

    public function testUrlParameterIsReportedAsDeprecatedSource(): void
    {
        $found = apiKeyFromRequest([], ['authkey' => 'from-url'], []);
        $this->assertSame(['key' => 'from-url', 'source' => 'query'], $found);
    }

    public function testMissingOrEmptyKeysAreNull(): void
    {
        $this->assertNull(apiKeyFromRequest([], [], []));
        $this->assertNull(apiKeyFromRequest(['HTTP_AUTHORIZATION' => 'Bearer '], [], []));
        $this->assertNull(apiKeyFromRequest(['HTTP_AUTHORIZATION' => 'Basic dXNlcjpwYXNz'], [], []));
        $this->assertNull(apiKeyFromRequest([], ['authkey' => ''], []));
    }

    public function testRetentionFromSettingValue(): void
    {
        $this->assertSame(90, logRetentionDays('90'));
        $this->assertSame(30, logRetentionDays(' 30 '));
        $this->assertNull(logRetentionDays('0'), 'zero keeps everything');
        $this->assertSame(LOG_RETENTION_DEFAULT_DAYS, logRetentionDays(''));
        $this->assertSame(LOG_RETENTION_DEFAULT_DAYS, logRetentionDays('forever'));
        $this->assertSame(LOG_RETENTION_DEFAULT_DAYS, logRetentionDays('-5'));
    }
}
