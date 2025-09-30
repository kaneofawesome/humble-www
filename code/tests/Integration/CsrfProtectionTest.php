<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CsrfProtectionTest extends WebTestCase
{
    public function testCsrfTokenRequired(): void
    {
        $client = static::createClient();

        // Try submitting form without CSRF token
        $client->request('POST', '/contact', [
            'contact_form[name]' => 'Test User',
            'contact_form[email]' => 'test@example.com',
            'contact_form[message]' => 'This is a test message without CSRF token'
        ]);

        // Should be rejected
        $this->assertResponseStatusCodeSame(400);
    }

    public function testCsrfTokenValidation(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $this->assertSelectorExists('input[name="contact_form[_token]"]');
        }
    }
}
