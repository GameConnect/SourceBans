<?php

namespace SourceBans\CoreBundle\Util\Admin;

use SourceBans\CoreBundle\Entity\Admin;
use SourceBans\CoreBundle\Util\KeyValues;

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
            $admin->setName($name);
            $admin->setAuth(Admin::AUTH_STEAM);
            $admin->setIdentity($player['steam']);

            $this->adminAdapter->persist($admin);
        }
    }
}
