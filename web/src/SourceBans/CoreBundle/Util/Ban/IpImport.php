<?php

namespace SourceBans\CoreBundle\Util\Ban;

use SourceBans\CoreBundle\Entity\Ban;

/**
 * IpImport
 */
class IpImport extends AbstractImport
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
            $ban->setType(Ban::TYPE_IP);
            $ban->setIp($identity);
            $ban->setReason('Imported from banned_ip.cfg');
            $ban->setLength(Ban::LENGTH_PERMANENT);

            $this->banAdapter->persist($ban);
        }
    }
}
