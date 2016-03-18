<?php

namespace SourceBans\CoreBundle\Validator\Constraints\Ban;

use Symfony\Component\Validator\Constraint;

/**
 * Type constraint
 *
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class Type extends Constraint
{
    /**
     * @inheritdoc
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
