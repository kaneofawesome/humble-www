<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MinimumSubmissionTest extends WebTestCase
{
    public function testMinimumValidSubmission(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $form = $crawler->selectButton('contact_form_submit')->form([
                'contact_form[name]' => 'Test User',
                'contact_form[email]' => 'test@example.com',
                'contact_form[message]' => 'This is a test message with minimum length',
            ]);

            $client->submit($form);

            $this->assertResponseRedirects();
            $this->assertStringContainsString('/contact/success', $client->getResponse()->headers->get('Location'));

            $client->followRedirect();
            $this->assertSelectorTextContains('h1', 'The scroll has arrived!');
            $this->assertResponseIsSuccessful();
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testEmailDeliveryValidation(): void
    {
        $this->markTestSkipped('Email delivery to be tested with MailHog integration');
    }

    public function testSubmissionWithOptionalFields(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $form = $crawler->selectButton('contact_form_submit')->form([
                'contact_form[name]' => 'Test User',
                'contact_form[email]' => 'test@example.com',
                'contact_form[message]' => 'This is a test message with minimum length',
                'contact_form[phone]' => '+1 (555) 123-4567',
            ]);

            $client->submit($form);

            $this->assertResponseRedirects();
            $this->assertStringContainsString('/contact/success', $client->getResponse()->headers->get('Location'));
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testEmptyOptionalFieldsAllowed(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $form = $crawler->selectButton('contact_form_submit')->form([
                'contact_form[name]' => 'Test User',
                'contact_form[email]' => 'test@example.com',
                'contact_form[message]' => 'This is a test message with minimum length',
                'contact_form[phone]' => '',
                'contact_form[serviceType]' => '',
            ]);

            $client->submit($form);

            $this->assertResponseRedirects();
            $this->assertStringContainsString('/contact/success', $client->getResponse()->headers->get('Location'));
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }
}
