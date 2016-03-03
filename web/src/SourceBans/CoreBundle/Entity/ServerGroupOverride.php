<?php

namespace SourceBans\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ServerGroupOverride
 *
 * @ORM\Entity
 * @ORM\Table(name="server_group_overrides", indexes={@ORM\Index(name="group_id", columns={"group_id"})})
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class ServerGroupOverride implements EntityInterface
{
    const ACCESS_ALLOW = 'allow';
    const ACCESS_DENY  = 'deny';
    const TYPE_COMMAND = 'command';
    const TYPE_GROUP   = 'group';

    /**
     * @var string
     *
     * @Assert\Choice({ServerGroupOverride::TYPE_COMMAND, ServerGroupOverride::TYPE_GROUP})
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
     * @Assert\Choice({ServerGroupOverride::ACCESS_ALLOW, ServerGroupOverride::ACCESS_DENY})
     * @ORM\Column(name="access", type="string", nullable=false)
     */
    private $access;

    /**
     * @var ServerGroup
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\ManyToOne(targetEntity="ServerGroup", inversedBy="overrides")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     * })
     */
    private $group;

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
     * @return ServerGroupOverride
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
     * @return ServerGroupOverride
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * @param string $access
     * @return ServerGroupOverride
     */
    public function setAccess($access)
    {
        $this->access = $access;

        return $this;
    }

    /**
     * @return ServerGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param ServerGroup $group
     * @return ServerGroupOverride
     */
    public function setGroup(ServerGroup $group)
    {
        $this->group = $group;

        return $this;
    }
}
