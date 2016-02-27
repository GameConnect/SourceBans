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
     * @var SettingRepository
     */
    private $settings;

    /**
     * @var GameAdapter
     */
    private $adapter;

    /**
     * @param RouterInterface   $router
     * @param SettingRepository $settings
     * @param GameAdapter       $adapter
     */
    public function __construct(RouterInterface $router, SettingRepository $settings, GameAdapter $adapter)
    {
        $this->router = $router;
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
            $game = $this->adapter->create($request->request->all());

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
            $this->adapter->update($game, $request->request->all());

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
}
