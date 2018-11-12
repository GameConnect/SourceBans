<?php

namespace SourceBans\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="sb_overrides")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Override
{
    const TYPE_COMMAND = 'command';
    const TYPE_GROUP = 'group';

    /**
     * @var string
     *
     * @Assert\Choice({Override::TYPE_COMMAND, Override::TYPE_GROUP})
     * @ORM\Id
     * @ORM\Column(name="type", type="string", length=8, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $type;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Length(max=32)
     * @ORM\Id
     * @ORM\Column(name="name", type="string", length=32, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $name;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @ORM\Column(name="flags", type="string", length=30, nullable=false)
     */
    private $flags;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
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

    public function getFlags(): ?string
    {
        return $this->flags;
    }

    public function setFlags(string $flags): self
    {
        $this->flags = $flags;

        return $this;
    }
}
