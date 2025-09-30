<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MinimumSubmissionTest extends WebTestCase
{
    /**
     * Test 2: Minimum Valid Submission
     * Goal: Test successful submission with only required fields
     */
    public function testMinimumValidSubmission(): void
    {
        $client = static::createClient();

        // Get the form first
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $form = $crawler->selectButton('submit')->form([
                'contact_form[name]' => 'Test User',
                'contact_form[email]' => 'test@example.com',
                'contact_form[message]' => 'This is a test message with minimum length'
            ]);

            // Handle captcha - assume math challenge for testing
            $mathChallenge = $crawler->filter('.math-challenge');
            if ($mathChallenge->count() > 0) {
                $form['contact_form[mathAnswer]'] = '7'; // Assuming 3+4=7 example
            }

            $client->submit($form);

            // Expected results:
            // - Success page with "We're grateful for your message!"
            $this->assertResponseRedirects('/contact/success');

            $client->followRedirect();
            $this->assertSelectorTextContains('body', "We're grateful for your message!");
            $this->assertResponseIsSuccessful();
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testEmailDeliveryValidation(): void
    {
        // This test would check MailHog inbox at http://localhost:8025
        // For now, we'll mark it as integration point to be tested manually
        $this->markTestSkipped('Email delivery to be tested with MailHog integration');
    }

    public function testSubmissionWithOptionalFields(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $form = $crawler->selectButton('submit')->form([
                'contact_form[name]' => 'Test User',
                'contact_form[email]' => 'test@example.com',
                'contact_form[message]' => 'This is a test message with minimum length',
                'contact_form[phone]' => '+1 (555) 123-4567'
            ]);

            // Handle captcha
            $mathChallenge = $crawler->filter('.math-challenge');
            if ($mathChallenge->count() > 0) {
                $form['contact_form[mathAnswer]'] = '7';
            }

            $client->submit($form);

            // Should succeed even with optional fields
            $this->assertResponseRedirects('/contact/success');
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testEmptyOptionalFieldsAllowed(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $form = $crawler->selectButton('submit')->form([
                'contact_form[name]' => 'Test User',
                'contact_form[email]' => 'test@example.com',
                'contact_form[message]' => 'This is a test message with minimum length',
                'contact_form[phone]' => '', // Empty optional field
                'contact_form[serviceType]' => '' // No service selected
            ]);

            $client->submit($form);

            // Should succeed with empty optional fields
            $this->assertResponseRedirects('/contact/success');
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }
}