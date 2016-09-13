<?php

namespace SourceBans\CoreBundle\Controller\Admin;

use Rb\Specification\Doctrine\Logic;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SourceBans\CoreBundle\Adapter\ReportAdapter;
use SourceBans\CoreBundle\Entity\Report;
use SourceBans\CoreBundle\Entity\SettingRepository;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Specification;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * ReportsController
 *
 * @Route(service="sourcebans.core.controller.admin.reports")
 */
class ReportsController
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
     * @var ReportAdapter
     */
    private $adapter;

    /**
     * @param RouterInterface   $router
     * @param SettingRepository $settings
     * @param ReportAdapter     $adapter
     */
    public function __construct(RouterInterface $router, SettingRepository $settings, ReportAdapter $adapter)
    {
        $this->router = $router;
        $this->settings = $settings;
        $this->adapter = $adapter;
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/admin/reports")
     * @Security("has_role('ROLE_REPORTS')")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $specification = new Specification\IsArchived;
        if ($request->query->get('type') != 'archive') {
            $specification = new Logic\Not($specification);
        }

        $reports = $this->adapter->all(
            $this->settings->get('items_per_page'),
            $request->query->getInt('page', 1),
            $request->query->get('sort'),
            $request->query->get('order'),
            [$specification]
        );

        return ['reports' => $reports];
    }

    /**
     * @param Request $request
     * @param Report $report
     * @return array|Response
     *
     * @Route("/admin/reports/edit/{id}")
     * @Security("has_role('ROLE_REPORTS')")
     * @Template
     */
    public function editAction(Request $request, Report $report)
    {
        try {
            $this->adapter->update($report, $request);

            return new RedirectResponse(
                $this->router->generate('sourcebans_core_admin_reports_edit', ['id' => $report->getId()])
            );
        } catch (InvalidFormException $exception) {
            return [
                'form' => $exception->getForm()->createView(),
            ];
        }
    }

    /**
     * @param Report $report
     * @return Response
     *
     * @Method({"POST"})
     * @Route("/admin/reports/delete/{id}")
     * @Security("has_role('ROLE_REPORTS')")
     */
    public function deleteAction(Report $report)
    {
        $this->adapter->delete($report);

        return new RedirectResponse($this->router->generate('sourcebans_core_admin_reports_index'));
    }

    /**
     * @param Report $report
     * @return Response
     *
     * @Method({"POST"})
     * @Route("/admin/reports/archive/{id}")
     * @Security("has_role('ROLE_REPORTS')")
     */
    public function archiveAction(Report $report)
    {
        $this->adapter->archive($report);

        return new RedirectResponse($this->router->generate('sourcebans_core_admin_reports_index'));
    }
}
