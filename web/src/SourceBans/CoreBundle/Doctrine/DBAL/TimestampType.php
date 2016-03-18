<?php

namespace SourceBans\CoreBundle\Doctrine\DBAL;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Type that maps a UNIX timestamp to a PHP DateTime object.
 */
class TimestampType extends Type
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'timestamp';
    }

    /**
     * @inheritdoc
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'INT(10) UNSIGNED';
    }

    /**
     * @inheritdoc
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        /* @var \DateTime $value */
        return null === $value ? null : $value->getTimestamp();
    }

    /**
     * @inheritdoc
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return null === $value ? null : \DateTime::createFromFormat('U', $value);
    }
}
