<?php

namespace SourceBans\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Comment
 *
 * @ORM\Entity
 * @ORM\Table(name="comments", indexes={@ORM\Index(name="admin_id", columns={"admin_id"}), @ORM\Index(name="object", columns={"object_type", "object_id"}), @ORM\Index(name="update_admin_id", columns={"update_admin_id"})})
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @ORM\HasLifecycleCallbacks
 */
class Comment
{
    const TYPE_BAN    = 'B';
    const TYPE_APPEAL = 'P';
    const TYPE_REPORT = 'S';

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
     * @ORM\Column(name="object_type", type="string", length=1, nullable=false)
     */
    private $objectType;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     */
    private $objectId;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @ORM\Column(name="message", type="text", nullable=false)
     */
    private $message;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_time", type="timestamp", nullable=true)
     */
    private $updateTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_time", type="timestamp", nullable=false)
     */
    private $createTime;

    /**
     * @var Admin
     *
     * @ORM\ManyToOne(targetEntity="Admin", inversedBy="comments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="admin_id", referencedColumnName="id")
     * })
     */
    private $admin;

    /**
     * @var Admin
     *
     * @ORM\ManyToOne(targetEntity="Admin")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="update_admin_id", referencedColumnName="id")
     * })
     */
    private $updateAdmin;

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
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @return integer
     */
    public function getObjectId()
    {
        return $this->objectId;
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
     * @return Comment
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateTime()
    {
        return $this->updateTime;
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
     * @return Comment
     */
    public function setAdmin(Admin $admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * @return Admin
     */
    public function getUpdateAdmin()
    {
        return $this->updateAdmin;
    }

    /**
     * @param Admin $updateAdmin
     * @return Comment
     */
    public function setUpdateAdmin(Admin $updateAdmin)
    {
        $this->updateAdmin = $updateAdmin;

        return $this;
    }

    /**
     * @param Appeal|Ban|Report $object
     * @return Comment
     */
    public function setObject($object)
    {
        if ($object instanceof Appeal) {
            $this->objectType = self::TYPE_APPEAL;
        }
        if ($object instanceof Ban) {
            $this->objectType = self::TYPE_BAN;
        }
        if ($object instanceof Report) {
            $this->objectType = self::TYPE_REPORT;
        }

        $this->objectId = $object->getId();

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createTime = new \DateTime;
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updateTime = new \DateTime;
    }
}
