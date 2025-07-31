<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationForm;
use App\Security\EmailVerifier;
use Symfony\Component\Mime\Address;
use App\Form\ResetPasswordRequestForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            
            $user->setDateOfRegister(new \DateTime());
            $user->setRoles(["ROLE_USER"]);
            $user->setFirstConnection(true);
            $user->setIsBanned(false);


            $entityManager->persist($user);
            $entityManager->flush();

            
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('admin@meetandrun.fr', 'Admin Meet&Run'))
                    ->to((string) $user->getEmail())
                    ->subject('Veuillez confirmer votre email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            

            return $security->login($user, 'form_login', 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            /** @var User $user */
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        
        $this->addFlash('success', 'Votre mail a été verifié avec succès');

        return $this->redirectToRoute('app_home');
    }

    #[Route('/verify/resend', name: 'app_verify_email_resend')]
    public function resendEmail(
        EmailVerifierInterface $emailVerifier,
        UserInterface $user
    ): Response {
        if ($user->isVerified()) {
            $this->addFlash('info', 'Ton adresse email est déjà vérifiée.');
            return $this->redirectToRoute('app_home');
        }

        $emailVerifier->sendEmailConfirmation(
            'app_verify_email', // ta route de confirmation
            $user,
            (new TemplatedEmail())
                ->from(new Address('noreply@monsite.fr', 'Nom de ton site'))
                ->to($user->getEmail())
                ->subject('Confirme ton adresse e-mail')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );

        $this->addFlash('success', 'Un nouvel email de confirmation t’a été envoyé.');
        return $this->redirectToRoute('app_home');
    }
}
