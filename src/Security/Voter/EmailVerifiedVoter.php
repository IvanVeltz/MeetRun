<?php



namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;


class EmailVerifiedVoter extends Voter
{
    
    protected function supports(string $attribute, $subject): bool
    {
        // On ne traite que l'attribut 'EMAIL_VERIFIED'
        return $attribute === 'EMAIL_VERIFIED';
    }

    
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        // Récupération de l'utilisateur connecté à partir du token
        $user = $token->getUser();
        

        // Si l'utilisateur n'est pas connecté ou n'est pas du bon type, on refuse l'accès
        if (!$user instanceof User) {
            return false;
        }

        // Vérifie si l'utilisateur a confirmé son adresse e-mail
        return $user->isVerified();
        
    }
}
