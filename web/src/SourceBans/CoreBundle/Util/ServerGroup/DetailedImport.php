<?php

namespace SourceBans\CoreBundle\Util\ServerGroup;

use SourceBans\CoreBundle\Entity\ServerGroup;
use SourceBans\CoreBundle\Entity\ServerGroupOverride;

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
        $kv = new KeyValues('Groups');
        $kv->load($file);

        foreach ($kv as $name => $data) {
            $serverGroup = new ServerGroup;
            $serverGroup->setName($name);

            if (isset($data['flags'])) {
                $serverGroup->setFlags($data['flags']);
            }
            if (isset($data['identity'])) {
                $serverGroup->setImmunity($data['immunity']);
            }

            $this->serverGroupAdapter->persist($serverGroup);

            if (isset($data['Overrides'])) {
                foreach ($data['Overrides'] as $name => $access) {
                    $type = ServerGroupOverride::TYPE_COMMAND;
                    // Parse name
                    if ($name{0} == ':') {
                        $type = ServerGroupOverride::TYPE_GROUP;
                        $name = substr($name, 1);
                    }

                    $override = new ServerGroupOverride;
                    $override->setGroup($serverGroup);
                    $override->setType($type);
                    $override->setName($name);
                    $override->setAccess($access);

                    $this->serverGroupAdapter->persist($override);
                }
            }
        }
    }
}
