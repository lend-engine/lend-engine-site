<?php

namespace AppBundle\Controller\Directory;

use AppBundle\Form\Type\DirectorySearchType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DirectoryController extends Controller
{
    /**
     * @Route("/directory", name="directory")
     */
    public function directoryShowAction(Request $request)
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $this->get('session');

        /** @var \AppBundle\Repository\OrgRepository $orgRepo */
        $orgRepo = $this->getDoctrine()->getRepository('AppBundle:Org');
        $allTags = $orgRepo->getTags();

        return $this->render('directory/home.html.twig', [
            'tags' => $allTags
        ]);
    }
}