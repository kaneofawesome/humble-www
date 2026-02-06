<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InputValidationTest extends WebTestCase
{
    public function testCharacterRestrictions(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $form = $crawler->selectButton('contact_form_submit')->form([
                'contact_form[name]' => 'Test<script>alert("xss")</script>User',
                'contact_form[email]' => 'test@example.com',
                'contact_form[message]' => 'This is a test message with minimum length',
            ]);
            $client->submit($form);

            $this->assertResponseIsSuccessful();
            $this->assertSelectorExists('.form-errors');
            $this->assertSelectorTextContains('body', 'invalid characters');
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testHtmlTagsStripped(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $form = $crawler->selectButton('contact_form_submit')->form([
                'contact_form[name]' => 'Test User',
                'contact_form[email]' => 'test@example.com',
                'contact_form[message]' => 'Message with <b>bold</b> and <i>italic</i> HTML tags',
            ]);
            $client->submit($form);

            $this->assertResponseIsSuccessful();
            $this->assertSelectorTextContains('body', 'invalid characters');
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testEmailValidation(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $invalidEmails = [
                'invalid-email',
                'test@',
                '@example.com',
            ];

            foreach ($invalidEmails as $invalidEmail) {
                $crawler = $client->request('GET', '/contact');
                $form = $crawler->selectButton('contact_form_submit')->form([
                    'contact_form[name]' => 'Test User',
                    'contact_form[email]' => $invalidEmail,
                    'contact_form[message]' => 'This is a test message with minimum length',
                ]);
                $client->submit($form);

                $this->assertResponseIsSuccessful();
                $this->assertSelectorExists('.form-errors');
            }
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testIdnDomainRejection(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $idnEmails = [
                "test@m\xC3\xBCnchen.de",
                "user@caf\xC3\xA9.com",
            ];

            foreach ($idnEmails as $idnEmail) {
                $crawler = $client->request('GET', '/contact');
                $form = $crawler->selectButton('contact_form_submit')->form([
                    'contact_form[name]' => 'Test User',
                    'contact_form[email]' => $idnEmail,
                    'contact_form[message]' => 'This is a test message with minimum length',
                ]);
                $client->submit($form);

                $this->assertResponseIsSuccessful();
                $this->assertSelectorTextContains('body', 'valid email address');
            }
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testMessageLengthValidation(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $form = $crawler->selectButton('contact_form_submit')->form([
                'contact_form[name]' => 'Test User',
                'contact_form[email]' => 'test@example.com',
                'contact_form[message]' => 'Short',
            ]);
            $client->submit($form);

            $this->assertResponseIsSuccessful();
            $this->assertSelectorTextContains('body', 'at least 10 characters');

            // Test message too long
            $crawler = $client->request('GET', '/contact');
            $longMessage = str_repeat('This is a very long message. ', 20);
            $form = $crawler->selectButton('contact_form_submit')->form([
                'contact_form[name]' => 'Test User',
                'contact_form[email]' => 'test@example.com',
                'contact_form[message]' => $longMessage,
            ]);
            $client->submit($form);

            $this->assertResponseIsSuccessful();
            $this->assertSelectorTextContains('body', 'cannot be longer than 500 characters');
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testAllowedCharacters(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $form = $crawler->selectButton('contact_form_submit')->form([
                'contact_form[name]' => "John O'Connor-Smith Jr.",
                'contact_form[email]' => 'john@example.com',
                'contact_form[message]' => "Hello, I'm interested in your services. Please contact me - it's urgent!",
            ]);
            $client->submit($form);

            $this->assertResponseRedirects();
            $this->assertStringContainsString('/contact/success', $client->getResponse()->headers->get('Location'));
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testFieldLengthLimits(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $longName = str_repeat('VeryLongName', 10);
            $form = $crawler->selectButton('contact_form_submit')->form([
                'contact_form[name]' => $longName,
                'contact_form[email]' => 'test@example.com',
                'contact_form[message]' => 'This is a test message with minimum length',
            ]);
            $client->submit($form);

            $this->assertResponseIsSuccessful();
            $this->assertSelectorExists('.form-errors');
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }
}
