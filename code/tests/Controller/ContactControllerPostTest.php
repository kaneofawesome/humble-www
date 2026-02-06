<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactControllerPostTest extends WebTestCase
{
    public function testValidContactFormSubmission(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/contact');
        $form = $crawler->selectButton('contact_form_submit')->form([
            'contact_form[name]' => 'Test User',
            'contact_form[email]' => 'test@example.com',
            'contact_form[message]' => 'This is a test message with minimum length required',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects();
        $this->assertStringContainsString('/contact/success', $client->getResponse()->headers->get('Location'));
    }

    public function testFormValidationErrors(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');
        $form = $crawler->selectButton('contact_form_submit')->form();

        $client->submit($form);

        // Controller renders form with errors (200 status)
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.form-errors');
    }

    public function testMessageLengthValidation(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');
        $form = $crawler->selectButton('contact_form_submit')->form([
            'contact_form[name]' => 'Test User',
            'contact_form[email]' => 'test@example.com',
            'contact_form[message]' => 'Short',
        ]);

        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'at least 10 characters');
    }

    public function testRateLimitingProtection(): void
    {
        $this->markTestSkipped('Rate limiting requires database integration test setup');
    }

    public function testInputSanitization(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');
        $form = $crawler->selectButton('contact_form_submit')->form([
            'contact_form[name]' => 'Test<script>alert("xss")</script>',
            'contact_form[email]' => 'test@example.com',
            'contact_form[message]' => 'This is a message with HTML content that should be sanitized',
        ]);

        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.form-errors', 'invalid characters');
    }

    public function testIdnDomainRejection(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');
        $form = $crawler->selectButton('contact_form_submit')->form([
            'contact_form[name]' => 'Test User',
            'contact_form[email]' => "test@m\xC3\xBCnchen.de",
            'contact_form[message]' => 'This is a test message with minimum length required',
        ]);

        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'valid email address');
    }
}
