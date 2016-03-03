<?php

namespace SourceBans\CoreBundle\Util\Admin;

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
        $serverGroups = $this->serverGroupAdapter->all();

        preg_match_all('/"(.+?)"[ \t]*"(.+?)"([ \t]*"(.+?)")?/', file_get_contents($file), $admins);

        for ($i = 0; $i < count($admins[0]); $i++) {
            list($identity, $flags, $password) = [$admins[1][$i], $admins[2][$i], $admins[4][$i]];

            // Parse authentication type depending on identity
            if (preg_match(SourceBans::PATTERN_STEAM, $identity)) {
                $auth = Admin::AUTH_STEAM;
            } elseif ($identity{0} == '!' && filter_var($identity, FILTER_VALIDATE_IP)) {
                $auth = Admin::AUTH_IP;
            } else {
                $auth = Admin::AUTH_NAME;
            }

            // Parse flags
            if ($flags{0} == '@') {
                $group = substr($flags, 1);
            //} elseif (strpos($flags, ':') !== false) {
            //    list($immunity, $flags) = explode(':', $flags);
            }

            $admin = new Admin;
            $admin->setName($identity);
            $admin->setAuth($auth);
            $admin->setIdentity($identity);

            if (!empty($password)) {
                $admin->setPassword($password);
                $admin->setServerPassword($password);
            }
            if (isset($group)) {
                $admin->addServerGroup($serverGroups[$group]);
            }

            $this->adminAdapter->persist($admin);
        }
    }
}
