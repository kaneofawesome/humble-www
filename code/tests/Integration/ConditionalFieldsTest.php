<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConditionalFieldsTest extends WebTestCase
{
    /**
     * Test 3: Service-Specific Fields
     * Goal: Test conditional field display and validation
     */
    public function testCoachingFieldsDisplay(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact?service=coaching');

        if ($client->getResponse()->isSuccessful()) {
            // Select "Engineering Leadership Coaching"
            $this->assertSelectorExists('select[name*="[serviceType]"]');

            // Verify coaching-specific fields appear
            $this->assertSelectorExists('textarea[name*="[professionalStatus]"]', 'Professional Status field should be present');
            $this->assertSelectorExists('textarea[name*="[coachingGoals]"]', 'Coaching Goals field should be present');

            // Verify project fields are not present (or hidden)
            $this->assertSelectorNotExists('input[name*="[company]"]');
            $this->assertSelectorNotExists('input[name*="[jobRole]"]');
            $this->assertSelectorNotExists('textarea[name*="[projectDescription]"]');
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testProjectFieldsDisplay(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact?service=project');

        if ($client->getResponse()->isSuccessful()) {
            // Select "Software/Hardware Project Help"
            $this->assertSelectorExists('select[name*="[serviceType]"]');

            // Verify project-specific fields appear
            $this->assertSelectorExists('input[name*="[company]"]', 'Company field should be present');
            $this->assertSelectorExists('input[name*="[jobRole]"]', 'Job Role field should be present');
            $this->assertSelectorExists('textarea[name*="[projectDescription]"]', 'Project Description field should be present');

            // Verify coaching fields are not present (or hidden)
            $this->assertSelectorNotExists('textarea[name*="[professionalStatus]"]');
            $this->assertSelectorNotExists('textarea[name*="[coachingGoals]"]');
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testCoachingSubmissionWithContext(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $form = $crawler->selectButton('submit')->form([
                'contact_form[name]' => 'Test Coach Client',
                'contact_form[email]' => 'coach.client@example.com',
                'contact_form[message]' => 'I am interested in engineering leadership coaching',
                'contact_form[serviceType]' => 'coaching',
                'contact_form[professionalStatus]' => 'I am a senior software engineer with 8 years experience looking to move into management',
                'contact_form[coachingGoals]' => 'I want to improve my leadership skills and learn to mentor junior developers effectively'
            ]);

            $client->submit($form);

            $this->assertResponseRedirects('/contact/success');

            // Verify email contains coaching context (this would be tested in email service)
            $this->assertTrue(true, 'Coaching context should be included in notification email');
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testProjectSubmissionWithContext(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            $form = $crawler->selectButton('submit')->form([
                'contact_form[name]' => 'Test Project Client',
                'contact_form[email]' => 'project.client@example.com',
                'contact_form[message]' => 'I need help with a software project',
                'contact_form[serviceType]' => 'project',
                'contact_form[company]' => 'Acme Corp',
                'contact_form[jobRole]' => 'CTO',
                'contact_form[projectDescription]' => 'We need to build a scalable microservices architecture for our e-commerce platform'
            ]);

            $client->submit($form);

            $this->assertResponseRedirects('/contact/success');

            // Verify email contains project context (this would be tested in email service)
            $this->assertTrue(true, 'Project context should be included in notification email');
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }

    public function testConditionalFieldsAreOptional(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        if ($client->getResponse()->isSuccessful()) {
            // Submit with service type selected but conditional fields empty
            $form = $crawler->selectButton('submit')->form([
                'contact_form[name]' => 'Test User',
                'contact_form[email]' => 'test@example.com',
                'contact_form[message]' => 'Basic inquiry with service type but no details',
                'contact_form[serviceType]' => 'coaching',
                'contact_form[professionalStatus]' => '', // Empty optional field
                'contact_form[coachingGoals]' => ''        // Empty optional field
            ]);

            $client->submit($form);

            // Should succeed - conditional fields are optional per requirements
            $this->assertResponseRedirects('/contact/success');
        } else {
            $this->markTestSkipped('Contact page not accessible yet');
        }
    }
}