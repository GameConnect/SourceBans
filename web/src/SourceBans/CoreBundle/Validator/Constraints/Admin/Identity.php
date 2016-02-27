<?php

namespace SourceBans\CoreBundle\Validator\Constraints\Admin;

use Symfony\Component\Validator\Constraint;

/**
 * Identity constraint
 *
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class Identity extends Constraint
{
    /**
     * @inheritdoc
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
