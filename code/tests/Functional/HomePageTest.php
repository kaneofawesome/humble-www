<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomePageTest extends WebTestCase
{
    public function testGetInTouchButtonNavigatesToContactPage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        // Check that we're on the home page
        $this->assertResponseIsSuccessful();

        // Check button exists
        $button = $crawler->selectLink('Get In Touch');
        $this->assertCount(1, $button, 'Get In Touch button should exist on home page');

        // Follow the link
        $client->click($button->link());

        // Verify we're on the contact page
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Get in Touch');

        // Verify the URL is correct
        $this->assertStringContainsString('/contact', $client->getRequest()->getRequestUri());
    }

    public function testGetInTouchButtonIsAccessible(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        // Check that the button has proper accessibility attributes
        $button = $crawler->filter('a:contains("Get In Touch")');
        $this->assertCount(1, $button, 'Get In Touch button should exist');

        // Verify it's a proper link element that can be keyboard navigated
        $this->assertEquals('a', $button->nodeName(), 'Get In Touch should be an anchor element for accessibility');

        // Check that it has an href attribute
        $href = $button->attr('href');
        $this->assertNotNull($href, 'Get In Touch button should have an href attribute');
    }
}