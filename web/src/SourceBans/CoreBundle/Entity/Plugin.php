<?php

namespace SourceBans\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Plugin
 *
 * @ORM\Entity
 * @ORM\Table(name="plugins")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Plugin
{
    const STATUS_UNINSTALLED = 0;
    const STATUS_INSTALLED   = 1;
    const STATUS_ENABLED     = 2;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="class", type="string", length=255, nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $class;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status = self::STATUS_UNINSTALLED;

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     * @return Plugin
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param integer $status
     * @return Plugin
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
}
