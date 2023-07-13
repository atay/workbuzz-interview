<?php

namespace App\Infrastructure\Framework\Symfony\Security\Voter;

use App\Infrastructure\Persistence\Doctrine\Entity\Survey;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SurveyVoter extends Voter
{
    public const DELETE = 'DELETE';
    public const EDIT = 'EDIT';
    public const ANSWER = 'ANSWER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::DELETE, self::EDIT, self::ANSWER]) && $subject instanceof Survey;
    }

    /**
     * @param Survey $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return match ($attribute) {
            self::DELETE => $subject->getStatus() !== Survey::STATUS_LIVE,
            self::EDIT => $subject->getStatus() === Survey::STATUS_NEW,
            self::ANSWER => $subject->getStatus() === Survey::STATUS_LIVE,
            default => false,
        };
    }
}