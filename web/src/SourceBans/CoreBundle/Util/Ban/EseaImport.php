<?php

namespace SourceBans\CoreBundle\Util\Ban;

use SourceBans\CoreBundle\Entity\Ban;

/**
 * EseaImport
 */
class EseaImport extends AbstractImport
{
    /**
     * @inheritdoc
     */
    public function import($file)
    {
        $handle = fopen($file, 'r');

        while (list($steam, $name) = fgetcsv($handle, 4096)) {
            $steam = 'STEAM_' . trim($steam);
            // If this is not a valid Steam ID, ignore
            if (!preg_match(SourceBans::PATTERN_STEAM, $steam)) {
                continue;
            }

            $ban = new Ban;
            $ban->setType(Ban::TYPE_STEAM);
            $ban->setSteam($steam);
            $ban->setName($name);
            $ban->setReason('Imported from esea_ban_list.csv');
            $ban->setLength(0);

            $this->banAdapter->persist($ban);
        }

        fclose($handle);
    }
}
