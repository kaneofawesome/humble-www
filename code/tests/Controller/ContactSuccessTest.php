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
        $this->assertSelectorTextContains('h1', 'The scroll has arrived!');
    }

    public function testSuccessPageHasReturnLink(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact/success');

        $this->assertResponseIsSuccessful();
        // Confirmation page has "Return Home" and "Explore Services" links
        $this->assertSelectorExists('a.btn-primary');
        $this->assertSelectorExists('a.btn-secondary');
    }

    public function testSuccessPageHasBrandedContent(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contact/success');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.confirmation-message', 'Humble Wizards');
    }
}
