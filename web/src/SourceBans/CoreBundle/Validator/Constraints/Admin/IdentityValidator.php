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
    const PATTERN_STEAM = '/^(STEAM_[0-9]:[0-9]:[0-9]+|\[U:[0-9]:[0-9]+\])$/i';

    /**
     * @inheritdoc
     */
    public function validate($admin, Constraint $constraint)
    {
        switch ($admin->getAuth()) {
            case Admin::AUTH_STEAM:
                $isValid = preg_match(self::PATTERN_STEAM, $admin->getIdentity());
                $message = 'This value is not a valid Steam ID.';
                break;
            case Admin::AUTH_IP:
                $isValid = filter_var($admin->getIdentity(), FILTER_VALIDATE_IP);
                $message = 'This value is not a valid IP address.';
                break;
            default:
                return;
        }

        if (!$isValid) {
            $this->context->buildViolation($message)
                ->atPath('identity')
                ->addViolation();
        }
    }
}
