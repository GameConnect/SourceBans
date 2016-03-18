<?php

namespace SourceBans\CoreBundle\Validator\Constraints\Admin;

use SourceBans\CoreBundle\Entity\Admin;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Identity validator
 */
class IdentityValidator extends ConstraintValidator
{
    /**
     * @inheritdoc
     * @param Admin $admin
     */
    public function validate($admin, Constraint $constraint)
    {
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

    /**
     * @param string $message
     */
    private function addViolation($message)
    {
        $this->context->buildViolation($message)
            ->atPath('identity')
            ->addViolation();
    }
}
