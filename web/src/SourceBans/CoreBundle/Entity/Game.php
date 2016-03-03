<?php

namespace SourceBans\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Game
 *
 * @UniqueEntity("folder")
 * @ORM\Entity
 * @ORM\Table(name="games", uniqueConstraints={@ORM\UniqueConstraint(name="folder", columns={"folder"})})
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Game implements EntityInterface
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
     * @var string|UploadedFile
     *
     * @Assert\NotBlank
     * @Assert\Length(max=32)
     * @Assert\File(mimeTypes={"image/gif", "image/jpeg", "image/png"})
     * @ORM\Column(name="icon", type="string", length=32, nullable=false)
     */
    private $icon;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Server", mappedBy="game")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="game_id", referencedColumnName="id")
     * })
     */
    private $servers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->servers = new ArrayCollection;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Game
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @param string $folder
     * @return Game
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * @return string|UploadedFile
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param string|UploadedFile $icon
     * @return Game
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getServers()
    {
        return $this->servers;
    }
}
