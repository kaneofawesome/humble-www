<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactSuccessTest extends WebTestCase
{
    public function testSuccessPageDisplaysCorrectly(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contact/success');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorTextContains('h1', "We're grateful for your message!");
    }

    public function testSuccessPageHasReturnLink(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contact/success');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('a[href="/contact"]');
    }

    public function testSuccessPageHasBrandedContent(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contact/success');

        $this->assertResponseIsSuccessful();
        // Should contain branded messaging consistent with email themes
        $this->assertSelectorTextContains('.success-content', 'grateful');
    }
}