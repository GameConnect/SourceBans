<?php

namespace SourceBans\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="sb_blocks", indexes={@ORM\Index(name="ban_id", columns={"ban_id"}), @ORM\Index(name="server_id", columns={"server_id"})})
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @ORM\HasLifecycleCallbacks
 */
class Block
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="name", type="string", length=64, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Id
     * @ORM\Column(name="create_time", type="datetime", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $createTime;

    /**
     * @var Ban
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\ManyToOne(targetEntity="Ban", inversedBy="blocks")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ban_id", referencedColumnName="id")
     * })
     */
    private $ban;

    /**
     * @var Server
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\ManyToOne(targetEntity="Server", inversedBy="blocks")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="server_id", referencedColumnName="id")
     * })
     */
    private $server;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreateTime(): ?\DateTime
    {
        return $this->createTime;
    }

    public function getBan(): ?Ban
    {
        return $this->ban;
    }

    public function setBan(Ban $ban): self
    {
        $this->ban = $ban;

        return $this;
    }

    public function getServer(): ?Server
    {
        return $this->server;
    }

    public function setServer(Server $server): self
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createTime = new \DateTime();
    }
}
