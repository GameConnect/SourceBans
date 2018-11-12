<?php

namespace SourceBans\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="sb_bans", indexes={@ORM\Index(name="server_id", columns={"server_id"}), @ORM\Index(name="admin_id", columns={"admin_id"}), @ORM\Index(name="unban_admin_id", columns={"unban_admin_id"})})
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @ORM\HasLifecycleCallbacks
 */
class Ban implements SteamAccountInterface
{
    const TYPE_STEAM = 0;
    const TYPE_IP = 1;
    const LENGTH_PERMANENT = 0;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @Assert\Choice({Ban::TYPE_STEAM, Ban::TYPE_IP})
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $type = self::TYPE_STEAM;

    /**
     * @var string
     *
     * @Assert\Regex("/^STEAM_[0-4]:[0-1]:\d+|\[U:[0-4]:\d+(:\d+)?\]$/i")
     * @ORM\Column(name="steam", type="string", length=32, nullable=true)
     */
    private $steam;

    /**
     * @var string
     *
     * @Assert\Ip
     * @ORM\Column(name="ip", type="string", length=15, nullable=true)
     */
    private $ip;

    /**
     * @var string
     *
     * @Assert\Length(max=64)
     * @ORM\Column(name="name", type="string", length=64, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     * @ORM\Column(name="reason", type="string", length=255, nullable=false)
     */
    private $reason;

    /**
     * @var int
     *
     * @Assert\Type("integer")
     * @Assert\GreaterThanOrEqual(0)
     * @ORM\Column(name="length", type="integer", nullable=false)
     */
    private $length = self::LENGTH_PERMANENT;

    /**
     * @var string
     *
     * @ORM\Column(name="admin_ip", type="string", length=15, nullable=false)
     */
    private $adminIp;

    /**
     * @var string
     *
     * @Assert\Length(max=255)
     * @ORM\Column(name="unban_reason", type="string", length=255, nullable=true)
     */
    private $unbanReason;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="unban_time", type="datetime", nullable=true)
     */
    private $unbanTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_time", type="datetime", nullable=false)
     */
    private $createTime;

    /**
     * @var Admin
     *
     * @ORM\ManyToOne(targetEntity="Admin", inversedBy="bans")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="admin_id", referencedColumnName="id")
     * })
     */
    private $admin;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Appeal", mappedBy="ban")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ban_id", referencedColumnName="id")
     * })
     */
    private $appeals;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Block", mappedBy="ban")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ban_id", referencedColumnName="id")
     * })
     */
    private $blocks;

    /**
     * @var Server
     *
     * @ORM\ManyToOne(targetEntity="Server", inversedBy="bans")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="server_id", referencedColumnName="id")
     * })
     */
    private $server;

    /**
     * @var Admin
     *
     * @ORM\ManyToOne(targetEntity="Admin")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="unban_admin_id", referencedColumnName="id")
     * })
     */
    private $unbanAdmin;

    /** @var int */
    private $accountId;

    public function __construct()
    {
        $this->appeals = new ArrayCollection();
        $this->blocks = new ArrayCollection();
    }

    public function __toString(): string
    {
        return ($this->getType() == self::TYPE_IP ? $this->getIp() : $this->getSteam()) ?: '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSteam(): ?string
    {
        return $this->steam;
    }

    public function setSteam(?string $steam): self
    {
        $this->steam = $steam;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function setLength(int $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getAdminIp(): ?string
    {
        return $this->adminIp;
    }

    public function setAdminIp(string $adminIp): self
    {
        $this->adminIp = $adminIp;

        return $this;
    }

    public function getUnbanReason(): ?string
    {
        return $this->unbanReason;
    }

    public function setUnbanReason(?string $unbanReason): self
    {
        $this->unbanReason = $unbanReason;

        return $this;
    }

    public function getUnbanTime(): ?\DateTime
    {
        return $this->unbanTime;
    }

    public function setUnbanTime(?\DateTime $unbanTime): self
    {
        $this->unbanTime = $unbanTime;

        return $this;
    }

    public function getCreateTime(): ?\DateTime
    {
        return $this->createTime;
    }

    public function getAdmin(): ?Admin
    {
        return $this->admin;
    }

    public function setAdmin(?Admin $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function getAppeals(): Collection
    {
        return $this->appeals;
    }

    public function getBlocks(): Collection
    {
        return $this->blocks;
    }

    public function getServer(): ?Server
    {
        return $this->server;
    }

    public function setServer(?Server $server): self
    {
        $this->server = $server;

        return $this;
    }

    public function getUnbanAdmin(): ?Admin
    {
        return $this->unbanAdmin;
    }

    public function setUnbanAdmin(?Admin $unbanAdmin): self
    {
        $this->unbanAdmin = $unbanAdmin;

        return $this;
    }

    public function isActive(): bool
    {
        return !$this->isExpired() && !$this->isUnbanned();
    }

    public function isExpired(): bool
    {
        $expireTime = $this->getCreateTime()->modify('+'.$this->getLength().' minutes');

        return !$this->isPermanent() && $expireTime < new \DateTime();
    }

    public function isPermanent(): bool
    {
        return $this->getLength() == self::LENGTH_PERMANENT;
    }

    public function isUnbanned(): bool
    {
        return (bool) $this->getUnbanTime();
    }

    public function getSteamAccountId(): ?int
    {
        if (isset($this->accountId)) {
            return $this->accountId;
        }
        if (!$this->getSteam()) {
            return null;
        }

        try {
            $steam = new \SteamID($this->getSteam());
        } catch (\InvalidArgumentException $exception) {
            return null;
        }

        return $this->accountId = $steam->GetAccountID();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createTime = new \DateTime();
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        if (!empty($this->steam)) {
            $steam = new \SteamID($this->steam);
            $this->steam = $steam->RenderSteam3();
        }
    }
}
