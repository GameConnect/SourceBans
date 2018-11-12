<?php

namespace SourceBans\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity("folder")
 * @ORM\Entity
 * @ORM\Table(name="sb_games", uniqueConstraints={@ORM\UniqueConstraint(name="folder", columns={"folder"})})
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Game
{
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
     * @Assert\NotBlank
     * @Assert\Length(max=32)
     * @ORM\Column(name="name", type="string", length=32, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Length(max=32)
     * @ORM\Column(name="folder", type="string", length=32, nullable=false)
     */
    private $folder;

    /**
     * @var string|File
     *
     * @Assert\NotBlank
     * @Assert\Length(max=32)
     * @Assert\File(mimeTypes={"image/gif", "image/jpeg", "image/png"})
     * @ORM\Column(name="icon", type="string", length=32, nullable=false)
     */
    private $icon;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Server", mappedBy="game")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="game_id", referencedColumnName="id")
     * })
     */
    private $servers;

    public function __construct()
    {
        $this->servers = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName() ?: '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFolder(): ?string
    {
        return $this->folder;
    }

    public function setFolder(string $folder): self
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * @return string|File
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param string|File $icon
     *
     * @return self
     */
    public function setIcon($icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getServers(): Collection
    {
        return $this->servers;
    }
}
