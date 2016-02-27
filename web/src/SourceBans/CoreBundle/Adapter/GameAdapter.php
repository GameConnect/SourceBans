<?php

namespace SourceBans\CoreBundle\Adapter;

use Pagerfanta\Pagerfanta;
use SourceBans\CoreBundle\Entity\EntityInterface;
use SourceBans\CoreBundle\Entity\Game;
use SourceBans\CoreBundle\Event\AdapterEvents;
use SourceBans\CoreBundle\Event\GameAdapterEvent;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\GameForm;

/**
 * GameAdapter
 */
class GameAdapter extends AbstractAdapter
{
    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function all($limit = 25, $page = 1, $sort = null, $order = null, array $options = [])
    {
        $query = $this->repository->createQueryBuilder('game')
            ->orderBy(sprintf('game.%s', $sort ?: 'name'), $order)
            ->getQuery();

        $pager = static::queryToPager($query);

        return $pager->setCurrentPage($page)->setMaxPerPage($limit);
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
    public function create(array $parameters)
    {
        $entity = new $this->entityClass;

        $this->processForm($entity, $parameters);
        $this->dispatcher->dispatch(AdapterEvents::GAME_CREATE, new GameAdapterEvent($entity));

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function update(EntityInterface $entity, array $parameters)
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
     * @param EntityInterface $entity
     * @param array $parameters
     * @throws InvalidFormException
     */
    protected function processForm(EntityInterface $entity, array $parameters)
    {
        $this->submitForm(GameForm::class, $entity, $parameters);

        $this->objectManager->persist($entity);
        $this->objectManager->flush();
    }

    protected function uploadIcon()
    {
        $uploadDir = $this->container->getParameter('kernel.root_dir') . '/../web/images/games';
    }

    protected function uploadMapImage(Game $game)
    {
        $uploadDir = $this->container->getParameter('kernel.root_dir') . '/../web/images/maps/' . $game->getFolder();
    }
}
