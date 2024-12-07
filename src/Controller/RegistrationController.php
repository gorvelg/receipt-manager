<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CreateNewPasswordType;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/admin/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        AppAuthenticator $authenticator,
        EntityManagerInterface $entityManager,
        TokenGeneratorInterface $tokenGenerator,
        MailService $mail
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $token = $tokenGenerator->generateToken();
            $user->setResetToken($token);

            $entityManager->persist($user);
            $entityManager->flush();

            // Generation de l'url
            $url = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
            $context = [
                'url' => $url,
                'username' => $user->getUsername()
            ];
            // do anything else you need here, like send an email
            $mail->sendMail(
                $user,
                'Création de votre compte',
                'create_password',
                $context
            );

            return $this->redirectToRoute('app_admin_get_user');

//            return $userAuthenticator->authenticateUser(
//                $user,
//                $authenticator,
//                $request
//            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/create-password/{token}', name: 'app_reset_password')]
    public function createNewPassword(
        Request $request,
        string $token,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        AppAuthenticator $authenticator,
        EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if (!$user) {
            throw $this->createNotFoundException('L\'utilisateur n\'existe pas.');
        }

        $form = $this->createForm(CreateNewPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $em->persist($user);
            $em->flush();

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/new_password.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
