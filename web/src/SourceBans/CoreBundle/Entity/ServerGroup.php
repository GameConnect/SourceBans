<?php

namespace SourceBans\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ServerGroup
 *
 * @UniqueEntity("name")
 * @ORM\Entity
 * @ORM\Table(name="server_groups", uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"})})
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class ServerGroup implements EntityInterface
{
    /**
     * @var integer
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
    private $flags;

    /**
     * @var integer
     *
     * @Assert\Type("integer")
     * @Assert\GreaterThanOrEqual(0)
     * @ORM\Column(name="immunity", type="smallint", nullable=false)
     */
    private $immunity = 0;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Admin", mappedBy="serverGroups")
     */
    private $admins;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="ServerGroup")
     * @ORM\JoinTable(name="server_groups_immunity",
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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ServerGroupOverride", mappedBy="group")
     */
    private $overrides;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Server", mappedBy="groups")
     */
    private $servers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->admins = new ArrayCollection;
        $this->immuneGroups = new ArrayCollection;
        $this->overrides = new ArrayCollection;
        $this->servers = new ArrayCollection;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ServerGroup
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
     * @return ServerGroup
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;

        return $this;
    }

    /**
     * @return integer
     */
    public function getImmunity()
    {
        return $this->immunity;
    }

    /**
     * @param integer $immunity
     * @return ServerGroup
     */
    public function setImmunity($immunity)
    {
        $this->immunity = $immunity;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getAdmins()
    {
        return $this->admins;
    }

    /**
     * @param Admin $admin
     * @return ServerGroup
     */
    public function addAdmin(Admin $admin)
    {
        $this->admins[] = $admin;

        return $this;
    }

    /**
     * @param Admin $admin
     * @return ServerGroup
     */
    public function removeAdmin(Admin $admin)
    {
        $this->admins->removeElement($admin);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getImmuneGroups()
    {
        return $this->immuneGroups;
    }

    /**
     * @param ServerGroup $serverGroup
     * @return ServerGroup
     */
    public function addImmuneGroup(ServerGroup $serverGroup)
    {
        $this->immuneGroups[] = $serverGroup;

        return $this;
    }

    /**
     * @param ServerGroup $serverGroup
     * @return ServerGroup
     */
    public function removeImmuneGroup(ServerGroup $serverGroup)
    {
        $this->immuneGroups->removeElement($serverGroup);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getServers()
    {
        return $this->servers;
    }

    /**
     * @param Server $server
     * @return ServerGroup
     */
    public function addServer(Server $server)
    {
        $this->servers[] = $server;

        return $this;
    }

    /**
     * @param Server $server
     * @return ServerGroup
     */
    public function removeServer(Server $server)
    {
        $this->servers->removeElement($server);

        return $this;
    }
}
