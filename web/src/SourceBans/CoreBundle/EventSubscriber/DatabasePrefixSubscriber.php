<?php

namespace SourceBans\CoreBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * DatabasePrefixSubscriber
 */
class DatabasePrefixSubscriber implements EventSubscriber
{
    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @param string $prefix
     */
    public function __construct($prefix)
    {
        $this->prefix = (string)$prefix;
    }

    /**
     * @inheritdoc
     */
    public function getSubscribedEvents()
    {
        return ['loadClassMetadata'];
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadataInfo $classMetadata */
        $classMetadata = $eventArgs->getClassMetadata();

        if (!$classMetadata->isInheritanceTypeSingleTable()
            || $classMetadata->getName() === $classMetadata->rootEntityName) {
            $classMetadata->setTableName($this->prefix . $classMetadata->getTableName());
        }

        foreach ($classMetadata->getAssociationMappings() as &$mapping) {
            if ($mapping['type'] == ClassMetadataInfo::MANY_TO_MANY && $mapping['isOwningSide']) {
                $mapping['joinTable']['name'] = $this->prefix . $mapping['joinTable']['name'];
            }
        }
    }
}
