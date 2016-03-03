<?php

namespace SourceBans\CoreBundle\Util\Admin;

use SourceBans\CoreBundle\Entity\Admin;

/**
 * ManiImport
 */
class ManiImport extends AbstractImport
{
    /**
     * @inheritdoc
     */
    public function import($file)
    {
        $kv = new KeyValues;
        $kv->load($file);

        foreach ($kv['players'] as $name => $player) {
            $admin = new Admin;
            $admin->setAuth(Admin::AUTH_STEAM);
            $admin->setName($name);
            $admin->setIdentity($player['steam']);

            $this->adminAdapter->persist($admin);
        }
    }
}
