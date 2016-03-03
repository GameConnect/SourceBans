<?php

namespace SourceBans\CoreBundle\Util\Admin;

use SourceBans\CoreBundle\Adapter\AdminAdapter;
use SourceBans\CoreBundle\Adapter\ServerGroupAdapter;

/**
 * AbstractImport
 */
abstract class AbstractImport
{
    /**
     * @var AdminAdapter
     */
    protected $adminAdapter;

    /**
     * @var ServerGroupAdapter
     */
    protected $serverGroupAdapter;

    /**
     * @param AdminAdapter $adminAdapter
     */
    public function setAdminAdapter(AdminAdapter $adminAdapter)
    {
        $this->adminAdapter = $adminAdapter;
    }

    /**
     * @param ServerGroupAdapter $serverGroupAdapter
     */
    public function setServerGroupAdapter(ServerGroupAdapter $serverGroupAdapter)
    {
        $this->serverGroupAdapter = $serverGroupAdapter;
    }

    /**
     * @param string $file
     */
    abstract public function import($file);
}
