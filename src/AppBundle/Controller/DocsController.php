<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DocsController extends Controller
{

    /**
     * @Route("/help/docs", name="docs_intro")
     */
    public function docsIntro(Request $request)
    {
        return $this->redirectToRoute('features_items');
    }
}