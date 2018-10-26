<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Account;
use AppBundle\Form\Type\RegistrationType;
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

        /** @var \AppBundle\Entity\AccountRepository $repo */
        $repo = $em->getRepository('AppBundle:Account');

        $key = getenv('SYMFONY__POSTMARK_API_KEY');

        $Account = new Account();
        $form = $this->createForm("AppBundle\Form\Type\RegistrationType", $Account);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $subDomain = $form->get('stub')->getData();
            $subDomain  = preg_replace('/[^a-z0-9]+/i', "", $subDomain);

            $toEmail = $form->get('ownerEmail')->getData();

            // Remove any common typos from the email
            $toEmail = str_replace(" .Com", ".com", $toEmail);

            /** @var $existingAccount \AppBundle\Entity\Account */
            if ($existingAccount = $repo->findOneBy(['stub' => $subDomain])) {

                // Use it and re-send information
                $dbSchema = $existingAccount->getDbSchema();
                $status = $existingAccount->getStatus();

                if ($status == 'PENDING' || $status == 'DEPLOYING') {
                    $this->addFlash('error', "There's already an account for {$subDomain}. We've resent the activation email to {$toEmail}.");
                } else {
                    $this->addFlash('error', "That account has already been activated.");
                    return $this->redirectToRoute('signup');
                }

            } else {

                $Account->setCreatedAt(new \DateTime());

                $dbSchema = $subDomain;
                $Account->setDbSchema($dbSchema);
                $Account->setServer('lend-engine-eu');

                $em->persist($Account);

                try {
                    $em->flush();
                    $this->addFlash('success', "We've sent an email to ".$toEmail." with a link to activate your account.");
                } catch (\Exception $e) {
                    $this->addFlash('error', "There was an error creating your account.");
                }

            }

            $activationUrl = 'http://www.lend-engine.com/activate?t='.$dbSchema;

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

                // This happens in the app when the account is deployed
//                $this->mailChimpSubscribe($Account);

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

    /**
     * @param Account $contact
     * @return bool
     */
//    private function mailChimpSubscribe(Account $contact)
//    {
//
//        if (!$contact->getOwnerEmail()) {
//            return true;
//        }
//
//        /** @var \Hype\MailchimpBundle\Mailchimp\Mailchimp $mailchimp */
//        $mailchimp = $this->get('hype_mailchimp');
//
//        $mergeVars = [
//            'fname' => $fname,
//            'lname' => $lname
//        ];
//
//        try {
//            $mailchimp->getList()->addMerge_vars($mergeVars)->subscribe($contact->getEmail(), 'html', $doubleOptIn, true);
//        } catch (\Hype\MailchimpBundle\Mailchimp\MailchimpAPIException $mailchimpException) {
//            $this->addFlash('error', 'Failed to subscribe to Mailchimp:' . $mailchimpException->getMessage());
//        } catch (\Exception $generalException) {
//            $this->addFlash('error', 'Failed to subscribe to Mailchimp:' . $generalException->getMessage());
//        }
//
//        return true;
//    }

}