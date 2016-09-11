<?php

namespace SourceBans\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Report
 *
 * @ORM\Entity
 * @ORM\Table(name="reports", indexes={@ORM\Index(name="server_id", columns={"server_id"})})
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @ORM\HasLifecycleCallbacks
 */
class Report extends AbstractSteamAccount implements EntityInterface, SteamAccountInterface
{
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
     * @Assert\NotBlank
     * @Assert\Length(max=64)
     * @ORM\Column(name="name", type="string", length=64, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @Assert\Regex("/^STEAM_[0-9]:[0-9]:[0-9]+|\[U:[0-9]:[0-9]+\]$/i")
     * @ORM\Column(name="steam", type="string", length=32, nullable=true)
     */
    private $steam;

    /**
     * @var string
     *
     * @Assert\Ip(version="all")
     * @ORM\Column(name="ip", type="string", length=15, nullable=true)
     */
    private $ip;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     * @ORM\Column(name="reason", type="string", length=255, nullable=false)
     */
    private $reason;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Length(max=64)
     * @ORM\Column(name="user_name", type="string", length=64, nullable=false)
     */
    private $userName;

    /**
     * @var string
     *
     * @Assert\Email
     * @Assert\Length(max=128)
     * @ORM\Column(name="user_email", type="string", length=128, nullable=true)
     */
    private $userEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="user_ip", type="string", length=15, nullable=false)
     */
    private $userIp;

    /**
     * @var boolean
     *
     * @Assert\Type("boolean")
     * @ORM\Column(name="archived", type="boolean", nullable=false)
     */
    private $archived = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_time", type="timestamp", nullable=false)
     */
    private $createTime;

    /**
     * @var Server
     *
     * @ORM\ManyToOne(targetEntity="Server", inversedBy="reports")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="server_id", referencedColumnName="id")
     * })
     */
    private $server;

    /**
     * @var integer
     */
    private $accountId;

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
     * @return Report
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * @return Report
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
     * @return Report
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

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
     * @return Report
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     * @return Report
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserEmail()
    {
        return $this->userEmail;
    }

    /**
     * @param string $userEmail
     * @return Report
     */
    public function setUserEmail($userEmail)
    {
        $this->userEmail = $userEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserIp()
    {
        return $this->userIp;
    }

    /**
     * @param string $userIp
     * @return Report
     */
    public function setUserIp($userIp)
    {
        $this->userIp = $userIp;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isArchived()
    {
        return $this->archived;
    }

    /**
     * @param boolean $archived
     * @return Report
     */
    public function setArchived($archived)
    {
        $this->archived = $archived;

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
     * @return Server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param Server $server
     * @return Report
     */
    public function setServer(Server $server)
    {
        $this->server = $server;

        return $this;
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
