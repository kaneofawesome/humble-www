<?php

namespace App\Service;

use Karser\Recaptcha3Bundle\Services\IpResolverInterface;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3Validator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CaptchaService
{
    private HttpClientInterface $httpClient;

    public function __construct(
        private string $recaptchaSecretKey,
        private LoggerInterface $logger,
        private RequestStack $requestStack,
        private ?Recaptcha3Validator $recaptchaValidator = null
    ) {
        $this->httpClient = HttpClient::create();
    }

    public function verifyRecaptcha(string $token, string $clientIp): bool
    {
        if (empty($this->recaptchaSecretKey) || $this->recaptchaSecretKey === 'your_secret_key_here') {
            $this->logger->warning('reCAPTCHA secret key not configured, falling back to math challenge');
            return false;
        }

        try {
            $response = $this->httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret' => $this->recaptchaSecretKey,
                    'response' => $token,
                    'remoteip' => $clientIp
                ]
            ]);

            $data = $response->toArray();

            if ($data['success'] ?? false) {
                $score = $data['score'] ?? 0;
                $action = $data['action'] ?? '';

                // For reCAPTCHA v3, we should check the score (0.0 to 1.0)
                // Higher scores indicate more likely human behavior
                if ($score >= 0.5) {
                    $this->logger->info('reCAPTCHA verification successful', [
                        'score' => $score,
                        'action' => $action,
                        'client_ip' => $clientIp
                    ]);
                    return true;
                } else {
                    $this->logger->warning('reCAPTCHA score too low', [
                        'score' => $score,
                        'action' => $action,
                        'client_ip' => $clientIp
                    ]);
                    return false;
                }
            }

            $this->logger->warning('reCAPTCHA verification failed', [
                'errors' => $data['error-codes'] ?? [],
                'client_ip' => $clientIp
            ]);

        } catch (\Exception $e) {
            $this->logger->error('reCAPTCHA verification error', [
                'error' => $e->getMessage(),
                'client_ip' => $clientIp
            ]);
        }

        return false;
    }

    public function generateMathChallenge(): array
    {
        // Generate simple math problems that are easy for humans but not bots
        $operations = [
            ['num1' => rand(1, 10), 'num2' => rand(1, 10), 'op' => '+'],
            ['num1' => rand(5, 15), 'num2' => rand(1, 5), 'op' => '-'],
            ['num1' => rand(2, 5), 'num2' => rand(2, 5), 'op' => '*'],
        ];

        $challenge = $operations[array_rand($operations)];

        // Calculate the answer
        switch ($challenge['op']) {
            case '+':
                $answer = $challenge['num1'] + $challenge['num2'];
                break;
            case '-':
                $answer = $challenge['num1'] - $challenge['num2'];
                break;
            case '*':
                $answer = $challenge['num1'] * $challenge['num2'];
                break;
            default:
                $answer = 0;
        }

        // Generate a unique ID for this challenge
        $challengeId = uniqid();

        // Store the challenge in session
        $session = $this->requestStack->getCurrentRequest()?->getSession();
        if ($session) {
            $mathChallenges = $session->get('math_challenges', []);
            $mathChallenges[$challengeId] = [
                'answer' => $answer,
                'created_at' => time()
            ];

            // Clean up old challenges (older than 10 minutes)
            $this->cleanupOldChallenges($mathChallenges);

            $session->set('math_challenges', $mathChallenges);
        }

        return [
            'id' => $challengeId,
            'question' => "What is {$challenge['num1']} {$challenge['op']} {$challenge['num2']}?",
            'answer' => $answer // Don't expose this in production
        ];
    }

    public function verifyMathChallenge(string $challengeId, $userAnswer): bool
    {
        $session = $this->requestStack->getCurrentRequest()?->getSession();
        if (!$session) {
            $this->logger->warning('No session available for math challenge verification');
            return false;
        }

        $mathChallenges = $session->get('math_challenges', []);

        if (!isset($mathChallenges[$challengeId])) {
            $this->logger->warning('Math challenge not found or expired', [
                'challenge_id' => $challengeId
            ]);
            return false;
        }

        $challenge = $mathChallenges[$challengeId];
        $correctAnswer = $challenge['answer'];

        // Remove the challenge after use
        unset($mathChallenges[$challengeId]);
        $session->set('math_challenges', $mathChallenges);

        $isCorrect = (int)$userAnswer === $correctAnswer;

        $this->logger->info('Math challenge verification', [
            'challenge_id' => $challengeId,
            'correct' => $isCorrect,
            'user_answer' => $userAnswer,
            'correct_answer' => $correctAnswer
        ]);

        return $isCorrect;
    }

    private function cleanupOldChallenges(array &$mathChallenges): void
    {
        $tenMinutesAgo = time() - 600; // 10 minutes

        foreach ($mathChallenges as $id => $challenge) {
            if ($challenge['created_at'] < $tenMinutesAgo) {
                unset($mathChallenges[$id]);
            }
        }
    }

    public function isRecaptchaConfigured(): bool
    {
        return !empty($this->recaptchaSecretKey) && $this->recaptchaSecretKey !== 'your_secret_key_here';
    }
}