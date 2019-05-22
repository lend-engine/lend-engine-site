<?php

namespace AppBundle\Controller\Directory;

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
        if ($home = $request->get('locate')) {
            $zoom = 10;
        } else {
            $home = 'SA43 1QA';
            $zoom = 3;
        }

        $libraries = [];

        $libraries[] = [
            'name' => "Example Tool Library",
            'website' => "www.tool-library.com",
            'description' => "We lend tools",
            'lat' => 51.440109,
            'long' => -0.09
        ];

        $libraries[] = [
            'name' => "Example Toy Library",
            'website' => "www.toy-library.com",
            'description' => "We lend toys",
            'lat' => 52.072,
            'long' => -4.667
        ];

        $libraries[] = [
            'name' => "Example Toy Library",
            'website' => "www.toy-library.com",
            'description' => "We lend toys",
            'lat' => 42.42,
            'long' => -76.49
        ];

        return $this->render('directory/home.html.twig', [
            'map_home' => $home,
            'map_zoom' => $zoom,
            'libraries' => $libraries
        ]);
    }

}