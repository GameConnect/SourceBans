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

            try {
                $steam = new \SteamID($identity);
            } catch (\InvalidArgumentException $exception) {
                continue;
            }

            $ban = new Ban;
            $ban->setType(Ban::TYPE_STEAM);
            $ban->setSteam($steam->RenderSteam3());
            $ban->setReason('Imported from banned_user.cfg');
            $ban->setLength(Ban::LENGTH_PERMANENT);

            $this->banAdapter->persist($ban);
        }
    }
}
