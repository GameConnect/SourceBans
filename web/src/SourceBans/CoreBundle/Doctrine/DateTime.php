<?php

namespace SourceBans\CoreBundle\Doctrine;

/**
 * DateTime object that implements __toString() for use as primary key in Doctrine
 */
class DateTime extends \DateTime
{
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->format(\DateTime::ISO8601);
    }
}
