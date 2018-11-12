<?php

namespace SourceBans\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="sb_comments", indexes={@ORM\Index(name="admin_id", columns={"admin_id"}), @ORM\Index(name="object", columns={"object_type", "object_id"}), @ORM\Index(name="update_admin_id", columns={"update_admin_id"})})
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @ORM\HasLifecycleCallbacks
 */
class Comment
{
    const TYPE_APPEAL = 'P';
    const TYPE_BAN = 'B';
    const TYPE_REPORT = 'S';

    /**
     * @var int
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
     * @var int
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
     * @ORM\Column(name="create_time", type="datetime", nullable=false)
     */
    private $createTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_time", type="datetime", nullable=true)
     */
    private $updateTime;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getObjectType(): ?string
    {
        return $this->objectType;
    }

    public function getObjectId(): ?int
    {
        return $this->objectId;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getCreateTime(): ?\DateTime
    {
        return $this->createTime;
    }

    public function getUpdateTime(): ?\DateTime
    {
        return $this->updateTime;
    }

    public function getAdmin(): ?Admin
    {
        return $this->admin;
    }

    public function setAdmin(Admin $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function getUpdateAdmin(): ?Admin
    {
        return $this->updateAdmin;
    }

    public function setUpdateAdmin(?Admin $updateAdmin): self
    {
        $this->updateAdmin = $updateAdmin;

        return $this;
    }

    /**
     * @param Appeal|Ban|Report $object
     *
     * @return self
     */
    public function setObject($object): self
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
        $this->createTime = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updateTime = new \DateTime();
    }
}
