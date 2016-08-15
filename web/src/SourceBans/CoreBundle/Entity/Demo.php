<?php

namespace SourceBans\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Demo
 *
 * @ORM\Entity
 * @ORM\Table(name="demos", indexes={@ORM\Index(name="object", columns={"object_type", "object_id"})})
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Demo
{
    const TYPE_BAN    = 'B';
    const TYPE_REPORT = 'S';

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="object_type", type="string", length=1, nullable=false)
     */
    private $objectType;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     */
    private $objectId;

    /**
     * @var string|File
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     * @Assert\File
     * @ORM\Column(name="file", type="string", length=255, nullable=false)
     */
    private $file;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @return integer
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @return string|File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string|File $file
     * @return Demo
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @param Ban|Report $object
     * @return Demo
     */
    public function setObject($object)
    {
        if ($object instanceof Ban) {
            $this->objectType = self::TYPE_BAN;
        }
        if ($object instanceof Report) {
            $this->objectType = self::TYPE_REPORT;
        }

        $this->objectId = $object->getId();

        return $this;
    }
}
