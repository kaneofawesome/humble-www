<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Psr\Log\LoggerInterface;

class ContactEmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private LoggerInterface $logger,
        private string $businessOwnerEmail
    ) {
    }

    public function sendBusinessNotification(array $formData, string $clientIp): void
    {
        try {
            $email = (new Email())
                ->from($this->businessOwnerEmail)
                ->to($this->businessOwnerEmail)
                ->subject('New Contact Form Submission')
                ->text($this->renderBusinessNotificationText($formData, $clientIp));

            $this->mailer->send($email);

            $this->logger->info('Business notification email sent successfully', [
                'to' => $this->businessOwnerEmail,
                'client_ip' => $clientIp
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to send business notification email', [
                'error' => $e->getMessage(),
                'to' => $this->businessOwnerEmail,
                'client_ip' => $clientIp
            ]);
            throw $e;
        }
    }

    public function sendUserConfirmation(array $formData): void
    {
        if (!isset($formData['email'])) {
            return;
        }

        try {
            $email = (new Email())
                ->from($this->businessOwnerEmail)
                ->to($formData['email'])
                ->subject('✨ Your message has been received ✨')
                ->html($this->renderUserConfirmationHtml($formData))
                ->text($this->renderUserConfirmationText($formData));

            $this->mailer->send($email);

            $this->logger->info('User confirmation email sent successfully', [
                'to' => $formData['email']
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to send user confirmation email', [
                'error' => $e->getMessage(),
                'to' => $formData['email']
            ]);
            throw $e;
        }
    }

    private function renderBusinessNotificationText(array $formData, string $clientIp): string
    {
        $content = "New Contact Form Submission\n";
        $content .= str_repeat("=", 30) . "\n\n";

        $content .= "Contact Information:\n";
        $content .= "- Name: " . ($formData['name'] ?? 'Not provided') . "\n";
        $content .= "- Email: " . ($formData['email'] ?? 'Not provided') . "\n";
        $content .= "- Phone: " . ($formData['phone'] ?? 'Not provided') . "\n";
        $content .= "\n";

        $content .= "Message:\n";
        $content .= ($formData['message'] ?? 'No message provided') . "\n\n";

        if (!empty($formData['serviceType'])) {
            $content .= "Service Interest: " . ucfirst($formData['serviceType']) . "\n\n";

            if ($formData['serviceType'] === 'coaching') {
                $content .= "Coaching Information:\n";
                $content .= "- Professional Status: " . ($formData['professionalStatus'] ?? 'Not provided') . "\n";
                $content .= "- Coaching Goals: " . ($formData['coachingGoals'] ?? 'Not provided') . "\n";
            } elseif ($formData['serviceType'] === 'project') {
                $content .= "Project Information:\n";
                $content .= "- Company: " . ($formData['company'] ?? 'Not provided') . "\n";
                $content .= "- Job Role: " . ($formData['jobRole'] ?? 'Not provided') . "\n";
                $content .= "- Project Description: " . ($formData['projectDescription'] ?? 'Not provided') . "\n";
            }
            $content .= "\n";
        }

        $content .= "Technical Information:\n";
        $content .= "- Submitted from IP: " . $clientIp . "\n";
        $content .= "- Submission time: " . (new \DateTime())->format('Y-m-d H:i:s T') . "\n";

        return $content;
    }

    private function renderUserConfirmationHtml(array $formData): string
    {
        try {
            return $this->twig->render('emails/user_confirmation.html.twig', [
                'user_name' => $formData['name'] ?? 'there'
            ]);
        } catch (\Exception $e) {
            // Fallback to plain text if template fails
            return '<html><body>' . nl2br(htmlspecialchars($this->renderUserConfirmationText($formData))) . '</body></html>';
        }
    }

    private function renderUserConfirmationText(array $formData): string
    {
        try {
            return $this->twig->render('emails/user_confirmation.txt.twig', [
                'user_name' => $formData['name'] ?? 'there'
            ]);
        } catch (\Exception $e) {
            // Fallback text
            $userName = isset($formData['name']) && !empty($formData['name']) ? $formData['name'] : 'there';

            return "✨ Your message has been received ✨\n\n" .
                   "The scroll has arrived!\n\n" .
                   "Hello {$userName},\n\n" .
                   "Your message has made its way to the Humble Wizards' tower. " .
                   "We'll review it with care before sending a reply your way.\n\n" .
                   "No need to cast refresh — we'll be back in touch as soon as our reply is ready.\n\n" .
                   "Until then, may your day be a little more magical,\n" .
                   "The Humble Wizards 🧙‍♂️";
        }
    }
}
