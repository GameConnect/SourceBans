<?php

namespace SourceBans\CoreBundle\Util\Admin;

use SourceBans\CoreBundle\Adapter\AdminAdapter;
use SourceBans\CoreBundle\Adapter\ServerGroupAdapter;

/**
 * ImportFactory
 */
class ImportFactory
{
    /**
     * @var AdminAdapter
     */
    private $adminAdapter;

    /**
     * @var ServerGroupAdapter
     */
    private $serverGroupAdapter;

    /**
     * @param AdminAdapter $adminAdapter
     * @param ServerGroupAdapter $serverGroupAdapter
     */
    public function __construct(AdminAdapter $adminAdapter, ServerGroupAdapter $serverGroupAdapter)
    {
        $this->adminAdapter = $adminAdapter;
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
            case 'admins.cfg':
                $importer = new DetailedImport;
                break;
            case 'admins_simple.ini':
                $importer = new SimpleImport;
                break;
            case 'clients.txt':
                $importer = new ManiImport;
                break;
            default:
                throw new \InvalidArgumentException('Unsupported file format.');
        }

        $importer->setAdminAdapter($this->adminAdapter);
        $importer->setServerGroupAdapter($this->serverGroupAdapter);
        $importer->import($file);
    }
}
