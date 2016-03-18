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

        while (list($id, $name) = fgetcsv($handle, 4096)) {
            try {
                $steam = new \SteamID('STEAM_' . trim($id));
            } catch (\InvalidArgumentException $exception) {
                continue;
            }

            $ban = new Ban;
            $ban->setType(Ban::TYPE_STEAM);
            $ban->setSteam($steam->RenderSteam3());
            $ban->setName($name);
            $ban->setReason('Imported from esea_ban_list.csv');
            $ban->setLength(Ban::LENGTH_PERMANENT);

            $this->banAdapter->persist($ban);
        }

        fclose($handle);
    }
}
