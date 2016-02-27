<?php

namespace SourceBans\CoreBundle\Security\Authorization\Voter;

use SourceBans\CoreBundle\Entity\Admin;
use SourceBans\CoreBundle\Entity\Ban;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * BanVoter
 */
class BanVoter implements VoterInterface
{
    /**
     * @inheritdoc
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, [
            'edit',
            'unban',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsClass($class)
    {
        return $class == Ban::class;
    }

    /**
     * @inheritdoc
     * @param Ban $ban
     */
    public function vote(TokenInterface $token, $ban, array $attributes)
    {
        if (!$this->supportsClass(get_class($ban))) {
            return self::ACCESS_ABSTAIN;
        }

        $attribute = $attributes[0];

        if (!$this->supportsAttribute($attribute)) {
            return self::ACCESS_ABSTAIN;
        }

        /** @var Admin $admin */
        $admin = $token->getUser();

        if (!($admin instanceof UserInterface)) {
            return self::ACCESS_DENIED;
        }

        $prefix = 'ROLE_' . strtoupper($attribute) . '_';

        foreach ($token->getRoles() as $role) {
            switch ($role->getRole()) {
                case $prefix . 'OWN_BANS':
                    if ($ban->getAdmin() instanceof Admin && $ban->getAdmin() == $admin) {
                        return self::ACCESS_GRANTED;
                    }
                    break;
                case $prefix . 'GROUP_BANS':
                    if (!($ban->getAdmin() instanceof Admin)) {
                        break;
                    }

                    $serverGroups = array_intersect(
                        $ban->getAdmin()->getServerGroups()->toArray(),
                        $admin->getServerGroups()->toArray()
                    );

                    if (count($serverGroups) > 0) {
                        return self::ACCESS_GRANTED;
                    }
                    break;
                case $prefix . 'ALL_BANS':
                case 'ROLE_ROOT':
                    return self::ACCESS_GRANTED;
            }
        }

        return self::ACCESS_DENIED;
    }
}
