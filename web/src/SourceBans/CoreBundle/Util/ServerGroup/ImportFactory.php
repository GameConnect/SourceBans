<?php

namespace SourceBans\CoreBundle\Util\ServerGroup;

use SourceBans\CoreBundle\Adapter\ServerGroupAdapter;

/**
 * ImportFactory
 */
class ImportFactory
{
    /**
     * @var ServerGroupAdapter
     */
    private $serverGroupAdapter;

    /**
     * @param ServerGroupAdapter $serverGroupAdapter
     */
    public function __construct(ServerGroupAdapter $serverGroupAdapter)
    {
        $this->serverGroupAdapter = $serverGroupAdapter;
    }

    /**
     * @param string $file
     * @param string $name
     */
    public function import($file, $name = null)
    {
        $name = $name ?: basename($file);

        switch ($name) {
            case 'admin_groups.cfg':
                $importer = new DetailedImport;
                break;
            default:
                throw new \InvalidArgumentException('Unsupported file format.');
        }

        $importer->setServerGroupAdapter($this->serverGroupAdapter);
        $importer->import($file);
    }
}
