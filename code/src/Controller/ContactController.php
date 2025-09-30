<?php

namespace App\Controller;

use App\Form\ContactFormType;
use App\Repository\RateLimitEntryRepository;
use App\Service\CaptchaService;
use App\Service\ContactEmailService;
use App\Service\RateLimitService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    public function __construct(
        private ContactEmailService $emailService,
        private CaptchaService $captchaService,
        private RateLimitService $rateLimitService
    ) {
    }

    #[Route('/contact', name: 'contact', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $form = $this->createForm(ContactFormType::class);

        $mathChallenge = $this->captchaService->generateMathChallenge();

        return $this->render('contact/index.html.twig', [
            'pageTitle' => 'Contact - Humble',
            'form' => $form->createView(),
            'fields' => $this->getFormFields(),
            'math_challenge' => $mathChallenge['question'],
            'math_challenge_id' => $mathChallenge['id'],
            'recaptcha_site_key' => $this->getParameter('app.recaptcha_site_key'),
        ]);
    }

    #[Route('/contact', name: 'contact_submit', methods: ['POST'])]
    public function submit(Request $request): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->redirectToRoute('contact');
        }

        $clientIp = $request->getClientIp();

        // Check rate limiting with graceful fallback
        try {
            $isRateLimited = $this->rateLimitService->isRateLimited($clientIp);
            if ($isRateLimited) {
                $this->addFlash('error', 'You have exceeded the submission limit of 5 messages per hour. Please try again later.');
                return $this->render('contact/index.html.twig', [
                    'pageTitle' => 'Contact - Humble',
                    'form' => $form->createView(),
                    'fields' => $this->getFormFields(),
                    'math_challenge' => $this->captchaService->generateMathChallenge()['question'],
                    'math_challenge_id' => '',
                    'recaptcha_site_key' => $this->getParameter('app.recaptcha_site_key'),
                    'rate_limited' => true,
                ]);
            }
        } catch (\Exception $e) {
            if (!$this->rateLimitService->isAvailable()) {
                $this->addFlash('info', 'Note: ' . $this->rateLimitService->getStatusMessage() . '. Your submission will still be processed.');
            }
        }

        $captchaToken = $form->get('captchaToken')->getData();
        $mathAnswer = $form->get('mathChallengeAnswer')->getData();
        $mathChallengeId = $request->request->get('math_challenge_id');

        $captchaValid = false;
        if ($captchaToken) {
            $captchaValid = $this->captchaService->verifyRecaptcha($captchaToken, $clientIp);
        } elseif ($mathAnswer && $mathChallengeId) {
            $captchaValid = $this->captchaService->verifyMathChallenge($mathChallengeId, $mathAnswer);
        }

        if (!$captchaValid) {
            $this->addFlash('error', 'Please complete the security verification.');
            $mathChallenge = $this->captchaService->generateMathChallenge();
            return $this->render('contact/index.html.twig', [
                'pageTitle' => 'Contact - Humble',
                'form' => $form->createView(),
                'fields' => $this->getFormFields(),
                'math_challenge' => $mathChallenge['question'],
                'math_challenge_id' => $mathChallenge['id'],
                'recaptcha_site_key' => $this->getParameter('app.recaptcha_site_key'),
                'captcha_error' => true,
            ]);
        }

        if (!$form->isValid()) {
            $mathChallenge = $this->captchaService->generateMathChallenge();
            return $this->render('contact/index.html.twig', [
                'pageTitle' => 'Contact - Humble',
                'form' => $form->createView(),
                'fields' => $this->getFormFields(),
                'math_challenge' => $mathChallenge['question'],
                'math_challenge_id' => $mathChallenge['id'],
                'recaptcha_site_key' => $this->getParameter('app.recaptcha_site_key'),
            ]);
        }

        $formData = $form->getData();

        try {
            $this->emailService->sendBusinessNotification($formData, $clientIp);
            $this->emailService->sendUserConfirmation($formData);

            try {
                $this->rateLimitService->updateSubmissionCount($clientIp);
            } catch (\Exception $e) {
                // Rate limit update failed, but submission was successful
                // Continue with successful response
            }

            return $this->redirectToRoute('contact_success', [
                'name' => $formData['name'] ?? 'there'
            ]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'There was an error sending your message. Please try again.');

            $mathChallenge = $this->captchaService->generateMathChallenge();
            return $this->render('contact/index.html.twig', [
                'pageTitle' => 'Contact - Humble',
                'form' => $form->createView(),
                'fields' => $this->getFormFields(),
                'math_challenge' => $mathChallenge['question'],
                'math_challenge_id' => $mathChallenge['id'],
                'recaptcha_site_key' => $this->getParameter('app.recaptcha_site_key'),
                'system_error' => true,
            ]);
        }
    }

    #[Route('/contact/success', name: 'contact_success', methods: ['GET'])]
    public function success(Request $request): Response
    {
        $name = $request->query->get('name', 'there');

        return $this->render('contact/confirmation.html.twig', [
            'user_name' => $name
        ]);
    }

    private function getFormFields(): array
    {
        return [
            'basic' => [
                'title' => 'Basic Information',
                'fields' => [
                    [
                        'name' => 'name',
                        'type' => 'input',
                        'css_class' => 'form-input'
                    ],
                    [
                        'name' => 'email',
                        'type' => 'input',
                        'css_class' => 'form-input'
                    ],
                    [
                        'name' => 'message',
                        'type' => 'textarea',
                        'css_class' => 'form-textarea'
                    ],
                    [
                        'name' => 'serviceType',
                        'type' => 'select',
                        'css_class' => 'form-select service-type-selector'
                    ],
                    [
                        'name' => 'phone',
                        'type' => 'input',
                        'css_class' => 'form-input'
                    ]
                ]
            ],
            'conditional' => [
                'coaching' => [
                    'title' => '💼 Leadership Coaching Information',
                    'container_id' => 'coaching-fields',
                    'fields' => [
                        [
                            'name' => 'professionalStatus',
                            'type' => 'textarea',
                            'css_class' => 'form-textarea'
                        ],
                        [
                            'name' => 'coachingGoals',
                            'type' => 'textarea',
                            'css_class' => 'form-textarea'
                        ]
                    ]
                ],
                'project' => [
                    'title' => '🔧 Project Information',
                    'container_id' => 'project-fields',
                    'fields' => [
                        [
                            'name' => 'company',
                            'type' => 'input',
                            'css_class' => 'form-input'
                        ],
                        [
                            'name' => 'jobRole',
                            'type' => 'input',
                            'css_class' => 'form-input'
                        ],
                        [
                            'name' => 'projectDescription',
                            'type' => 'textarea',
                            'css_class' => 'form-textarea'
                        ]
                    ]
                ]
            ]
        ];
    }
}
