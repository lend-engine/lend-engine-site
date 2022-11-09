<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Tenant;
use AppBundle\Form\Type\SignupType;
use Postmark\PostmarkClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RegistrationController extends Controller
{

    /**
     * @Route("/signup", name="signup")
     */
    public function registerPageAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        /** @var \AppBundle\Repository\TenantRepository $repo */
        $repo = $em->getRepository('AppBundle:Tenant');

        $key = getenv('SYMFONY__POSTMARK_API_KEY');

        $tenant = new Tenant();
        $form = $this->createForm(SignupType::class, $tenant);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $subDomain = strtolower($form->get('stub')->getData());
            $subDomain = preg_replace('/[^a-z0-9\s ]+/i', "", $subDomain);

            $toEmail = $form->get('ownerEmail')->getData();
            $toName  = $form->get('ownerName')->getData();
            $org     = $form->get('name')->getData();

            // Remove any common typos from the email
            $toEmail = str_replace(" .Com", ".com", $toEmail);
            
            // Simple anti-spam
            if (!strstr($toName, ' ')) {
                $this->addFlash('error', "Please enter a real name.");
                return $this->redirectToRoute('signup');
            }

            if ($toName == $org) {
                $this->addFlash('error', "Your data was invalid. Please try again.");
                return $this->redirectToRoute('signup');
            }

            /** @var $existingTenant \AppBundle\Entity\Tenant */
            if ($existingTenant = $repo->findOneBy(['stub' => $subDomain])) {

                // Use it and re-send information
                if (!$dbSchema = $existingTenant->getDbSchema()) {
                    $this->addFlash('error', 'Error creating your account. Please try again.');
                    return $this->redirectToRoute('signup');
                }

                $status = $existingTenant->getStatus();

                if ($status == 'PENDING' || $status == 'DEPLOYING') {
                    $this->addFlash('error', "There's already an account for {$subDomain}. We've resent the activation email to {$toEmail}.");
                } else {
                    $this->addFlash('error', "That account has already been activated.");
                    return $this->redirectToRoute('signup');
                }

            } else {

                $tenant->setCreatedAt(new \DateTime());

                $dbSchema = $subDomain;
                $tenant->setDbSchema($dbSchema);
                $tenant->setServer('lend-engine-eu');
                $tenant->setOrgEmail($tenant->getOwnerEmail());

                $em->persist($tenant);

                try {
                    $em->flush();
                    $this->addFlash('success', "We've sent an email to ".$toEmail." with a link to activate your account.");
                } catch (\Exception $e) {
                    $this->addFlash('error', "There was an error creating your account.");
                    return $this->redirectToRoute('signup');
                }

            }

            $activationUrl = 'https://www.lend-engine.com/activate?t='.$dbSchema;

            try {

                $client = new PostmarkClient($key);
                $message = $this->renderView(
                    'emails/signup.html.twig',
                    [
                        'activationUrl' => $activationUrl
                    ]
                );
                $client->sendEmail(
                    "Lend Engine <hello@lend-engine.com>",
                    $toEmail,
                    "Your Lend Engine account",
                    $message
                );

                return $this->redirectToRoute('signup_success');

            } catch (\Exception $generalException) {
                $this->addFlash('error', 'Failed to send email:' . $generalException->getMessage());
            }

        }

        return $this->render('default/signup.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/signup-success", name="signup_success")
     */
    public function registerSuccessAction(Request $request)
    {
        return $this->render('signup/signup_success.html.twig', [

        ]);
    }

}
