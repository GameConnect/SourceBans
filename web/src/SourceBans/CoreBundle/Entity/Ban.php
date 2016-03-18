<?php

namespace SourceBans\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use SourceBans\CoreBundle\Validator\Constraints\Ban as BanAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Bans
 *
 * @BanAssert\Type
 * @ORM\Entity
 * @ORM\Table(name="bans", indexes={@ORM\Index(name="server_id", columns={"server_id"}), @ORM\Index(name="admin_id", columns={"admin_id"}), @ORM\Index(name="unban_admin_id", columns={"unban_admin_id"})})
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @ORM\HasLifecycleCallbacks
 */
class Ban implements EntityInterface, SteamAccountInterface
{
    const TYPE_STEAM       = 0;
    const TYPE_IP          = 1;
    const LENGTH_PERMANENT = 0;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @Assert\Choice({Ban::TYPE_STEAM, Ban::TYPE_IP})
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $type = self::TYPE_STEAM;

    /**
     * @var string
     *
     * @ORM\Column(name="steam", type="string", length=32, nullable=true)
     */
    private $steam;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=45, nullable=true)
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
     * @var integer
     *
     * @Assert\Type("integer")
     * @ORM\Column(name="length", type="integer", nullable=false)
     */
    private $length;

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
     * @ORM\Column(name="unban_time", type="timestamp", nullable=true)
     */
    private $unbanTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_time", type="timestamp", nullable=false)
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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Appeal", mappedBy="ban")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ban_id", referencedColumnName="id")
     * })
     */
    private $appeals;

    /**
     * @var ArrayCollection
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

    /**
     * @var integer
     */
    private $accountId;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->appeals = new ArrayCollection;
        $this->blocks = new ArrayCollection;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)($this->getType() == self::TYPE_IP ? $this->getIp() : $this->getSteam());
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param integer $type
     * @return Ban
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getSteam()
    {
        return $this->steam;
    }

    /**
     * @param string $steam
     * @return Ban
     */
    public function setSteam($steam)
    {
        $this->steam = $steam;

        return $this;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     * @return Ban
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

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
     * @return Ban
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     * @return Ban
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * @return integer
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param integer $length
     * @return Ban
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdminIp()
    {
        return $this->adminIp;
    }

    /**
     * @param string $adminIp
     * @return Ban
     */
    public function setAdminIp($adminIp)
    {
        $this->adminIp = $adminIp;

        return $this;
    }

    /**
     * @return string
     */
    public function getUnbanReason()
    {
        return $this->unbanReason;
    }

    /**
     * @param string $unbanReason
     * @return Ban
     */
    public function setUnbanReason($unbanReason)
    {
        $this->unbanReason = $unbanReason;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUnbanTime()
    {
        return $this->unbanTime;
    }

    /**
     * @param \DateTime $unbanTime
     * @return Ban
     */
    public function setUnbanTime(\DateTime $unbanTime)
    {
        $this->unbanTime = $unbanTime;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * @return Admin
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @param Admin $admin
     * @return Ban
     */
    public function setAdmin(Admin $admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getAppeals()
    {
        return $this->appeals;
    }

    /**
     * @return ArrayCollection
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * @return Server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param Server $server
     * @return Ban
     */
    public function setServer(Server $server)
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @return Admin
     */
    public function getUnbanAdmin()
    {
        return $this->unbanAdmin;
    }

    /**
     * @param Admin $unbanAdmin
     * @return Ban
     */
    public function setUnbanAdmin(Admin $unbanAdmin)
    {
        $this->unbanAdmin = $unbanAdmin;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return !$this->isExpired() && !$this->isUnbanned();
    }

    /**
     * @return boolean
     */
    public function isExpired()
    {
        $expireTime = $this->getCreateTime()->modify('+' . $this->getLength() . ' minutes');

        return !$this->isPermanent() && $expireTime < new \DateTime;
    }

    /**
     * @return boolean
     */
    public function isInactive()
    {
        return $this->isExpired() || $this->isUnbanned();
    }

    /**
     * @return boolean
     */
    public function isPermanent()
    {
        return !$this->getLength();
    }

    /**
     * @return boolean
     */
    public function isUnbanned()
    {
        return !!$this->getUnbanTime();
    }

    /**
     * @inheritdoc
     */
    public function getSteamAccountId()
    {
        if (isset($this->accountId)) {
            return $this->accountId;
        }
        if ($this->getSteam() == '') {
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
        $this->createTime = new \DateTime;
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
