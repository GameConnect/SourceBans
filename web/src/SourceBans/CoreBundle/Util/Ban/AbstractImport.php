<?php

namespace SourceBans\CoreBundle\Util\Ban;

use SourceBans\CoreBundle\Adapter\BanAdapter;

/**
 * AbstractImport
 */
abstract class AbstractImport
{
    /**
     * @var BanAdapter
     */
    protected $banAdapter;

    /**
     * @param BanAdapter $banAdapter
     */
    public function setBanAdapter(BanAdapter $banAdapter)
    {
        $this->banAdapter = $banAdapter;
    }

    /**
     * @param string $file
     */
    abstract public function import($file);
}
