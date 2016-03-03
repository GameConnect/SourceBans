<?php

namespace SourceBans\CoreBundle\Util\ServerGroup;

use SourceBans\CoreBundle\Adapter\ServerGroupAdapter;

/**
 * AbstractImport
 */
abstract class AbstractImport
{
    /**
     * @var ServerGroupAdapter
     */
    protected $serverGroupAdapter;

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
