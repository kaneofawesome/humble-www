<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FormDisplayTest extends WebTestCase
{
    /**
     * Test 1: Basic Form Display
     * Goal: Verify form loads correctly with required fields
     */
    public function testBasicFormDisplay(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        // Verify HTTPS redirect (if testing on HTTP)
        $this->assertTrue(
            $client->getResponse()->isSuccessful() || $client->getResponse()->isRedirection(),
            'Contact page should load or redirect to HTTPS'
        );

        if ($client->getResponse()->isRedirection()) {
            $this->assertStringContains('https:', $client->getResponse()->headers->get('Location'));
        }

        if (!$client->getResponse()->isRedirection()) {
            // Confirm form displays with required fields
            $this->assertSelectorExists('form', 'Contact form should exist');
            $this->assertSelectorExists('input[name*="[name]"]', 'Name field should be present');
            $this->assertSelectorExists('input[name*="[email]"]', 'Email field should be present');
            $this->assertSelectorExists('textarea[name*="[message]"]', 'Message field should be present');
            $this->assertSelectorExists('select[name*="[serviceType]"]', 'Service type dropdown should be present');
            $this->assertSelectorExists('button[type="submit"]', 'Submit button should be present');

            // Verify reCAPTCHA widget or math challenge
            $hasRecaptcha = $crawler->filter('[data-sitekey]')->count() > 0;
            $hasMathChallenge = $crawler->filter('.math-challenge')->count() > 0;
            $this->assertTrue(
                $hasRecaptcha || $hasMathChallenge,
                'Form should have reCAPTCHA widget or math challenge'
            );
        }
    }

    public function testFormFieldLabels(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $this->assertSelectorExists('label[for*="name"]');
            $this->assertSelectorExists('label[for*="email"]');
            $this->assertSelectorExists('label[for*="message"]');

            // Check for required field indicators
            $this->assertSelectorTextContains('label[for*="name"]', 'Name');
            $this->assertSelectorTextContains('label[for*="email"]', 'Email');
            $this->assertSelectorTextContains('label[for*="message"]', 'Message');
        }
    }

    public function testFormValidationAttributes(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            // Required fields should have required attribute
            $this->assertSelectorExists('input[name*="[name]"][required]');
            $this->assertSelectorExists('input[name*="[email]"][required]');
            $this->assertSelectorExists('textarea[name*="[message]"][required]');

            // Message field should have length constraints
            $this->assertSelectorExists('textarea[name*="[message]"][minlength="10"]');
            $this->assertSelectorExists('textarea[name*="[message]"][maxlength="500"]');
        }
    }
}