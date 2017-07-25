<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Account;
use Postmark\PostmarkClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends Controller
{

    /**
     * @Route("/contact", name="page_contact")
     */
    public function contactForm(Request $request)
    {

        $key = getenv('SYMFONY__POSTMARK_API_KEY');

        $form = $this->createForm("AppBundle\Form\Type\ContactType");

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $subject    = $form->get('subject')->getData();
            $fromEmail  = $form->get('email')->getData();
            $message    = $form->get('message')->getData();

            try {

                $client = new PostmarkClient($key);
                $message = $this->renderView(
                    'emails/contact.html.twig',
                    [
                        'message' => $message,
                        'from'    => $fromEmail
                    ]
                );
                $client->sendEmail(
                    "hello@lend-engine.com",
                    "chris@lend-engine.com",
                    $subject,
                    $message,
                    null,
                    null,
                    true,
                    $fromEmail
                );

                $this->addFlash('success', "Thanks!");

                return $this->redirectToRoute('page_contact');

            } catch (\Exception $generalException) {

                $this->addFlash('error', 'Failed to send email:' . $generalException->getMessage());

            }

        }

        return $this->render('default/contact.html.twig', [
            'form' => $form->createView()
        ]);

    }
}