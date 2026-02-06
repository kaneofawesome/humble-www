<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

class ThemePersistenceTest extends WebTestCase
{
    public function testThemePersistsWithoutFlash(): void
    {
        $client = static::createClient();

        // Set dark theme preference via cookie
        $client->getCookieJar()->set(new Cookie('theme', 'dark'));

        // Navigate to home page - ThemeListener reads cookie and sets Twig global
        $crawler = $client->request('GET', '/');

        // Verify theme is applied in HTML (ThemeListener reads cookie)
        $this->assertSelectorExists('html[data-theme="dark"]');

        // Navigate to another page
        $crawler = $client->request('GET', '/contact');

        // Verify theme persists
        $this->assertSelectorExists('html[data-theme="dark"]');
    }

    public function testDefaultThemeNoFlash(): void
    {
        $client = static::createClient();

        // Ensure no theme preference exists
        $client->getCookieJar()->clear();

        // Navigate to home page
        $crawler = $client->request('GET', '/');

        // Verify default theme is applied (light is the default)
        $this->assertSelectorExists('html[data-theme="light"]');

        // Verify page loads without requiring JavaScript
        $this->assertResponseIsSuccessful();
    }

    public function testThemePersistsAfterRefresh(): void
    {
        $client = static::createClient();

        // Set dark theme preference
        $client->getCookieJar()->set(new Cookie('theme', 'dark'));

        // Navigate to home page
        $crawler = $client->request('GET', '/');

        // Verify theme is applied
        $this->assertSelectorExists('html[data-theme="dark"]');

        // Simulate refresh by requesting the same page again
        $crawler = $client->request('GET', '/');

        // Verify theme still persists
        $this->assertSelectorExists('html[data-theme="dark"]');
    }

    public function testThemePersistsInNewTab(): void
    {
        $client = static::createClient();

        // Set dark theme preference
        $client->getCookieJar()->set(new Cookie('theme', 'dark'));

        // Navigate to a specific page
        $crawler = $client->request('GET', '/contact');

        // Verify theme is applied
        $this->assertSelectorExists('html[data-theme="dark"]');

        // Navigate directly to a different page (simulating new tab with same session)
        $crawler = $client->request('GET', '/services');

        // Verify theme persists in new tab
        $this->assertSelectorExists('html[data-theme="dark"]');
    }

    public function testLightThemePersistsWithoutFlash(): void
    {
        $client = static::createClient();

        // Set light theme preference
        $client->getCookieJar()->set(new Cookie('theme', 'light'));

        // Navigate to home page
        $crawler = $client->request('GET', '/');

        // Verify light theme is applied
        $this->assertSelectorExists('html[data-theme="light"]');

        // Navigate to another page
        $crawler = $client->request('GET', '/services');

        // Verify light theme persists
        $this->assertSelectorExists('html[data-theme="light"]');
    }

    public function testRapidNavigationMaintainsTheme(): void
    {
        $client = static::createClient();

        // Set dark theme preference
        $client->getCookieJar()->set(new Cookie('theme', 'dark'));

        // Rapidly navigate between multiple pages
        $pages = ['/', '/contact', '/services', '/'];

        foreach ($pages as $page) {
            $crawler = $client->request('GET', $page);
            // Verify theme persists on each navigation
            $this->assertSelectorExists('html[data-theme="dark"]');
        }
    }
}
