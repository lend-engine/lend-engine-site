<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Account;
use Postmark\PostmarkClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ActivationController extends Controller
{

    /**
     * @Route("/activate", name="activate")
     */
    public function activateAccountAction(Request $request)
    {
        $dbSchema = $request->get('t');

        $key = getenv('SYMFONY__POSTMARK_API_KEY');

        $em = $this->getDoctrine()->getManager();

        /** @var \AppBundle\Entity\AccountRepository $repo */
        $repo = $em->getRepository('AppBundle:Account');

        /** @var $Account \AppBundle\Entity\Account */
        if (!$Account = $repo->findOneBy(['dbSchema' => $dbSchema])) {
            return $this->redirectToRoute('account_not_found');
        }

        // Activate the account and redirect to deployment
        $Account->setStatus('TRIAL');

        $trialExpiresAt = new \DateTime();
        $trialExpiresAt->modify("+30 days");
        $Account->setTrialExpiresAt($trialExpiresAt);

        $em->persist($Account);
        $em->flush();

        $stub = $Account->getStub();

        try {

            $client = new PostmarkClient($key);
            $message = $this->renderView(
                'emails/basic.html.twig',
                [
                    'message' => 'Account "'.$Account->getName().'" activated account.<br>http://'.$stub.'.lend-engine-app.com'
                ]
            );
            $client->sendEmail(
                "Lend Engine <hello@lend-engine.com>",
                "chris@lend-engine.com",
                "Lend Engine account activated : ".$Account->getName(),
                $message
            );

        } catch (\Exception $generalException) {
            $this->addFlash('error', 'Failed to send email:' . $generalException->getMessage());
        }

        $db = $this->get('database_connection');

        // Create them a database!
        $db->executeQuery('CREATE DATABASE '.$dbSchema.' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        return $this->redirect('http://'.$stub.'.lend-engine-app.com/deploy');

    }

}