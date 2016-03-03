<?php

namespace SourceBans\CoreBundle\Util\Ban;

use SourceBans\CoreBundle\Entity\Ban;

/**
 * SteamImport
 */
class SteamImport extends AbstractImport
{
    /**
     * @inheritdoc
     */
    public function import($file)
    {
        foreach (file($file) as $line) {
            list(, $length, $identity) = explode(' ', rtrim($line));
            // If this is not a permanent ban, ignore
            if ($length > 0) {
                continue;
            }

            $ban = new Ban;
            $ban->setType(Ban::TYPE_STEAM);
            $ban->setSteam($identity);
            $ban->setReason('Imported from banned_user.cfg');
            $ban->setLength(0);

            $this->banAdapter->persist($ban);
        }
    }
}
