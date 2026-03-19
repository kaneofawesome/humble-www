<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ResetPasswordControllerTest extends WebTestCase
{
    public function testForgotPasswordPageRendersSuccessfully(): void
    {
        $client = static::createClient();
        $client->request('GET', '/reset-password');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.auth-title', 'Forgot Password');
    }

    public function testForgotPasswordPageHasEmailField(): void
    {
        $client = static::createClient();
        $client->request('GET', '/reset-password');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('input[type="email"]');
        $this->assertSelectorExists('button[type="submit"]');
    }

    public function testForgotPasswordPageHasBackToLoginLink(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/reset-password');

        $this->assertResponseIsSuccessful();
        $loginLink = $crawler->filter('a[href="/login"]');
        $this->assertGreaterThan(0, $loginLink->count(), 'Forgot password page should have a link back to login');
    }

    public function testForgotPasswordPageUsesAuthCardStyling(): void
    {
        $client = static::createClient();
        $client->request('GET', '/reset-password');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.auth-section');
        $this->assertSelectorExists('.auth-container');
        $this->assertSelectorExists('.auth-card');
    }

    public function testForgotPasswordPageIsPubliclyAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/reset-password');

        // Should not redirect to login
        $this->assertResponseIsSuccessful();
    }

    public function testCheckEmailPageIsPubliclyAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/reset-password/check-email');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.auth-title', 'Check Your Email');
    }

    public function testCheckEmailPageUsesAuthCardStyling(): void
    {
        $client = static::createClient();
        $client->request('GET', '/reset-password/check-email');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.auth-section');
        $this->assertSelectorExists('.auth-card');
    }

    public function testLoginPageHasForgotPasswordLink(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $forgotLink = $crawler->filter('a[href="/reset-password"]');
        $this->assertGreaterThan(0, $forgotLink->count(), 'Login page should have a forgot password link');
    }

    public function testLoginForgotPasswordLinkText(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $forgotLink = $crawler->filter('a[href="/reset-password"]');
        $this->assertStringContainsString('Forgot', $forgotLink->text());
    }

    public function testResetPasswordWithInvalidTokenRedirects(): void
    {
        $client = static::createClient();
        $client->request('GET', '/reset-password/reset/invalid-token');

        // Token gets stored in session, redirects to the reset route without token in URL
        $this->assertResponseRedirects('/reset-password/reset');
    }
}
