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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

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
     * @param RouterInterface   $router
     * @param Connection        $connection
     * @param SettingRepository $settings
     * @param ServerAdapter     $adapter
     */
    public function __construct(
        RouterInterface $router,
        Connection $connection,
        SettingRepository $settings,
        ServerAdapter $adapter
    ) {
        $this->router = $router;
        $this->connection = $connection;
        $this->settings = $settings;
        $this->adapter = $adapter;
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
}
