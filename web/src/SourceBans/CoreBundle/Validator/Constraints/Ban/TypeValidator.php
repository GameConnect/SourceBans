<?php

namespace SourceBans\CoreBundle\Validator\Constraints\Ban;

use SourceBans\CoreBundle\Entity\Ban;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Type validator
 */
class TypeValidator extends ConstraintValidator
{
    /**
     * @inheritdoc
     * @param Ban $ban
     */
    public function validate($ban, Constraint $constraint)
    {
        if ($ban->getType() == Ban::TYPE_STEAM) {
            try {
                new \SteamID((string)$ban->getSteam());
            } catch (\InvalidArgumentException $exception) {
                $this->addViolation('steam', 'Invalid Steam ID');
            }
        } elseif ($ban->getType() == Ban::TYPE_IP && !filter_var($ban->getIp(), FILTER_VALIDATE_IP)) {
            $this->addViolation('ip', 'Invalid IP address');
        }
    }

    /**
     * @param string $path
     * @param string $message
     */
    private function addViolation($path, $message)
    {
        $this->context->buildViolation($message)
            ->atPath($path)
            ->addViolation();
    }
}
