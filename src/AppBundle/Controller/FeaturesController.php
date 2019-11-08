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
            'asset-tracking-software' => 'assets',
            'automation' => 'automation',
            'barcode-tracking' => 'barcodes',
            'equipment-tracking-software' => 'items',
            'event-booking-software' => 'events',
            'payment-tracking-for-lending-libraries' => 'payments',
            'loan-management-software' => 'loans',
            'sites' => 'sites',
            'maintenance-and-servicing' => 'maintenance',
            'costs' => 'costs',
            'member-management-software' => 'members',
            'self-serve-lending-library-platform' => 'member_site',
            'equipment-rental-software' => 'rentals',
            'languages' => 'languages',
        ];
        return $this->render('features/'.$slugMap[$slug].'.html.twig', []);
    }
}
