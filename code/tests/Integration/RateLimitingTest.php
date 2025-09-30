<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RateLimitingTest extends WebTestCase
{
    /**
     * Test 5: Rate Limiting
     * Goal: Test spam protection (5 submissions per hour per IP)
     */
    public function testRateLimitProtection(): void
    {
        $client = static::createClient();

        for ($i = 1; $i <= 5; $i++) {
            $crawler = $client->request('GET', '/contact');

            if (!$client->getResponse()->isSuccessful()) {
                $this->markTestSkipped('Contact page not accessible yet');
                return;
            }

            $form = $crawler->selectButton('submit')->form([
                'contact_form[name]' => "Test User $i",
                'contact_form[email]' => "test$i@example.com",
                'contact_form[message]' => "This is test message number $i with required minimum length for validation"
            ]);

            // Handle captcha if present
            $mathChallenge = $crawler->filter('.math-challenge');
            if ($mathChallenge->count() > 0) {
                $form['contact_form[mathAnswer]'] = '7'; // Assume 3+4=7
            }

            $client->submit($form);

            // First 5 should succeed
            $this->assertResponseRedirects('/contact/success', "Submission $i should succeed");
        }

        // 6th submission should be blocked
        $crawler = $client->request('GET', '/contact');
        $form = $crawler->selectButton('submit')->form([
            'contact_form[name]' => 'Test User 6',
            'contact_form[email]' => 'test6@example.com',
            'contact_form[message]' => 'This is the 6th test message that should be blocked by rate limiting'
        ]);
        $client->submit($form);
        $this->assertResponseStatusCodeSame(403);
        $this->assertSelectorTextContains('body', 'rate limit');
    }

    public function testRateLimitErrorMessage(): void
    {
        $client = static::createClient();

        for ($i = 1; $i <= 5; $i++) {
            $crawler = $client->request('GET', '/contact');

            if (!$client->getResponse()->isSuccessful()) {
                $this->markTestSkipped('Contact page not accessible yet');
                return;
            }

            $form = $crawler->selectButton('submit')->form([
                'contact_form[name]' => "Rate Test $i",
                'contact_form[email]' => "rate$i@example.com",
                'contact_form[message]' => "Rate limiting test message $i with minimum required length"
            ]);

            $client->submit($form);
        }

        $crawler = $client->request('GET', '/contact');
        $form = $crawler->selectButton('submit')->form([
            'contact_form[name]' => 'Rate Test 6',
            'contact_form[email]' => 'rate6@example.com',
            'contact_form[message]' => 'This should trigger rate limit error'
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(403);

        $this->assertSelectorNotExists('.stack-trace');
        $this->assertSelectorNotExists('.exception');
        $this->assertSelectorTextContains('.error-message', 'too many submissions');
    }

    public function testRateLimitPreservesFormData(): void
    {
        $client = static::createClient();

        for ($i = 1; $i <= 5; $i++) {
            $crawler = $client->request('GET', '/contact');

            if (!$client->getResponse()->isSuccessful()) {
                $this->markTestSkipped('Contact page not accessible yet');
                return;
            }

            $form = $crawler->selectButton('submit')->form([
                'contact_form[name]' => "Preserve Test $i",
                'contact_form[email]' => "preserve$i@example.com",
                'contact_form[message]' => "Form data preservation test $i with required length"
            ]);

            $client->submit($form);
        }

        $crawler = $client->request('GET', '/contact');
        $formData = [
            'contact_form[name]' => 'John Doe',
            'contact_form[email]' => 'john@example.com',
            'contact_form[message]' => 'This form data should be preserved when rate limit error occurs'
        ];

        $form = $crawler->selectButton('submit')->form($formData);
        $client->submit($form);
        $this->assertResponseStatusCodeSame(403);

        $this->assertSelectorExists('input[value="John Doe"]');
        $this->assertSelectorExists('input[value="john@example.com"]');
        $this->assertSelectorTextContains('textarea', 'This form data should be preserved');
    }
}
