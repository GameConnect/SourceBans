<?php

namespace SourceBans\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Override
 *
 * @ORM\Entity
 * @ORM\Table(name="overrides")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Override
{
    const TYPE_COMMAND = 'command';
    const TYPE_GROUP   = 'group';

    /**
     * @var string
     *
     * @Assert\Choice({Override::TYPE_COMMAND, Override::TYPE_GROUP})
     * @ORM\Id
     * @ORM\Column(name="type", type="string", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $type;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Length(max=32)
     * @ORM\Id
     * @ORM\Column(name="name", type="string", length=32, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $name;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @ORM\Column(name="flags", type="string", length=30, nullable=false)
     */
    private $flags;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Override
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Override
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * @param string $flags
     * @return Override
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;

        return $this;
    }
}
