<?php

namespace SourceBans\CoreBundle\Util\Admin;

use SourceBans\CoreBundle\Entity\Admin;
use SourceBans\CoreBundle\Util\KeyValues;

/**
 * DetailedImport
 */
class DetailedImport extends AbstractImport
{
    /**
     * @inheritdoc
     */
    public function import($file)
    {
        $serverGroups = $this->serverGroupAdapter->all();

        $kv = new KeyValues('Admins');
        $kv->load($file);

        foreach ($kv as $name => $data) {
            $admin = new Admin;
            $admin->setName($name);
            $admin->setAuth($data['auth']);
            $admin->setIdentity($data['identity']);

            if (isset($data['password'])) {
                $admin->setPlainPassword($data['password']);
                $admin->setServerPassword($data['password']);
            }
            if (isset($data['group'])) {
                foreach ((array)$data['group'] as $group) {
                    $admin->addServerGroup($serverGroups[$group]);
                }
            }

            $this->adminAdapter->persist($admin);
        }
    }
}
