<?php

namespace App\Controller;

use App\Entity\HumbleProfile;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(): never
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);

        try {
            $form->handleRequest($request);
        } catch (\Doctrine\DBAL\Exception $e) {
            $this->addFlash('error', 'Unable to process registration. Please try again later.');
            return $this->render('security/register.html.twig', [
                'registrationForm' => $form,
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $existingUser = $userRepository->findByEmail($email);

            if ($existingUser !== null) {
                if ($existingUser->getHumbleProfile() !== null) {
                    $this->addFlash('error', 'An account with this email already exists. Please log in.');
                    return $this->redirectToRoute('app_login');
                }

                // User exists from rust-tutor but has no humble profile — link them
                $profile = new HumbleProfile();
                $profile->setUser($existingUser);
                $entityManager->persist($profile);
                $entityManager->flush();

                $this->addFlash('success', 'Your existing Humble Wizards account has been linked. Please log in with your existing password.');
                return $this->redirectToRoute('app_login');
            }

            try {
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                $profile = new HumbleProfile();
                $profile->setUser($user);

                $entityManager->persist($user);
                $entityManager->persist($profile);
                $entityManager->flush();

                $this->addFlash('success', 'Account created successfully! Please log in.');
                return $this->redirectToRoute('app_login');
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'An account with this email already exists.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while creating your account. Please try again.');
            }
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
