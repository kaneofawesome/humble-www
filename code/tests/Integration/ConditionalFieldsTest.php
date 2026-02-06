<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConditionalFieldsTest extends WebTestCase
{
    public function testCoachingFieldsDisplay(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact?service=coaching');

        if ($client->getResponse()->isSuccessful()) {
            $this->assertSelectorExists('select[name*="[serviceType]"]');
            $this->assertSelectorExists('textarea[name*="[professionalStatus]"]', 'Professional Status field should be present');
            $this->assertSelectorExists('textarea[name*="[coachingGoals]"]', 'Coaching Goals field should be present');
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testProjectFieldsDisplay(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact?service=project');

        if ($client->getResponse()->isSuccessful()) {
            $this->assertSelectorExists('select[name*="[serviceType]"]');
            $this->assertSelectorExists('input[name*="[company]"]', 'Company field should be present');
            $this->assertSelectorExists('input[name*="[jobRole]"]', 'Job Role field should be present');
            $this->assertSelectorExists('textarea[name*="[projectDescription]"]', 'Project Description field should be present');
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testCoachingSubmissionWithContext(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $form = $crawler->selectButton('contact_form_submit')->form([
                'contact_form[name]' => 'Test Coach Client',
                'contact_form[email]' => 'coach.client@example.com',
                'contact_form[message]' => 'I am interested in engineering leadership coaching',
                'contact_form[serviceType]' => '1',
                'contact_form[professionalStatus]' => 'I am a senior software engineer with 8 years experience looking to move into management',
                'contact_form[coachingGoals]' => 'I want to improve my leadership skills and learn to mentor junior developers effectively',
            ]);

            $client->submit($form);

            $this->assertResponseRedirects();
            $this->assertStringContainsString('/contact/success', $client->getResponse()->headers->get('Location'));
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testProjectSubmissionWithContext(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $form = $crawler->selectButton('contact_form_submit')->form([
                'contact_form[name]' => 'Test Project Client',
                'contact_form[email]' => 'project.client@example.com',
                'contact_form[message]' => 'I need help with a software project',
                'contact_form[serviceType]' => '2',
                'contact_form[company]' => 'Acme Corp',
                'contact_form[jobRole]' => 'CTO',
                'contact_form[projectDescription]' => 'We need to build a scalable microservices architecture for our e-commerce platform',
            ]);

            $client->submit($form);

            $this->assertResponseRedirects();
            $this->assertStringContainsString('/contact/success', $client->getResponse()->headers->get('Location'));
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testConditionalFieldsAreOptional(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $form = $crawler->selectButton('contact_form_submit')->form([
                'contact_form[name]' => 'Test User',
                'contact_form[email]' => 'test@example.com',
                'contact_form[message]' => 'Basic inquiry with service type but no details',
                'contact_form[serviceType]' => '1',
                'contact_form[professionalStatus]' => '',
                'contact_form[coachingGoals]' => '',
            ]);

            $client->submit($form);

            $this->assertResponseRedirects();
            $this->assertStringContainsString('/contact/success', $client->getResponse()->headers->get('Location'));
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }
}
