<?php

namespace SourceBans\Repository;

use Rb\Specification\Doctrine\SpecificationRepository;
use SourceBans\Entity\Setting;

class SettingRepository extends SpecificationRepository
{
    public function __get(string $name)
    {
        return $this->get($name);
    }

    public function __isset(string $name): bool
    {
        $settings = $this->all();

        return isset($settings[$name]);
    }

    public function all(): array
    {
        static $settings = [];
        if ($settings !== []) {
            return $settings;
        }

        $query = $this->createQueryBuilder('settings')->getQuery();

        return $settings = array_column($query->getArrayResult(), 'value', 'name');
    }

    public function get(string $name)
    {
        $settings = $this->all();
        if (!isset($settings[$name])) {
            return null;
        }

        return $settings[$name];
    }

    public function update(array $settings)
    {
        foreach ($settings as $name => $value) {
            $this->createQueryBuilder('settings')
                ->update(Setting::class, 'settings')
                ->set('settings.value', ':value')
                ->where('settings.name = :name')
                ->setParameter('name', $name)
                ->setParameter('value', (string) $value)
                ->getQuery()
                ->execute();
        }
    }
}
