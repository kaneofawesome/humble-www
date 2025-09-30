<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

class ThemePersistenceTest extends WebTestCase
{
    public function testThemePersistsWithoutFlash(): void
    {
        $client = static::createClient();

        // Set dark theme preference
        $client->getCookieJar()->set(new Cookie('theme', 'dark'));

        // Navigate to home page
        $crawler = $client->request('GET', '/');

        // Verify theme is applied in HTML
        $this->assertSelectorExists('html[data-theme="dark"]');

        // Navigate to another page
        $client->clickLink('Contact');

        // Verify theme persists
        $this->assertSelectorExists('html[data-theme="dark"]');

        // Test browser back button
        $client->back();
        $this->assertSelectorExists('html[data-theme="dark"]');

        // Test browser forward button
        $client->forward();
        $this->assertSelectorExists('html[data-theme="dark"]');
    }

    public function testDefaultThemeNoFlash(): void
    {
        $client = static::createClient();

        // Ensure no theme preference exists
        $client->getCookieJar()->clear();

        // Navigate to home page
        $crawler = $client->request('GET', '/');

        // Verify default theme is applied (should be 'light' or system preference)
        $this->assertSelectorExists('html[data-theme]');

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
        $client->clickLink('Services');

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
