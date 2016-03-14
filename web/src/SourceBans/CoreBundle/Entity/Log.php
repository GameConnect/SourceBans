<?php

namespace SourceBans\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Log
 *
 * @ORM\Entity
 * @ORM\Table(name="logs", indexes={@ORM\Index(name="admin_id", columns={"admin_id"})})
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @ORM\HasLifecycleCallbacks
 */
class Log implements EntityInterface
{
    const TYPE_ERROR       = 'e';
    const TYPE_INFORMATION = 'm';
    const TYPE_WARNING     = 'w';

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
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=64, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="string", length=255, nullable=false)
     */
    private $message;

    /**
     * @var string
     *
     * @ORM\Column(name="function", type="text", nullable=false)
     */
    private $function;

    /**
     * @var string
     *
     * @ORM\Column(name="query", type="string", length=255, nullable=true)
     */
    private $query;

    /**
     * @var string
     *
     * @ORM\Column(name="admin_ip", type="string", length=15, nullable=false)
     */
    private $adminIp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_time", type="timestamp", nullable=false)
     */
    private $createTime;

    /**
     * @var Admin
     *
     * @ORM\ManyToOne(targetEntity="Admin", inversedBy="logs")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="admin_id", referencedColumnName="id")
     * })
     */
    private $admin;

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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Log
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Log
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return Log
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * @param string $function
     * @return Log
     */
    public function setFunction($function)
    {
        $this->function = $function;

        return $this;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string $query
     * @return Log
     */
    public function setQuery($query)
    {
        $this->query = $query;

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
     * @return Log
     */
    public function setAdminIp($adminIp)
    {
        $this->adminIp = $adminIp;

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
     * @return Log
     */
    public function setAdmin(Admin $admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createTime = new \DateTime;
    }
}
