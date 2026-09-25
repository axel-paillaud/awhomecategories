<?php
/**
 * @author    Axelweb <contact@axelweb.fr>
 * @copyright 2026 Axelweb
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Axelweb
 */
declare(strict_types=1);

namespace Axelweb\AwHomeCategories\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminConfigurationController extends FrameworkBundleAdminController
{
    public function index(Request $request): Response
    {
        $generalFormDataHandler = $this->get('axelweb.awhomecategories.form.general_form_data_handler');

        $generalForm = $generalFormDataHandler->getForm();
        $generalForm->handleRequest($request);

        if ($generalForm->isSubmitted() && $generalForm->isValid()) {
            $errors = $generalFormDataHandler->save($generalForm->getData());

            if (empty($errors)) {
                $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));

                return $this->redirectToRoute('awhomecategories_form_configuration');
            }

            $this->flashErrors($errors);
        }

        return $this->render('@Modules/awhomecategories/views/templates/admin/form.html.twig', [
            'generalForm' => $generalForm->createView(),
        ]);
    }
}
