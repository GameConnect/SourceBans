<?php

namespace SourceBans\CoreBundle\Util\Admin;

use Rb\Specification\Doctrine\Condition;
use SourceBans\CoreBundle\Entity\Admin;

/**
 * SimpleImport
 */
class SimpleImport extends AbstractImport
{
    /**
     * @inheritdoc
     */
    public function import($file)
    {
        preg_match_all('/"(.+?)"[ \t]*"(.+?)"([ \t]*"(.+?)")?/', file_get_contents($file), $admins);

        for ($i = 0; $i < count($admins[0]); $i++) {
            $identity = $admins[1][$i];
            $flags    = $admins[2][$i];
            $password = $admins[4][$i];

            $admin = new Admin;
            $admin->setName($identity);
            $this->parseIdentity($admin, $identity);

            if ($flags{0} == '@') {
                $serverGroup = $this->serverGroupAdapter->getBy([
                    new Condition\Equals('name', substr($flags, 1)),
                ]);

                if ($serverGroup !== null) {
                    $admin->addServerGroup($serverGroup);
                }
            }
            if (!empty($password)) {
                $admin->setPlainPassword($password);
                $admin->setServerPassword($password);
            }

            $this->adminAdapter->persist($admin);
        }
    }

    /**
     * Parse authentication type from identity
     * @param Admin $admin
     * @param string $identity
     * @return Admin
     */
    private function parseIdentity(Admin $admin, $identity)
    {
        if ($identity{0} == '!' && filter_var(substr($identity, 1), FILTER_VALIDATE_IP)) {
            return $admin
                ->setAuth(Admin::AUTH_IP)
                ->setIdentity(substr($identity, 1));
        }

        try {
            $steam = new \SteamID($identity);
            return $admin
                ->setAuth(Admin::AUTH_STEAM)
                ->setIdentity($steam->RenderSteam3());
        } catch (\InvalidArgumentException $exception) {
            return $admin
                ->setAuth(Admin::AUTH_NAME)
                ->setIdentity($identity);
        }
    }
}
