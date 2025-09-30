<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactControllerPostTest extends WebTestCase
{
    public function testValidContactFormSubmission(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/contact');
        $form = $crawler->selectButton('Submit')->form();

        $form['contact_form[name]'] = 'Test User';
        $form['contact_form[email]'] = 'test@example.com';
        $form['contact_form[message]'] = 'This is a test message with minimum length required';

        $client->submit($form);

        $this->assertResponseRedirects('/contact/success');
    }

    public function testFormValidationErrors(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');
        $form = $crawler->selectButton('Submit')->form();

        // Submit empty form
        $client->submit($form);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorTextContains('.form-error', 'This value should not be blank');
    }

    public function testMessageLengthValidation(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');
        $form = $crawler->selectButton('Submit')->form();

        $form['contact_form[name]'] = 'Test User';
        $form['contact_form[email]'] = 'test@example.com';
        $form['contact_form[message]'] = 'Short';

        $client->submit($form);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorTextContains('.form-error', 'message must be at least 10 characters');
    }

    public function testRateLimitingProtection(): void
    {
        $client = static::createClient();

        for ($i = 0; $i < 5; $i++) {
            $crawler = $client->request('GET', '/contact');
            $form = $crawler->selectButton('Submit')->form();

            $form['contact_form[name]'] = "Test User $i";
            $form['contact_form[email]'] = "test$i@example.com";
            $form['contact_form[message]'] = "This is test message number $i with required length";

            $client->submit($form);
        }

        $crawler = $client->request('GET', '/contact');
        $form = $crawler->selectButton('Submit')->form();

        $form['contact_form[name]'] = 'Test User 6';
        $form['contact_form[email]'] = 'test6@example.com';
        $form['contact_form[message]'] = 'This is the sixth test message that should be blocked';

        $client->submit($form);

        $this->assertResponseStatusCodeSame(403);
        $this->assertSelectorTextContains('.error', 'rate limit');
    }

    public function testInputSanitization(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');
        $form = $crawler->selectButton('Submit')->form();

        $form['contact_form[name]'] = 'Test<script>alert("xss")</script>';
        $form['contact_form[email]'] = 'test@example.com';
        $form['contact_form[message]'] = 'This is a message with <b>HTML</b> content that should be sanitized';

        $client->submit($form);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorTextContains('.form-error', 'special characters');
    }

    public function testIdnDomainRejection(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');
        $form = $crawler->selectButton('Submit')->form();

        $form['contact_form[name]'] = 'Test User';
        $form['contact_form[email]'] = 'test@münchen.de'; // IDN domain
        $form['contact_form[message]'] = 'This is a test message with minimum length required';

        $client->submit($form);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorTextContains('.form-error', 'ASCII domains only');
    }
}
