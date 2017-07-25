<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DirectoryController extends Controller
{
    /**
     * @Route("/libraries", name="libraries")
     */
    public function directoryShowAction(Request $request)
    {
        if ($home = $request->get('locate')) {
            $zoom = 10;
        } else {
            $home = 'SA43 1QA';
            $zoom = 6;
        }
        return $this->render('libraries/home.html.twig', [
            'map_home' => $home,
            'map_zoom' => $zoom
        ]);
    }
}