<?php

namespace SourceBans\CoreBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SourceBans\CoreBundle\Adapter\BanAdapter;
use SourceBans\CoreBundle\Entity\Ban;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\ImportForm;
use SourceBans\CoreBundle\Util\Ban\ImportFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * BansController
 *
 * @Route(service="sourcebans.core.controller.admin.bans")
 */
class BansController
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
     * @var BanAdapter
     */
    private $adapter;

    /**
     * @var ImportFactory
     */
    private $importer;

    /**
     * @param RouterInterface      $router
     * @param FormFactoryInterface $formFactory
     * @param BanAdapter           $adapter
     * @param ImportFactory        $importer
     */
    public function __construct(
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        BanAdapter $adapter,
        ImportFactory $importer
    ) {
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->adapter = $adapter;
        $this->importer = $importer;
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin/bans")
     * @Security("has_role('ROLE_ADD_BANS')")
     * @Template
     */
    public function indexAction(Request $request)
    {
        return [];
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin/bans/add")
     * @Security("has_role('ROLE_ADD_BANS')")
     * @Template
     */
    public function addAction(Request $request)
    {
        try {
            $ban = $this->adapter->create();

            return new RedirectResponse(
                $this->router->generate('sourcebans_core_admin_bans_edit', ['id' => $ban->getId()])
            );
        } catch (InvalidFormException $exception) {
            return [
                'form' => $exception->getForm()->createView(),
            ];
        }
    }

    /**
     * @param Request $request
     * @param Ban $ban
     * @return array|Response
     *
     * @Route("/admin/bans/edit/{id}")
     * @Security("is_granted('edit', ban)")
     * @Template
     */
    public function editAction(Request $request, Ban $ban)
    {
        try {
            $this->adapter->update($ban);

            return new RedirectResponse(
                $this->router->generate('sourcebans_core_admin_bans_edit', ['id' => $ban->getId()])
            );
        } catch (InvalidFormException $exception) {
            return [
                'form' => $exception->getForm()->createView(),
            ];
        }
    }

    /**
     * @param Ban $ban
     * @return Response
     *
     * @Method({"POST"})
     * @Route("/admin/bans/delete/{id}")
     * @Security("has_role('ROLE_DELETE_BANS')")
     */
    public function deleteAction(Ban $ban)
    {
        $this->adapter->delete($ban);

        return new RedirectResponse($this->router->generate('sourcebans_core_admin_bans_index'));
    }

    /**
     * @param Request $request
     * @param Ban $ban
     * @return array|Response
     *
     * @Method({"POST"})
     * @Route("/admin/bans/unban/{id}")
     * @Security("is_granted('unban', ban)")
     */
    public function unbanAction(Request $request, Ban $ban)
    {
        $this->adapter->unban($ban, $request->request->get('reason'));

        return new RedirectResponse($this->router->generate('sourcebans_core_admin_bans_index'));
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin/bans/import")
     * @Security("has_role('ROLE_ADD_BANS')")
     * @Template
     */
    public function importAction(Request $request)
    {
        $form = $this->formFactory->create(ImportForm::class)
            ->handleRequest($request);

        if ($form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();
            $this->importer->import($file->getRealPath(), $file->getClientOriginalName());

            return new RedirectResponse($this->router->generate('sourcebans_core_admin_bans_index'));
        }

        return ['form' => $form->createView()];
    }
}
