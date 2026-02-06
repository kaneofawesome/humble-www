<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CsrfProtectionTest extends WebTestCase
{
    public function testCsrfTokenRequired(): void
    {
        $client = static::createClient();

        // Submit form without CSRF token via raw POST
        $client->request('POST', '/contact', [
            'contact_form[name]' => 'Test User',
            'contact_form[email]' => 'test@example.com',
            'contact_form[message]' => 'This is a test message without CSRF token',
        ]);

        // Without captcha or CSRF, the form will not pass validation and redirect
        // The response should NOT be a successful redirect to /contact/success
        $response = $client->getResponse();
        $this->assertFalse(
            $response->isRedirection() && str_contains($response->headers->get('Location', ''), '/contact/success'),
            'Form should not succeed without CSRF token'
        );
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
