<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordForm;
use App\Repository\UserRepository;
use App\Repository\EventRepository;
use Symfony\Component\Mime\Address;
use App\Repository\FollowRepository;
use App\Form\ResetPasswordRequestForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SecurityController extends AbstractController
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer,) {
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

    #[Route('/user/delete/{id}', name: 'app_delete_user', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function deleteUser(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        FollowRepository $followRepository,
        EventRepository $eventRepository,
        TokenStorageInterface $tokenStorage
    ):Response
    {
        $currentUser = $this->getUser();

        // On verifie qu'on supprime bien le bon user
        if($currentUser !== $user){
            $this->addFlash('error', 'Vous ne pouvez pas supprimer un autre utilisateur');
            return $this->redirectToRoute('app_user');
        }

        // On verifie que le CSRF_token est bien conforme avec celui créé par le formulaire de la vue, si ce n'est
        // pas le cas on bloque la requete 
        if (!$this->isCsrfTokenValid('delete-user'.$user->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('CSRF invalide');
        }

        // Si tout est ok, on anonymise l'user
        $deletedId = uniqid('user_deleted_');
        $user->setFirstName($deletedId);
        $user->setLastName($deletedId);
        $user->setEmail($deletedId.'@deleted.fr');
        $user->setPassword(null);
        $user->setDateOfBirth(null);
        $user->setPostalCode(null);
        $user->setCity(null);
        $user->setLongitude(null);
        $user->setLatitude(null);
        $user->setBio(null);
        $user->setSexe(null);
        $user->setLevel(null);
        $user->setDeleted(true);

        // On supprime les follows, et on le supprime dans les follow des followers
        $follows = $followRepository->findBy(['userSource' => $user->getId()]);
        foreach($follows as $follow){
            $entityManager->remove($follow);
        }

        $followers = $followRepository->findBy(['userTarget' => $user->getId()]);
        foreach($followers as $follower){
            $entityManager->remove( $follower);
        }

        // Annuler les courses futurs
        $events = $eventRepository->findUpcomingEventsByUser( $user);
        foreach($events as $event){
            $event->setCancelled(true);
        }

        $entityManager->persist( $user );
        $entityManager->flush();
        

        $request->getSession()->invalidate();
        $tokenStorage->setToken(null);
        $this->addFlash('success','Votre compte a été supprimé avec succès');
        return $this->redirectToRoute('app_logout');
    }
}
