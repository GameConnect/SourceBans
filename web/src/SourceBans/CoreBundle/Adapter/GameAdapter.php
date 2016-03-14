<?php

namespace SourceBans\CoreBundle\Adapter;

use Pagerfanta\Pagerfanta;
use SourceBans\CoreBundle\Entity\EntityInterface;
use SourceBans\CoreBundle\Entity\Game;
use SourceBans\CoreBundle\Event\AdapterEvents;
use SourceBans\CoreBundle\Event\GameAdapterEvent;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\GameForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * GameAdapter
 */
class GameAdapter extends AbstractAdapter
{
    /**
     * @var string
     */
    protected $imageDir;

    /**
     * @inheritdoc
     * @param string $imageDir
     */
    public function __construct(ContainerInterface $container, $entityClass, $imageDir)
    {
        parent::__construct($container, $entityClass);

        $this->imageDir = $imageDir;
    }

    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function all($limit = null, $page = null, $sort = null, $order = null, array $options = [])
    {
        $query = $this->repository->createQueryBuilder('game')
            ->orderBy(sprintf('game.%s', $sort ?: 'name'), $order)
            ->getQuery();

        return static::queryToPager($query, $limit, $page);
    }

    /**
     * @inheritdoc
     */
    public function allBy(array $criteria, $limit = null, $page = null)
    {
        $offset = (null === $page ?: $page * $limit - $limit);

        return $this->repository->findBy($criteria, null, $limit, $offset);
    }

    /**
     * @inheritdoc
     * @return Game
     */
    public function get($id)
    {
        return $this->repository->find($id);
    }

    /**
     * @inheritdoc
     * @return Game
     */
    public function getBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * @inheritdoc
     * @return Game
     */
    public function create(array $parameters = null)
    {
        $entity = new $this->entityClass;

        $this->processForm($entity, $parameters);
        $this->dispatcher->dispatch(AdapterEvents::GAME_CREATE, new GameAdapterEvent($entity));

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function update(EntityInterface $entity, array $parameters = null)
    {
        $this->processForm($entity, $parameters);
        $this->dispatcher->dispatch(AdapterEvents::GAME_UPDATE, new GameAdapterEvent($entity));
    }

    /**
     * @inheritdoc
     */
    public function delete(EntityInterface $entity)
    {
        $this->objectManager->remove($entity);
        $this->objectManager->flush();
        $this->dispatcher->dispatch(AdapterEvents::GAME_DELETE, new GameAdapterEvent($entity));
    }

    /**
     * @param Game $game
     * @param UploadedFile $file
     * @param string $mapName
     * @return File
     */
    public function uploadMapImage(Game $game, UploadedFile $file, $mapName)
    {
        return $file->move($this->imageDir . '/maps/' . $game->getFolder(), $mapName . '.' . $file->guessExtension());
    }

    /**
     * @param EntityInterface $entity
     * @param array $parameters
     * @throws InvalidFormException
     */
    protected function processForm(EntityInterface $entity, array $parameters = null)
    {
        /** @var Game $entity */
        $this->submitForm(GameForm::class, $entity, $parameters);

        $icon = $entity->getIcon();
        $fileName = $entity->getFolder() . '.' . $icon->guessExtension();

        $icon->move($this->imageDir . '/games', $fileName);
        $entity->setIcon($fileName);

        $this->objectManager->persist($entity);
        $this->objectManager->flush();
    }
}
