<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Your Name *',
                'attr' => [
                    'placeholder' => 'Enter your full name',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Please enter your name'),
                    new Assert\Length(
                        min: 1,
                        max: 100,
                        minMessage: 'Your name must be at least {{ limit }} character',
                        maxMessage: 'Your name cannot be longer than {{ limit }} characters'
                    ),
                    new Assert\Regex(
                        pattern: '/^[a-zA-Z0-9\s.,\'-]+$/',
                        message: 'Your name contains invalid characters'
                    )
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email Address *',
                'attr' => [
                    'placeholder' => 'your.email@example.com',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Please enter your email address'),
                    new Assert\Email(message: 'Please enter a valid email address'),
                    new Assert\Regex(
                        pattern: '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                        message: 'Please enter a valid email address with ASCII characters only'
                    )
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Your Message *',
                'attr' => [
                    'placeholder' => 'Tell us how we can help you (10-500 characters)',
                    'rows' => 4,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Please enter your message'),
                    new Assert\Length(
                        min: 10,
                        max: 500,
                        minMessage: 'Your message must be at least {{ limit }} characters',
                        maxMessage: 'Your message cannot be longer than {{ limit }} characters'
                    ),
                    new Assert\Regex(
                        pattern: '/^[a-zA-Z0-9\s.,\'\-\!\?\:\;\(\)]+$/',
                        message: 'Your message contains invalid characters'
                    )
                ]
            ])
            ->add('serviceType', ChoiceType::class, [
                'label' => 'What can we help you with?',
                'choices' => [
                    'General inquiry' => null,
                    'Engineering leadership coaching' => 'coaching',
                    'Software/hardware project assistance' => 'project',
                ],
                'placeholder' => 'Select a service type (optional)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control service-type-selector',
                    'data-toggle-fields' => 'true'
                ]
            ])
            ->add('phone', TelType::class, [
                'label' => 'Phone Number',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Optional phone number',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\Regex(
                        pattern: '/^[a-zA-Z0-9\s+\-\(\)\.]+$/',
                        message: 'Please enter a valid phone number'
                    )
                ]
            ])
            ->add('company', TextType::class, [
                'label' => 'Company Name',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Your company name (for project inquiries)',
                    'class' => 'form-control project-field',
                    'style' => 'display: none;'
                ],
                'constraints' => [
                    new Assert\Length(
                        max: 100,
                        maxMessage: 'Company name cannot be longer than {{ limit }} characters'
                    ),
                    new Assert\Regex(
                        pattern: '/^[a-zA-Z0-9\s.,\'\-&]+$/',
                        message: 'Company name contains invalid characters'
                    )
                ]
            ])
            ->add('jobRole', TextType::class, [
                'label' => 'Job Title/Role',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Your role (for project inquiries)',
                    'class' => 'form-control project-field',
                    'style' => 'display: none;'
                ],
                'constraints' => [
                    new Assert\Length(
                        max: 100,
                        maxMessage: 'Job role cannot be longer than {{ limit }} characters'
                    ),
                    new Assert\Regex(
                        pattern: '/^[a-zA-Z0-9\s.,\'\-\/]+$/',
                        message: 'Job role contains invalid characters'
                    )
                ]
            ])
            ->add('projectDescription', TextareaType::class, [
                'label' => 'Project Details',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Brief description of your project (10-1000 characters)',
                    'rows' => 3,
                    'class' => 'form-control project-field',
                    'style' => 'display: none;'
                ],
                'constraints' => [
                    new Assert\Length(
                        min: 10,
                        max: 1000,
                        minMessage: 'Project description must be at least {{ limit }} characters when provided',
                        maxMessage: 'Project description cannot be longer than {{ limit }} characters'
                    ),
                    new Assert\Regex(
                        pattern: '/^[a-zA-Z0-9\s.,\'\-\!\?\:\;\(\)\/]+$/',
                        message: 'Project description contains invalid characters'
                    )
                ]
            ])
            ->add('professionalStatus', TextareaType::class, [
                'label' => 'Professional Background',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Tell us about your current role and experience (10-500 characters)',
                    'rows' => 3,
                    'class' => 'form-control coaching-field',
                    'style' => 'display: none;'
                ],
                'constraints' => [
                    new Assert\Length(
                        min: 10,
                        max: 500,
                        minMessage: 'Professional status must be at least {{ limit }} characters when provided',
                        maxMessage: 'Professional status cannot be longer than {{ limit }} characters'
                    ),
                    new Assert\Regex(
                        pattern: '/^[a-zA-Z0-9\s.,\'\-\!\?\:\;\(\)\/]+$/',
                        message: 'Professional background contains invalid characters'
                    )
                ]
            ])
            ->add('coachingGoals', TextareaType::class, [
                'label' => 'Coaching Goals',
                'required' => false,
                'attr' => [
                    'placeholder' => 'What specific areas would you like coaching support with? (10-500 characters)',
                    'rows' => 3,
                    'class' => 'form-control coaching-field',
                    'style' => 'display: none;'
                ],
                'constraints' => [
                    new Assert\Length(
                        min: 10,
                        max: 500,
                        minMessage: 'Coaching goals must be at least {{ limit }} characters when provided',
                        maxMessage: 'Coaching goals cannot be longer than {{ limit }} characters'
                    ),
                    new Assert\Regex(
                        pattern: '/^[a-zA-Z0-9\s.,\'\-\!\?\:\;\(\)\/]+$/',
                        message: 'Coaching goals contains invalid characters'
                    )
                ]
            ])
            ->add('captchaToken', HiddenType::class, [
                'mapped' => false,
                'required' => false
            ])
            ->add('mathChallengeAnswer', HiddenType::class, [
                'mapped' => false,
                'required' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Send Message ✨',
                'attr' => [
                    'class' => 'btn btn-primary btn-lg'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'contact_form',
        ]);
    }
}