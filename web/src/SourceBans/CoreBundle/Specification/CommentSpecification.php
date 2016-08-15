<?php

namespace SourceBans\CoreBundle\Specification;

use Rb\Specification\Doctrine\Query;
use Rb\Specification\Doctrine\Specification;
use SourceBans\CoreBundle\Entity\Comment;

/**
 * CommentSpecification
 */
class CommentSpecification extends Specification
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct([
            new Query\Select(['admin', 'updateAdmin']),
            new Query\Join('admin', 'admin'),
            new Query\LeftJoin('updateAdmin', 'updateAdmin'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($className)
    {
        return $className == Comment::class;
    }
}
