<?php

namespace SourceBans\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Server
 *
 * @ORM\Entity
 * @ORM\Table(name="servers", uniqueConstraints={@ORM\UniqueConstraint(name="host", columns={"host", "port"})}, indexes={@ORM\Index(name="game_id", columns={"game_id"})})
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Server implements EntityInterface
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
     * @Assert\Length(max=255)
     * @ORM\Column(name="host", type="string", length=255, nullable=false)
     */
    private $host;

    /**
     * @var integer
     *
     * @Assert\Type("integer")
     * @ORM\Column(name="port", type="smallint", nullable=false)
     */
    private $port = 27015;

    /**
     * @var string
     *
     * @Assert\Length(max=32)
     * @ORM\Column(name="rcon", type="string", length=32, nullable=true)
     */
    private $rcon;

    /**
     * @var boolean
     *
     * @Assert\Type("boolean")
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled = true;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Action", mappedBy="server")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="server_id", referencedColumnName="id")
     * })
     */
    private $actions;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Ban", mappedBy="server")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="server_id", referencedColumnName="id")
     * })
     */
    private $bans;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Block", mappedBy="server")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="server_id", referencedColumnName="id")
     * })
     */
    private $blocks;

    /**
     * @var Game
     *
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="servers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="game_id", referencedColumnName="id")
     * })
     */
    private $game;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="ServerGroup", inversedBy="servers", cascade={"persist"})
     * @ORM\JoinTable(name="servers_server_groups",
     *   joinColumns={
     *     @ORM\JoinColumn(name="server_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     *   }
     * )
     */
    private $groups;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Report", mappedBy="server")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="server_id", referencedColumnName="id")
     * })
     */
    private $reports;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->actions = new ArrayCollection;
        $this->bans = new ArrayCollection;
        $this->blocks = new ArrayCollection;
        $this->groups = new ArrayCollection;
        $this->reports = new ArrayCollection;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getHost() . ':' . $this->getPort();
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
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return Server
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return integer
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param integer $port
     * @return Server
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @return string
     */
    public function getRcon()
    {
        return $this->rcon;
    }

    /**
     * @param string $rcon
     * @return Server
     */
    public function setRcon($rcon)
    {
        $this->rcon = $rcon;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     * @return Server
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @return ArrayCollection
     */
    public function getBans()
    {
        return $this->bans;
    }

    /**
     * @return ArrayCollection
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * @return Game
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * @param Game $game
     * @return Server
     */
    public function setGame(Game $game)
    {
        $this->game = $game;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param ServerGroup $serverGroup
     * @return Server
     */
    public function addGroup(ServerGroup $serverGroup)
    {
        $this->groups[] = $serverGroup;

        return $this;
    }

    /**
     * @param ServerGroup $serverGroup
     * @return Server
     */
    public function removeGroup(ServerGroup $serverGroup)
    {
        $this->groups->removeElement($serverGroup);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getReports()
    {
        return $this->reports;
    }
}
