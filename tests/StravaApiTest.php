<?php

use Iamstuartwilson\StravaApi;

class StravaApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Iamstuartwilson\StravaApi
     */
    private $stravaApi;

    public function setUp()
    {
        $this->stravaApi = new \Iamstuartwilson\StravaApi(999, '_SECRET_');
    }

    public function tearDown()
    {
        unset($this->stravaApi);
    }

    public function testIfAuthenticationUrlWorksAsExpected()
    {
        $expected = 'https://www.strava.com/oauth/authorize'
            . '?client_id=999'
            . '&redirect_uri=' . urlencode('https://example.org/')
            . '&response_type=code'
            . '&approval_prompt=auto';

        $url = $this->stravaApi->authenticationUrl('https://example.org/', 'auto', null, null);

        $this->assertEquals($expected, $url);
    }

    public function testIfAuthenticationUrlWithScopeWorksAsExpected()
    {
        $expected = 'https://www.strava.com/oauth/authorize'
            . '?client_id=999'
            . '&redirect_uri=' . urlencode('https://example.org/')
            . '&response_type=code'
            . '&approval_prompt=auto'
            . '&scope=read';

        $url = $this->stravaApi->authenticationUrl('https://example.org/', 'auto', 'read', null);

        $this->assertEquals($expected, $url);
    }

    public function testIfTokenRefreshCheckReturnsTrueIfNoExpiresTimestampIsSet()
    {
        $this->stravaApi->setAccessToken('access_token', 'refresh_token', null);

        self::assertFalse($this->stravaApi->isTokenRefreshNeeded());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testIfTokenRefreshCheckReturnsTrueIfExpiresTimestampIsInThePast()
    {
        $this->stravaApi->setAccessToken('access_token', 'refresh_token', time() - 86400);

        self::assertTrue($this->stravaApi->isTokenRefreshNeeded());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testIfTokenRefreshCheckReturnsTrueIfExpiresTimestampIsDueInLessThanOneHour()
    {
        $this->stravaApi->setAccessToken('access_token', 'refresh_token', time() + 1800);

        self::assertTrue($this->stravaApi->isTokenRefreshNeeded());
    }

    public function testIfTokenRefreshCheckReturnsFalseIfExpiresTimestampIsMoreThanOneHourInTheFuture()
    {
        $this->stravaApi->setAccessToken('access_token', 'refresh_token', time() + 7200);

        self::assertFalse($this->stravaApi->isTokenRefreshNeeded());
    }
}
