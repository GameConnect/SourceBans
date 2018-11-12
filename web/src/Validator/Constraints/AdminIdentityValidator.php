<?php

namespace SourceBans\Validator\Constraints;

use SourceBans\Entity\Admin;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AdminIdentityValidator extends ConstraintValidator
{
    public function validate($admin, Constraint $constraint)
    {
        /** @var Admin $admin */
        if ($admin->getAuth() == Admin::AUTH_STEAM) {
            try {
                new \SteamID($admin->getIdentity());
            } catch (\InvalidArgumentException $exception) {
                $this->addViolation('Invalid Steam ID');
            }
        } elseif ($admin->getAuth() == Admin::AUTH_IP && !filter_var($admin->getIdentity(), FILTER_VALIDATE_IP)) {
            $this->addViolation('Invalid IP address');
        }
    }

    private function addViolation(string $message)
    {
        $this->context->buildViolation($message)
            ->atPath('identity')
            ->addViolation();
    }
}
