<?php

namespace SourceBans\CoreBundle\Util\Ban;

use SourceBans\CoreBundle\Adapter\BanAdapter;

/**
 * ImportFactory
 */
class ImportFactory
{
    /**
     * @var BanAdapter
     */
    private $banAdapter;

    /**
     * @param BanAdapter $banAdapter
     */
    public function __construct(BanAdapter $banAdapter)
    {
        $this->banAdapter = $banAdapter;
    }

    /**
     * @param string $file
     * @param string $name
     */
    public function import($file, $name = null)
    {
        $name = $name ?: basename($file);

        switch ($name) {
            case 'banned_ip.cfg':
                $importer = new IpImport;
                break;
            case 'banned_user.cfg':
                $importer = new SteamImport;
                break;
            case 'esea_ban_list.csv':
                $importer = new EseaImport;
                break;
            default:
                throw new \InvalidArgumentException('Unsupported file format.');
        }

        $importer->setBanAdapter($this->banAdapter);
        $importer->import($file);
    }
}
