<?php

namespace SourceBans\CoreBundle\Event;

use SourceBans\CoreBundle\Entity\Comment;
use Symfony\Component\EventDispatcher\Event;

/**
 * CommentAdapterEvent
 */
class CommentAdapterEvent extends Event
{
    /**
     * @var Comment
     */
    protected $comment;

    /**
     * @param Comment $comment
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return Comment
     */
    public function getComment()
    {
        return $this->comment;
    }
}
