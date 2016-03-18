<?php

namespace SourceBans\CoreBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SourceBans\CoreBundle\Adapter\GameAdapter;
use SourceBans\CoreBundle\Entity\Game;
use SourceBans\CoreBundle\Entity\SettingRepository;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\MapImageForm;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * GamesController
 *
 * @Route(service="sourcebans.core.controller.admin.games")
 */
class GamesController
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var SettingRepository
     */
    private $settings;

    /**
     * @var GameAdapter
     */
    private $adapter;

    /**
     * @param RouterInterface      $router
     * @param FormFactoryInterface $formFactory
     * @param SettingRepository    $settings
     * @param GameAdapter          $adapter
     */
    public function __construct(
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        SettingRepository $settings,
        GameAdapter $adapter
    ) {
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->settings = $settings;
        $this->adapter = $adapter;
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin/games")
     * @Security("has_role('ROLE_VIEW_GAMES')")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $games = $this->adapter->all(
            $this->settings->get('items_per_page'),
            $request->query->getInt('page', 1),
            $request->query->get('sort'),
            $request->query->get('order')
        );

        return ['games' => $games];
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin/games/add")
     * @Security("has_role('ROLE_ADD_GAMES')")
     * @Template
     */
    public function addAction(Request $request)
    {
        try {
            $game = $this->adapter->create($request);

            return new RedirectResponse(
                $this->router->generate('sourcebans_core_admin_games_edit', ['id' => $game->getId()])
            );
        } catch (InvalidFormException $exception) {
            return [
                'form' => $exception->getForm()->createView(),
            ];
        }
    }

    /**
     * @param Request $request
     * @param Game $game
     * @return array|Response
     *
     * @Route("/admin/games/edit/{id}")
     * @Security("has_role('ROLE_EDIT_GAMES')")
     * @Template
     */
    public function editAction(Request $request, Game $game)
    {
        try {
            $this->adapter->update($game, $request);

            return new RedirectResponse(
                $this->router->generate('sourcebans_core_admin_games_edit', ['id' => $game->getId()])
            );
        } catch (InvalidFormException $exception) {
            return [
                'form' => $exception->getForm()->createView(),
            ];
        }
    }

    /**
     * @param Game $game
     * @return Response
     *
     * @Method({"POST"})
     * @Route("/admin/games/delete/{id}")
     * @Security("has_role('ROLE_DELETE_GAMES')")
     */
    public function deleteAction(Game $game)
    {
        $this->adapter->delete($game);

        return new RedirectResponse($this->router->generate('sourcebans_core_admin_games_index'));
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin/games/mapImage")
     * @Security("has_role('ROLE_ADD_GAMES')")
     * @Template
     */
    public function mapImageAction(Request $request)
    {
        $form = $this->formFactory->create(MapImageForm::class)
            ->handleRequest($request);

        if ($form->isValid()) {
            $this->adapter->uploadMapImage(
                $form->get('game')->getData(),
                $form->get('file')->getData(),
                $form->get('mapName')->getData()
            );

            return new RedirectResponse($this->router->generate('sourcebans_core_admin_bans_index'));
        }

        return ['form' => $form->createView()];
    }
}
