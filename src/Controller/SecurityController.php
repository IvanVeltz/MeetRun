<?php

namespace App\Controller;

use App\Form\ResetPasswordForm;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use App\Form\ResetPasswordRequestForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Karser\Recaptcha3Bundle\Twig\Recaptcha3Extension;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SecurityController extends AbstractController
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer) {
        $this->mailer = $mailer;
    }

    
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/login/check-google', name: 'google_check')]
    public function googleCheck()
    {
        // Symfony prend automatiquement en charge la réponse OAuth
    }

    #[Route('/forgottenPassword', 'app_forgotten_password')]
    public function forgottenPassword(
        Request $request,
        UserRepository $userRepository,
        TokenGeneratorInterface $tokenGenerator,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer


    ): Response
    {
        $form = $this->createForm(ResetPasswordRequestForm::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            // Le formulaire est envoyé ET valide
            // On va chercher l'utilisateur
            $user = $userRepository->findOneByEmail($form->get('email')->getData());

            // n verifie si on a bien un utilisateur
            if($user){
                // On a un utilisateur
                // Génération du token de réinitialisation
                $resetToken = $tokenGenerator->generateToken();
                $user->setResetToken($resetToken);
                $entityManager->persist($user);
                $entityManager->flush();

                // On genere l'url vers resetPassword
                $url =  $this->generateUrl('app_reset_password', ['token' => $resetToken],
                UrlGeneratorInterface::ABSOLUTE_URL);

                $email = (new TemplatedEmail())
                    ->from(new Address('admin@meetandrun.fr', 'Admin Meet&Run'))
                    ->to($user->getEmail())
                    ->subject('Récupération de mot de passe')
                    ->htmlTemplate('emails/password_reset.html.twig')
                    ->context([
                        'user' => $user,
                        'url' => $url
                    ]);

                $this->mailer->send($email);

                $this->addFlash('success', 'E-mail envoyé avec succès');
                return $this->redirectToRoute('app_login');

            }

            // On a pas d'utilisateur
            $this->addFlash('danger', 'Un problème est survenue');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password_request.html.twig', [
            'requestPassForm' => $form
        ]);
    }

    #[Route('/forgottenPassword/{token}', 'app_reset_password')]
    public function resetPassword(
        string $token,
        UserRepository $userRepository,
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager
        ): Response 
        {
            // Vérifier si un utilisateur correspond à ce token
            $user = $userRepository->findOneByResetToken($token);

            if ($user) {
                $form = $this->createForm(ResetPasswordForm::class);

                $form->handleRequest($request);

                if($form->isSubmitted() && $form->isValid()){
                    $user->setPassword(
                        $userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData())
                    );

                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->addFlash('success', 'Mot de passe modifié avec succès');
                    return $this->redirectToRoute('app_login');

                }
                // Ici, on peut afficher un formulaire pour changer le mot de passe
                return $this->render('security/reset_password.html.twig', [
                    'passForm' => $form
                ]);
            }

            $this->addFlash('danger', 'Token invalide ou expiré.');
            return $this->redirectToRoute('app_login');
        }
}
