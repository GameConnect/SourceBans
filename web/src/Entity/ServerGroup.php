<?php

namespace SourceBans\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity("name")
 * @ORM\Entity
 * @ORM\Table(name="sb_server_groups", uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"})})
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class ServerGroup
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="smallint", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Length(max=32)
     * @ORM\Column(name="name", type="string", length=32, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="flags", type="string", length=32, nullable=false)
     */
    private $flags = '';

    /**
     * @var int
     *
     * @Assert\Type("integer")
     * @Assert\GreaterThanOrEqual(0)
     * @ORM\Column(name="immunity", type="smallint", nullable=false)
     */
    private $immunity = 0;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Admin", mappedBy="serverGroups")
     */
    private $admins;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="ServerGroup")
     * @ORM\JoinTable(name="sb_server_group_immunity",
     *   joinColumns={
     *     @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="other_id", referencedColumnName="id")
     *   }
     * )
     */
    private $immuneGroups;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="ServerGroupOverride", mappedBy="group")
     */
    private $overrides;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Server", mappedBy="groups")
     */
    private $servers;

    public function __construct()
    {
        $this->admins = new ArrayCollection();
        $this->immuneGroups = new ArrayCollection();
        $this->overrides = new ArrayCollection();
        $this->servers = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName() ?: '';
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFlags(): string
    {
        return $this->flags;
    }

    public function setFlags(string $flags): self
    {
        $this->flags = $flags;

        return $this;
    }

    public function getImmunity(): int
    {
        return $this->immunity;
    }

    public function setImmunity(int $immunity): self
    {
        $this->immunity = $immunity;

        return $this;
    }

    public function getAdmins(): Collection
    {
        return $this->admins;
    }

    public function addAdmin(Admin $admin): self
    {
        $this->admins[] = $admin;

        return $this;
    }

    public function removeAdmin(Admin $admin): self
    {
        $this->admins->removeElement($admin);

        return $this;
    }

    public function getImmuneGroups(): Collection
    {
        return $this->immuneGroups;
    }

    public function addImmuneGroup(self $serverGroup): self
    {
        $this->immuneGroups[] = $serverGroup;

        return $this;
    }

    public function removeImmuneGroup(self $serverGroup): self
    {
        $this->immuneGroups->removeElement($serverGroup);

        return $this;
    }

    public function getServers(): Collection
    {
        return $this->servers;
    }

    public function addServer(Server $server): self
    {
        $this->servers[] = $server;

        return $this;
    }

    public function removeServer(Server $server): self
    {
        $this->servers->removeElement($server);

        return $this;
    }

    public function getOverrides(): Collection
    {
        return $this->overrides;
    }

    public function addOverride(ServerGroupOverride $override): self
    {
        $this->overrides[] = $override;

        return $this;
    }

    public function removeOverride(ServerGroupOverride $override): self
    {
        $this->overrides->removeElement($override);

        return $this;
    }
}
