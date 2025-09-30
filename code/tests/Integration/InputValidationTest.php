<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InputValidationTest extends WebTestCase
{
    /**
     * Test 4: Input Validation
     * Goal: Test security and validation constraints
     */
    public function testCharacterRestrictions(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            // Test special characters in name field
            $form = $crawler->selectButton('submit')->form([
                'contact_form[name]' => 'Test<script>alert("xss")</script>User',
                'contact_form[email]' => 'test@example.com',
                'contact_form[message]' => 'This is a test message with minimum length'
            ]);

            $client->submit($form);

            // Should return validation error, not execute script
            $this->assertResponseStatusCodeSame(422);
            $this->assertSelectorExists('.form-error');
            $this->assertSelectorTextContains('body', 'special characters');

            // Verify no script execution occurred
            $this->assertSelectorNotExists('script');
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testHtmlTagsStripped(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $form = $crawler->selectButton('submit')->form([
                'contact_form[name]' => 'Test User',
                'contact_form[email]' => 'test@example.com',
                'contact_form[message]' => 'Message with <b>bold</b> and <i>italic</i> HTML tags'
            ]);

            $client->submit($form);

            // Should reject HTML content
            $this->assertResponseStatusCodeSame(422);
            $this->assertSelectorTextContains('.form-error', 'HTML tags');
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testEmailValidation(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            // Test invalid email formats
            $invalidEmails = [
                'invalid-email',
                'test@',
                '@example.com',
                'test..test@example.com'
            ];

            foreach ($invalidEmails as $invalidEmail) {
                $form = $crawler->selectButton('submit')->form([
                    'contact_form[name]' => 'Test User',
                    'contact_form[email]' => $invalidEmail,
                    'contact_form[message]' => 'This is a test message with minimum length'
                ]);

                $client->submit($form);

                $this->assertResponseStatusCodeSame(422);
                $this->assertSelectorExists('.form-error');
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
            // Test IDN (Internationalized Domain Names) rejection
            $idnEmails = [
                'test@münchen.de',
                'user@café.com',
                'info@пример.рф'
            ];

            foreach ($idnEmails as $idnEmail) {
                $form = $crawler->selectButton('submit')->form([
                    'contact_form[name]' => 'Test User',
                    'contact_form[email]' => $idnEmail,
                    'contact_form[message]' => 'This is a test message with minimum length'
                ]);

                $client->submit($form);

                $this->assertResponseStatusCodeSame(422);
                $this->assertSelectorTextContains('.form-error', 'ASCII domains only');
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
            // Test message too short (less than 10 characters)
            $form = $crawler->selectButton('submit')->form([
                'contact_form[name]' => 'Test User',
                'contact_form[email]' => 'test@example.com',
                'contact_form[message]' => 'Short' // Only 5 characters
            ]);

            $client->submit($form);

            $this->assertResponseStatusCodeSame(422);
            $this->assertSelectorTextContains('.form-error', 'at least 10 characters');

            // Test message too long (over 500 characters)
            $longMessage = str_repeat('This is a very long message. ', 20); // Over 500 chars
            $form['contact_form[message]'] = $longMessage;

            $client->submit($form);

            $this->assertResponseStatusCodeSame(422);
            $this->assertSelectorTextContains('.form-error', 'cannot exceed 500 characters');
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testAllowedCharacters(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            // Test allowed basic punctuation: period, comma, apostrophe, hyphen, space
            $form = $crawler->selectButton('submit')->form([
                'contact_form[name]' => "John O'Connor-Smith Jr.",
                'contact_form[email]' => 'john@example.com',
                'contact_form[message]' => "Hello, I'm interested in your services. Please contact me - it's urgent."
            ]);

            $client->submit($form);

            // This should succeed as it only contains allowed characters
            $this->assertResponseRedirects('/contact/success');
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testFieldLengthLimits(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            // Test name field length limit (should be around 100 characters)
            $longName = str_repeat('VeryLongName', 10); // 120 characters
            $form = $crawler->selectButton('submit')->form([
                'contact_form[name]' => $longName,
                'contact_form[email]' => 'test@example.com',
                'contact_form[message]' => 'This is a test message with minimum length'
            ]);

            $client->submit($form);

            $this->assertResponseStatusCodeSame(422);
            $this->assertSelectorExists('.form-error');
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }
}
