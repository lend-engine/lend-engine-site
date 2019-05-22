<?php

namespace AppBundle\Controller\Directory;

use AppBundle\Entity\Org;
use AppBundle\Form\Type\OrgType;
use Postmark\PostmarkClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class OrgController extends Controller
{

    /**
     * @Route("/directory/org/{id}", name="org", defaults={"id" = 0}, requirements={"id": "\d+"})
     */
    public function addOrgAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $key = getenv('SYMFONY__POSTMARK_API_KEY');

        if ($id) {

            if (!$org = $this->getDoctrine()->getRepository('AppBundle:Org')->find($id)) {
                $this->addFlash('error', "We couldn't find an organisation with id {$id}.");
                return $this->redirectToRoute('directory');
            }

            if ($org->getOwner() != $this->getUser()) {
                $this->addFlash('error', "You're not the owner of that organisation.");
                return $this->redirectToRoute('directory');
            }

            $mode = 'edit';
        } else {
            $org = new Org();
            $org->setOwner($this->getUser());
            $mode = 'create';
        }

        $form = $this->createForm(OrgType::class, $org);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $org->setLends(implode(',', $form->getData()->getLends()));
            try {
                $em->persist($org);
                $em->flush();
                if ($mode == 'edit') {
                    $this->addFlash('success', "Your organisation details were updated OK.");
                } else {
                    $this->addFlash('success', "Excellent - your organisation was added!");
                }
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
            return $this->redirectToRoute('fos_user_profile_show');
        }

        return $this->render('directory/add_org.html.twig', [
            'form' => $form->createView(),
            'mode' => $mode
        ]);

    }
}