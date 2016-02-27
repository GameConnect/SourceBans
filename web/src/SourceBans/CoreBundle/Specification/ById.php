<?php

namespace SourceBans\CoreBundle\Specification;

use Rb\Specification\Doctrine\Condition\Equals;

/**
 * ById
 */
class ById extends Equals
{
    /**
     * Constructor
     * @param integer $id
     * @param string|null $dqlAlias
     */
    public function __construct($id, $dqlAlias = null)
    {
        parent::__construct('id', (int)$id, $dqlAlias);
    }
}
