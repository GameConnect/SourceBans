<?php

namespace SourceBans\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SourceBans\Validator\Constraints\AdminIdentity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AdminIdentity
 * @UniqueEntity("name")
 * @UniqueEntity({"auth", "identity"})
 * @ORM\Entity(repositoryClass="SourceBans\Repository\AdminRepository")
 * @ORM\Table(name="sb_admins", uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"}), @ORM\UniqueConstraint(name="auth", columns={"auth", "identity"})}, indexes={@ORM\Index(name="group_id", columns={"group_id"})})
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @ORM\HasLifecycleCallbacks
 */
class Admin implements SteamAccountInterface, UserInterface
{
    const AUTH_IP = 'ip';
    const AUTH_NAME = 'name';
    const AUTH_STEAM = 'steam';
    const FLAG_RESERVATION = 'a';
    const FLAG_GENERIC = 'b';
    const FLAG_KICK = 'c';
    const FLAG_BAN = 'd';
    const FLAG_UNBAN = 'e';
    const FLAG_SLAY = 'f';
    const FLAG_CHANGEMAP = 'g';
    const FLAG_CONVARS = 'h';
    const FLAG_CONFIG = 'i';
    const FLAG_CHAT = 'j';
    const FLAG_VOTE = 'k';
    const FLAG_PASSWORD = 'l';
    const FLAG_RCON = 'm';
    const FLAG_CHEATS = 'n';
    const FLAG_CUSTOM1 = 'o';
    const FLAG_CUSTOM2 = 'p';
    const FLAG_CUSTOM3 = 'q';
    const FLAG_CUSTOM4 = 'r';
    const FLAG_CUSTOM5 = 's';
    const FLAG_CUSTOM6 = 't';
    const FLAG_ROOT = 'z';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="smallint", nullable=false)
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
     * @Assert\Choice({Admin::AUTH_STEAM, Admin::AUTH_IP, Admin::AUTH_NAME})
     * @ORM\Column(name="auth", type="string", length=8, nullable=false)
     */
    private $auth = self::AUTH_STEAM;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Length(max=64)
     * @ORM\Column(name="identity", type="string", length=64, nullable=false)
     */
    private $identity;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=64, nullable=true)
     */
    private $password;

    /**
     * @var string
     *
     * @Assert\Email
     * @Assert\Length(max=128)
     * @ORM\Column(name="email", type="string", length=128, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="server_password", type="string", length=64, nullable=true)
     */
    private $serverPassword;

    /**
     * @var string
     *
     * @ORM\Column(name="validation_key", type="string", length=64, nullable=true)
     */
    private $validationKey;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_time", type="datetime", nullable=false)
     */
    private $createTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="login_time", type="datetime", nullable=true)
     */
    private $loginTime;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Action", mappedBy="admin")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="admin_id", referencedColumnName="id")
     * })
     */
    private $actions;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Ban", mappedBy="admin")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="admin_id", referencedColumnName="id")
     * })
     */
    private $bans;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="admin")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="admin_id", referencedColumnName="id")
     * })
     */
    private $comments;

    /**
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="admins")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     * })
     */
    private $group;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Log", mappedBy="admin")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="admin_id", referencedColumnName="id")
     * })
     */
    private $logs;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="ServerGroup", inversedBy="admins", cascade={"persist"})
     * @ORM\JoinTable(name="sb_admins_server_groups",
     *   joinColumns={
     *     @ORM\JoinColumn(name="admin_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     *   }
     * )
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $serverGroups;

    /** @var string */
    private $plainPassword;

    /** @var string */
    private $flags;

    /** @var int */
    private $immunity;

    /** @var int */
    private $accountId;

    public function __construct()
    {
        $this->actions = new ArrayCollection();
        $this->bans = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->serverGroups = new ArrayCollection();
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

    public function getAuth(): string
    {
        return $this->auth;
    }

    public function setAuth(string $auth): self
    {
        $this->auth = $auth;

        return $this;
    }

    public function getIdentity(): ?string
    {
        return $this->identity;
    }

    public function setIdentity(string $identity): self
    {
        $this->identity = $identity;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getServerPassword(): ?string
    {
        return $this->serverPassword;
    }

    public function setServerPassword(?string $serverPassword): self
    {
        $this->serverPassword = $serverPassword;

        return $this;
    }

    public function getValidationKey(): ?string
    {
        return $this->validationKey;
    }

    public function setValidationKey(?string $validationKey): self
    {
        $this->validationKey = $validationKey;

        return $this;
    }

    public function getCreateTime(): ?\DateTime
    {
        return $this->createTime;
    }

    public function getLoginTime(): ?\DateTime
    {
        return $this->loginTime;
    }

    public function setLoginTime(?\DateTime $loginTime): self
    {
        $this->loginTime = $loginTime;

        return $this;
    }

    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function getBans(): Collection
    {
        return $this->bans;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(?Group $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function getServerGroups(): Collection
    {
        return $this->serverGroups;
    }

    public function addServerGroup(ServerGroup $serverGroup): self
    {
        $this->serverGroups[] = $serverGroup;

        return $this;
    }

    public function removeServerGroup(ServerGroup $serverGroup): self
    {
        $this->serverGroups->removeElement($serverGroup);

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * Returns the server permissions.
     */
    public function getFlags(): string
    {
        if (isset($this->flags)) {
            return $this->flags;
        }

        /** @var ServerGroup $serverGroup */
        foreach ($this->getServerGroups() as $serverGroup) {
            $this->flags .= $serverGroup->getFlags();
        }

        return $this->flags = count_chars($this->flags, 3);
    }

    /**
     * Returns whether the admin has a server permission.
     */
    public function hasFlag(string $flag): bool
    {
        $flags = $this->getFlags();

        return strpos($flags, $flag) !== false || strpos($flags, self::FLAG_ROOT) !== false;
    }

    /**
     * Returns the immunity level.
     */
    public function getImmunity(): int
    {
        if (isset($this->immunity)) {
            return $this->immunity;
        }

        $this->immunity = 0;
        /** @var ServerGroup $serverGroup */
        foreach ($this->getServerGroups() as $serverGroup) {
            if ($this->immunity < $serverGroup->getImmunity()) {
                $this->immunity = $serverGroup->getImmunity();
            }
        }

        return $this->immunity;
    }

    public function getSteamAccountId(): ?int
    {
        if (isset($this->accountId)) {
            return $this->accountId;
        }
        if ($this->getAuth() != self::AUTH_STEAM) {
            return null;
        }

        try {
            $steam = new \SteamID($this->getIdentity());
        } catch (\InvalidArgumentException $exception) {
            return null;
        }

        return $this->accountId = $steam->GetAccountID();
    }

    public function getUsername(): ?string
    {
        return $this->getName();
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getRoles(): array
    {
        $defaultRoles = ['ROLE_ADMIN'];

        $group = $this->getGroup();
        if (!$group) {
            return $defaultRoles;
        }

        return array_merge(
            $defaultRoles,
            $group->getRoles()
        );
    }

    public function eraseCredentials()
    {
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
        if ($this->auth == self::AUTH_STEAM) {
            $steam = new \SteamID($this->identity);
            $this->identity = $steam->RenderSteam3();
        }
    }
}
