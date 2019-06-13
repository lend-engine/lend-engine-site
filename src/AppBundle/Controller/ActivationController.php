<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Tenant;
use Postmark\PostmarkClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ActivationController extends Controller
{

    /**
     * @Route("/activate", name="activate")
     */
    public function activateTenantAction(Request $request)
    {
        $dbSchema = $request->get('t');

        $key = getenv('SYMFONY__POSTMARK_API_KEY');

        $em = $this->getDoctrine()->getManager();

        /** @var \AppBundle\Repository\TenantRepository $repo */
        $repo = $em->getRepository('AppBundle:Tenant');

        /** @var $tenant \AppBundle\Entity\Tenant */
        if (!$tenant = $repo->findOneBy(['dbSchema' => $dbSchema])) {
            return $this->redirectToRoute('account_not_found');
        }

        // Create them a database!
        $db = $this->get('database_connection');
        try {
            $db->executeQuery('CREATE DATABASE '.$dbSchema.' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        } catch (\Exception $generalException) {
            // We can re-deploy an existing DB without causing any trouble so just carry on
            // Assumes DB exists exception
        }

        // DB creation OK

        // Redirect to deployment
        if ($tenant->getStatus() == Tenant::STATUS_PENDING) {
            $tenant->setStatus('DEPLOYING');
            $em->persist($tenant);
            $em->flush();
        }

        try {

            $client = new PostmarkClient($key);
            $message = $this->renderView(
                'emails/basic.html.twig',
                [
                    'message' => 'Tenant "'.$tenant->getName().'" activated account : http://'.$tenant->getStub().'.lend-engine-app.com'
                ]
            );
            $client->sendEmail(
                "Lend Engine <hello@lend-engine.com>",
                "chris@lend-engine.com",
                "Lend Engine account deployment : ".$tenant->getName(),
                $message
            );

        } catch (\Exception $generalException) {
            $this->addFlash('error', 'Failed to send email:' . $generalException->getMessage());
        }

        return $this->redirect('http://'.$tenant->getStub().'.lend-engine-app.com/deploy');

    }

}