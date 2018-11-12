<?php

namespace SourceBans\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity({"host", "port"})
 * @ORM\Entity
 * @ORM\Table(name="sb_servers", uniqueConstraints={@ORM\UniqueConstraint(name="host", columns={"host", "port"})}, indexes={@ORM\Index(name="game_id", columns={"game_id"})})
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Server
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
     * @Assert\Length(max=255)
     * @ORM\Column(name="host", type="string", length=128, nullable=false)
     */
    private $host;

    /**
     * @var int
     *
     * @Assert\Type("integer")
     * @ORM\Column(name="port", type="smallint", nullable=false)
     */
    private $port = 27015;

    /**
     * @var string
     *
     * @Assert\Length(max=32)
     * @ORM\Column(name="rcon_password", type="string", length=64, nullable=true)
     */
    private $rconPassword;

    /**
     * @var bool
     *
     * @Assert\Type("boolean")
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled = true;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Action", mappedBy="server")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="server_id", referencedColumnName="id")
     * })
     */
    private $actions;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Ban", mappedBy="server")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="server_id", referencedColumnName="id")
     * })
     */
    private $bans;

    /**
     * @var Collection
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
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="ServerGroup", inversedBy="servers", cascade={"persist"})
     * @ORM\JoinTable(name="sb_servers_server_groups",
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
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Report", mappedBy="server")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="server_id", referencedColumnName="id")
     * })
     */
    private $reports;

    public function __construct()
    {
        $this->actions = new ArrayCollection();
        $this->bans = new ArrayCollection();
        $this->blocks = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->reports = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getHost().':'.$this->getPort();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    public function getRconPassword(): ?string
    {
        return $this->rconPassword;
    }

    public function setRconPassword(?string $rconPassword): self
    {
        $this->rconPassword = $rconPassword;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function getBans(): Collection
    {
        return $this->bans;
    }

    public function getBlocks(): Collection
    {
        return $this->blocks;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(Game $game): self
    {
        $this->game = $game;

        return $this;
    }

    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addGroup(ServerGroup $serverGroup): self
    {
        $this->groups[] = $serverGroup;

        return $this;
    }

    public function removeGroup(ServerGroup $serverGroup): self
    {
        $this->groups->removeElement($serverGroup);

        return $this;
    }

    public function getReports(): Collection
    {
        return $this->reports;
    }
}
