<?php

namespace SourceBans\CoreBundle\Entity;

/**
 * SettingRepository
 */
class SettingRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @return array
     */
    public function all()
    {
        static $settings = [];
        if ($settings !== []) {
            return $settings;
        }

        $query = $this->createQueryBuilder('settings')->getQuery();

        return $settings = array_column($query->getArrayResult(), 'value', 'name');
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        $settings = $this->all();
        if (!isset($settings[$name])) {
            return null;
        }

        return $settings[$name];
    }

    /**
     * @param array $settings
     */
    public function update(array $settings)
    {
        foreach ($settings as $name => $value) {
            $this->createQueryBuilder('settings')
                ->update(Setting::class, 'settings')
                ->set('settings.value', ':value')
                ->where('settings.name = :name')
                ->setParameter('name', $name)
                ->setParameter('value', (string)$value)
                ->getQuery()
                ->execute();
        }
    }
}
