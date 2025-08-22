<?php

// src/Security/Voter/EmailVerifiedVoter.php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter personnalisé pour vérifier si l'utilisateur a confirmé son adresse email.
 *
 * Il permet d'utiliser #[IsGranted('EMAIL_VERIFIED')] dans les contrôleurs.
 */
class EmailVerifiedVoter extends Voter
{
    /**
     * Cette méthode détermine si ce Voter est responsable du droit demandé.
     * Elle est appelée avant la vérification réelle.
     *
     * @param string $attribute Le droit demandé (ex: 'EMAIL_VERIFIED')
     * @param mixed $subject L'objet lié à l'autorisation (souvent null ici)
     * @return bool true si ce voter gère cet attribut
     */
    protected function supports(string $attribute, $subject): bool
    {
        // On ne traite que l'attribut 'EMAIL_VERIFIED'
        return $attribute === 'EMAIL_VERIFIED';
    }

    /**
     * Cette méthode contient la logique de décision du voter.
     * Elle est appelée uniquement si supports() retourne true.
     *
     * @param string $attribute Le droit demandé (toujours 'EMAIL_VERIFIED' ici)
     * @param mixed $subject L'objet lié à l'autorisation (pas utilisé ici)
     * @param TokenInterface $token Le token de sécurité contenant l'utilisateur
     * @return bool true si l'utilisateur est autorisé
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        // Récupération de l'utilisateur connecté à partir du token
        $user = $token->getUser();
        dd([
            'user_class' => get_class($user),
            'is_verified' => $user instanceof User ? $user->isVerified() : null,
        ]);

        // Si l'utilisateur n'est pas connecté ou n'est pas du bon type, on refuse l'accès
        if (!$user instanceof User) {
            return false;
        }

        // Vérifie si l'utilisateur a confirmé son adresse e-mail
        return $user->isVerified();
        
    }
}
