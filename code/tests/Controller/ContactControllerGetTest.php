<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactControllerGetTest extends WebTestCase
{
    public function testContactPageDisplaysCorrectly(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contact');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="contact_form[name]"]');
        $this->assertSelectorExists('input[name="contact_form[email]"]');
        $this->assertSelectorExists('textarea[name="contact_form[message]"]');
    }

    public function testContactPageWithServicePreselection(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contact?service=coaching');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('select[name="contact_form[serviceType]"]');
    }

    public function testHttpRedirectsToHttps(): void
    {
        // This test will verify HTTPS enforcement is working
        // Implementation will be added during security configuration phase
        $this->markTestSkipped('Will be implemented with HTTPS enforcement');
    }

    public function testContactPageHasRecaptcha(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contact');

        $this->assertResponseIsSuccessful();
        // Should have reCAPTCHA or math challenge
        // This test will fail until reCAPTCHA integration is complete
        $this->assertSelectorExists('[data-sitekey]');
    }

    public function testContactPageHasCsrfProtection(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contact');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('input[name="contact_form[_token]"]');
    }
}