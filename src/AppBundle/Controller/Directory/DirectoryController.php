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

        /** @var \AppBundle\Repository\OrgSiteRepository $repo */
        $repo = $this->getDoctrine()->getRepository('AppBundle:OrgSite');

        /** @var \AppBundle\Repository\OrgRepository $orgRepo */
        $orgRepo = $this->getDoctrine()->getRepository('AppBundle:Org');
        $allTags = $orgRepo->getTags();

        $options = [
            'tags' => $orgRepo->getTags(true)
        ];
        $form = $this->createForm(DirectorySearchType::class, null, $options);

        // Preset form values
        if ($lat = $session->get('lat')) {
            $form->get('latitude')->setData($lat);
        }
        if ($long = $session->get('long')) {
            $form->get('longitude')->setData($long);
        }


        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//
//        }

        $filters = [];
        if ($string = $form->get('search')->getData()) {
            $filters['search'] = $string;
        }
        if ($tags = $form->get('tags')->getData()) {
            $filters['tags'] = $tags;
        }
        // Get sites for the map
        $sites = $repo->search($filters);

        // Use the long and lat from the form else default to London
        if ($lat = $form->get('latitude')->getData()) {
            $session->set('lat', $lat);
        } else {
            $lat = 51.5285578;
        }
        if ($long  = $form->get('longitude')->getData()) {
            $session->set('long', $long);
        } else {
            $long = -0.2420229;
        }

        if (!$maxDistance = $form->get('distance')->getData()) {
            $maxDistance = 500;
        }

        /** @var \AppBundle\Entity\OrgSite $site */
        $n=0;
        foreach ($sites AS $site) {
            $tags = explode(',', $site->getOrg()->getLends());
            foreach ($tags AS $t) {
                if (isset($allTags[$t])) {
                    $tag = [
                        'code' => $t,
                        'name' => $allTags[$t]
                    ];
                    $site->addTag($tag);
                }
            }

            if ($lat && $long) {
                $distance = round($this->getDistance($lat, $long, $site->getLatitude(), $site->getLongitude()), 1);
                if ($distance > $maxDistance) {
                    unset($sites[$n]);
                } else {
                    $site->setDistance($distance);
                }
            }

            $website = str_replace('http://', '', $site->getOrg()->getWebsite());
            $website = str_replace('https://', '', $website);

            $site->getOrg()->setWebsite($website);
            $n++;
        }

//        $libraries = [];
//
//        $libraries[] = [
//            'name' => "Example Tool Library",
//            'website' => "www.tool-library.com",
//            'description' => "We lend tools",
//            'lat' => 51.440109,
//            'long' => -0.09
//        ];
//
//        $libraries[] = [
//            'name' => "Example Toy Library",
//            'website' => "www.toy-library.com",
//            'description' => "We lend toys",
//            'lat' => 52.072,
//            'long' => -4.667
//        ];
//
//        $libraries[] = [
//            'name' => "Example Toy Library",
//            'website' => "www.toy-library.com",
//            'description' => "We lend toys",
//            'lat' => 42.42,
//            'long' => -76.49
//        ];

        return $this->render('directory/home.html.twig', [
            'lat'  => $lat,
            'long' => $long,
            'sites' => $sites,
            'tags' => $allTags,
            'form' => $form->createView()
        ]);
    }

    /**
     * @param $lat1
     * @param $lon1
     * @param $lat2
     * @param $lon2
     * @param string $unit
     * @return float|int
     */
    private function getDistance($lat1, $lon1, $lat2, $lon2, $unit = "M") {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        }
        else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "K") {
                return ($miles * 1.609344);
            } else if ($unit == "N") {
                return ($miles * 0.8684);
            } else {
                return $miles;
            }
        }
    }

}