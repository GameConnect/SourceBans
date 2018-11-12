<?php

namespace SourceBans\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="sb_server_group_overrides", indexes={@ORM\Index(name="group_id", columns={"group_id"})})
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class ServerGroupOverride
{
    const ACCESS_ALLOW = 'allow';
    const ACCESS_DENY = 'deny';
    const TYPE_COMMAND = 'command';
    const TYPE_GROUP = 'group';

    /**
     * @var string
     *
     * @Assert\Choice({ServerGroupOverride::TYPE_COMMAND, ServerGroupOverride::TYPE_GROUP})
     * @ORM\Id
     * @ORM\Column(name="type", type="string", length=8, nullable=false)
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
     * @ORM\Column(name="access", type="string", length=8, nullable=false)
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAccess(): ?string
    {
        return $this->access;
    }

    public function setAccess(string $access): self
    {
        $this->access = $access;

        return $this;
    }

    public function getGroup(): ?ServerGroup
    {
        return $this->group;
    }

    public function setGroup(ServerGroup $group): self
    {
        $this->group = $group;

        return $this;
    }
}
