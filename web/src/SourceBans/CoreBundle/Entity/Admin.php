<?php

namespace SourceBans\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use SourceBans\CoreBundle\Validator\Constraints\Admin as AdminAssert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Admin
 *
 * @UniqueEntity("name")
 * @AdminAssert\Identity
 * @ORM\Entity(repositoryClass="AdminRepository")
 * @ORM\Table(name="admins", uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"}), @ORM\UniqueConstraint(name="auth", columns={"auth", "identity"})}, indexes={@ORM\Index(name="group_id", columns={"group_id"})})
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @ORM\HasLifecycleCallbacks
 */
class Admin implements EntityInterface, UserInterface, \Serializable
{
    const AUTH_IP          = 'ip';
    const AUTH_NAME        = 'name';
    const AUTH_STEAM       = 'steam';
    const FLAG_RESERVATION = 'a';
    const FLAG_GENERIC     = 'b';
    const FLAG_KICK        = 'c';
    const FLAG_BAN         = 'd';
    const FLAG_UNBAN       = 'e';
    const FLAG_SLAY        = 'f';
    const FLAG_CHANGEMAP   = 'g';
    const FLAG_CONVARS     = 'h';
    const FLAG_CONFIG      = 'i';
    const FLAG_CHAT        = 'j';
    const FLAG_VOTE        = 'k';
    const FLAG_PASSWORD    = 'l';
    const FLAG_RCON        = 'm';
    const FLAG_CHEATS      = 'n';
    const FLAG_CUSTOM1     = 'o';
    const FLAG_CUSTOM2     = 'p';
    const FLAG_CUSTOM3     = 'q';
    const FLAG_CUSTOM4     = 'r';
    const FLAG_CUSTOM5     = 's';
    const FLAG_CUSTOM6     = 't';
    const FLAG_ROOT        = 'z';

    /**
     * @var integer
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
     * @ORM\Column(name="auth", type="string", nullable=false)
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
     * @ORM\Column(name="language", type="string", length=2, nullable=true)
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="theme", type="string", length=32, nullable=true)
     */
    private $theme;

    /**
     * @var string
     *
     * @ORM\Column(name="timezone", type="string", length=32, nullable=true)
     */
    private $timezone;

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
     * @ORM\Column(name="login_time", type="timestamp", nullable=true)
     */
    private $loginTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_time", type="timestamp", nullable=false)
     */
    private $createTime;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Action", mappedBy="admin")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="admin_id", referencedColumnName="id")
     * })
     */
    private $actions;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Ban", mappedBy="admin")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="admin_id", referencedColumnName="id")
     * })
     */
    private $bans;

    /**
     * @var ArrayCollection
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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Log", mappedBy="admin")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="admin_id", referencedColumnName="id")
     * })
     */
    private $logs;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="ServerGroup", inversedBy="admins", cascade={"persist"})
     * @ORM\JoinTable(name="admins_server_groups",
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

    /**
     * @var string
     */
    private $communityId;

    /**
     * @var string
     */
    private $flags;

    /**
     * @var integer
     */
    private $immunity;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->actions = new ArrayCollection;
        $this->bans = new ArrayCollection;
        $this->comments = new ArrayCollection;
        $this->logs = new ArrayCollection;
        $this->serverGroups = new ArrayCollection;
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
     * @return Admin
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * @param string $auth
     * @return Admin
     */
    public function setAuth($auth)
    {
        $this->auth = $auth;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @param string $identity
     * @return Admin
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return Admin
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Admin
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     * @return Admin
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     * @return Admin
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     * @return Admin
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * @return string
     */
    public function getServerPassword()
    {
        return $this->serverPassword;
    }

    /**
     * @param string $serverPassword
     * @return Admin
     */
    public function setServerPassword($serverPassword)
    {
        $this->serverPassword = $serverPassword;

        return $this;
    }

    /**
     * @return string
     */
    public function getValidationKey()
    {
        return $this->validationKey;
    }

    /**
     * @param string $validationKey
     * @return Admin
     */
    public function setValidationKey($validationKey)
    {
        $this->validationKey = $validationKey;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLoginTime()
    {
        return $this->loginTime;
    }

    /**
     * @param \DateTime $loginTime
     * @return Admin
     */
    public function setLoginTime(\DateTime $loginTime)
    {
        $this->loginTime = $loginTime;

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
     * @return ArrayCollection
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @return ArrayCollection
     */
    public function getBans()
    {
        return $this->bans;
    }

    /**
     * @return ArrayCollection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param Group $group
     * @return Admin
     */
    public function setGroup(Group $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @return ArrayCollection
     */
    public function getServerGroups()
    {
        return $this->serverGroups;
    }

    /**
     * @param ServerGroup $serverGroup
     * @return Admin
     */
    public function addServerGroup(ServerGroup $serverGroup)
    {
        $this->serverGroups[] = $serverGroup;

        return $this;
    }

    /**
     * @param ServerGroup $serverGroup
     * @return Admin
     */
    public function removeServerGroup(ServerGroup $serverGroup)
    {
        $this->serverGroups->removeElement($serverGroup);

        return $this;
    }

    /**
     * Returns the Steam Community ID
     *
     * @return string
     */
    public function getCommunityId()
    {
        if (isset($this->communityId)) {
            return $this->communityId;
        }
        if ($this->getAuth() != self::AUTH_STEAM) {
            return null;
        }

        $accountId = 0;
        $identity = $this->getIdentity();

        if (preg_match('/^STEAM_[0-9]:([0-9]):([0-9]+)$/i', $identity, $matches)) {
            $accountId = $matches[1] + $matches[2] * 2;
        } elseif (preg_match('/^\[U:[0-9]:([0-9]+)\]$/i', $identity, $matches)) {
            $accountId = $matches[1];
        }

        return $this->communityId = gmp_strval(gmp_add('76561197960265728', $accountId));
    }

    /**
     * Returns the server permissions
     *
     * @return string
     */
    public function getFlags()
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
     * Returns whether the admin has a server permission
     *
     * @param string $flag
     * @return boolean
     */
    public function hasFlag($flag)
    {
        $flags = $this->getFlags();

        return strpos($flags, $flag) !== false || strpos($flags, self::FLAG_ROOT) !== false;
    }

    /**
     * Returns the immunity level
     *
     * @return integer
     */
    public function getImmunity()
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

    /**
     * @inheritdoc
     */
    public function getUsername()
    {
        return $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getRoles()
    {
        $defaultRoles = ['ROLE_ADMIN'];

        $group = $this->getGroup();
        if ($group === null) {
            return $defaultRoles;
        }

        return array_merge(
            $defaultRoles,
            array_map(
                function (Permission $permission) {
                    return $permission->getRole();
                },
                $group->getPermissions()->toArray()
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->name,
            $this->password,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->name,
            $this->password,
        ) = unserialize($serialized);
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
        if ($this->auth == self::AUTH_STEAM) {
            $this->identity = strtoupper($this->identity);
        }
    }
}
