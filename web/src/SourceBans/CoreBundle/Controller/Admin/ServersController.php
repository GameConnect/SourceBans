<?php

namespace SourceBans\CoreBundle\Controller\Admin;

use Doctrine\DBAL\Connection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SourceBans\CoreBundle\Adapter\ServerAdapter;
use SourceBans\CoreBundle\Entity\Server;
use SourceBans\CoreBundle\Entity\SettingRepository;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Util\SourceRcon;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * ServersController
 *
 * @Route(service="sourcebans.core.controller.admin.servers")
 */
class ServersController
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var SettingRepository
     */
    private $settings;

    /**
     * @var ServerAdapter
     */
    private $adapter;

    /**
     * @var string
     */
    private $patternStatus;

    /**
     * @param RouterInterface     $router
     * @param TranslatorInterface $translator
     * @param Connection          $connection
     * @param SettingRepository   $settings
     * @param ServerAdapter       $adapter
     * @param string              $patternStatus
     */
    public function __construct(
        RouterInterface $router,
        TranslatorInterface $translator,
        Connection $connection,
        SettingRepository $settings,
        ServerAdapter $adapter,
        $patternStatus
    ) {
        $this->router = $router;
        $this->translator = $translator;
        $this->connection = $connection;
        $this->settings = $settings;
        $this->adapter = $adapter;
        $this->patternStatus = $patternStatus;
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin/servers")
     * @Security("has_role('ROLE_VIEW_SERVERS')")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $servers = $this->adapter->all(
            $this->settings->get('items_per_page'),
            $request->query->getInt('page', 1),
            $request->query->get('sort'),
            $request->query->get('order')
        );

        return ['servers' => $servers];
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin/servers/add")
     * @Security("has_role('ROLE_ADD_SERVERS')")
     * @Template
     */
    public function addAction(Request $request)
    {
        try {
            $server = $this->adapter->create();

            return new RedirectResponse(
                $this->router->generate('sourcebans_core_admin_servers_edit', ['id' => $server->getId()])
            );
        } catch (InvalidFormException $exception) {
            return [
                'form' => $exception->getForm()->createView(),
            ];
        }
    }

    /**
     * @param Request $request
     * @param Server $server
     * @return array|Response
     *
     * @Route("/admin/servers/edit/{id}")
     * @Security("has_role('ROLE_EDIT_SERVERS')")
     * @Template
     */
    public function editAction(Request $request, Server $server)
    {
        try {
            $this->adapter->update($server);

            return new RedirectResponse(
                $this->router->generate('sourcebans_core_admin_servers_edit', ['id' => $server->getId()])
            );
        } catch (InvalidFormException $exception) {
            return [
                'form' => $exception->getForm()->createView(),
            ];
        }
    }

    /**
     * @param Server $server
     * @return Response
     *
     * @Method({"POST"})
     * @Route("/admin/servers/delete/{id}")
     * @Security("has_role('ROLE_DELETE_SERVERS')")
     */
    public function deleteAction(Server $server)
    {
        $this->adapter->delete($server);

        return new RedirectResponse($this->router->generate('sourcebans_core_admin_servers_index'));
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin/servers/config")
     * @Security("has_role('ROLE_VIEW_SERVERS')")
     * @Template
     */
    public function configAction(Request $request)
    {
        $host = $this->connection->getHost();
        if (in_array($host, ['localhost', '127.0.0.1', '::1'])) {
            $host = $request->headers->get('host');
        }

        return [
            'db_host' => $host,
            'db_port' => $this->connection->getPort(),
            'db_user' => $this->connection->getUsername(),
            'db_pass' => $this->connection->getPassword(),
            'db_name' => $this->connection->getDatabase(),
        ];
    }

    /**
     * @param Request $request
     * @param Server $server
     * @return array|Response
     *
     * @Route("/admin/servers/rcon/{id}")
     * @Security("has_role('ROLE_VIEW_SERVERS')")
     * @Template
     */
    public function rconAction(Request $request, Server $server)
    {
        return ['server' => $server];
    }

    /**
     * @param Server $server
     * @param string $name
     * @return array|Response
     *
     * @Route("/admin/servers/kick/{id}/{name}")
     * @Security("has_role('ROLE_ADD_BANS')")
     */
    public function kickAction(Server $server, $name)
    {
        $response = $this->rcon($server, 'kick "' . addslashes($name) . '"');

        return new JsonResponse($response);
    }

    /**
     * @param Server $server
     * @param string $name
     * @return array|Response
     *
     * @Route("/admin/servers/getProfile/{id}/{name}")
     * @Security("has_role('ROLE_ADD_BANS')")
     */
    public function getProfileAction(Server $server, $name)
    {
        $response = $this->rcon($server, 'status');
        if (isset($response['error'])) {
            return new JsonResponse($response);
        }

        preg_match_all($this->patternStatus, $response['result'], $players);
        for ($i = 0; $i < count($players[0]); $i++) {
            if ($players[2][$i] == $name) {
                $steam = new \SteamID($players[3][$i]);

                return new JsonResponse([
                    'id' => $steam->RenderSteam3(),
                ]);
            }
        }

        return new JsonResponse([
            'error' => [
                'code'    => 'ERR_INVALID_NAME',
                'message' => $this->translator->trans('controllers.servers.getProfile.err_invalid_name', ['{name}' => $name]),
            ],
        ]);
    }

    /**
     * @param Server $server
     * @param string $command
     * @return array
     */
    private function rcon(Server $server, $command)
    {
        $rcon   = new SourceRcon($server->getHost(), $server->getPort(), $server->getRcon());
        $result = [
            'id'   => $server->getId(),
            'host' => $server->getHost(),
            'port' => $server->getPort(),
        ];

        if (!$rcon->auth()) {
            $result['error'] = [
                'code'    => 'ERR_INVALID_PASSWORD',
                'message' => 'Invalid RCON password.',
            ];
        } else {
            $result['result'] = $rcon->execute($command);
        }

        return $result;
    }
}
