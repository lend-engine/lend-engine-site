<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class FeaturesController extends Controller
{

    /**
     * @Route("/tour", name="page_tour_old")
     */
    public function tourAction(Request $request)
    {
        return $this->redirectToRoute('features_items');
    }

    /**
     * @Route("/features/{slug}", name="features")
     */
    public function featureHandler($slug, Request $request)
    {
        $slugMap = [
            'asset_management' => 'assets',
            'automation' => 'automation',
            'barcode_tracking' => 'barcodes',
            'items' => 'items',
            'payments' => 'payments',
            'loans' => 'loans',
            'sites' => 'sites',
            'costs' => 'costs',
            'members' => 'members',
            'member_site' => 'member_site',
            'rentals' => 'rentals',
            'languages' => 'languages',
        ];
        return $this->render('features/'.$slugMap[$slug].'.html.twig', []);
    }
}
