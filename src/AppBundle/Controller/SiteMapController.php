<?php
namespace AppBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SiteMapController extends Controller
{
    /**
     * @Route("/sitemap.xml", name="sitemap", defaults={"_format"="xml"})
     */
    public function showAction(Request $request) {
        $urls = array();
        $hostname = $request->getSchemeAndHttpHost();

        // add static urls
        $urls[] = array('loc' => $this->generateUrl('homepage'));
        $urls[] = array('loc' => $this->generateUrl('page_contact'));
        $urls[] = array('loc' => $this->generateUrl('page_pricing'));
        $urls[] = array('loc' => $this->generateUrl('privacy'));
        $urls[] = array('loc' => $this->generateUrl('mibase'));

        // Industries
        $urls[] = array('loc' => $this->generateUrl('toy_libraries'));
        $urls[] = array('loc' => $this->generateUrl('tool_libraries'));
        $urls[] = array('loc' => $this->generateUrl('lot'));
        $urls[] = array('loc' => $this->generateUrl('sports'));

        // Features
        $slugMap = [
            'asset-tracking-software' => 'assets',
            'automation' => 'automation',
            'barcode-tracking' => 'barcodes',
            'equipment-tracking-software' => 'items',
            'payment-tracking-for-lending-libraries' => 'payments',
            'loan-management-software' => 'loans',
            'sites' => 'sites',
            'costs' => 'costs',
            'member-management-software' => 'members',
            'self-serve-lending-library-platform' => 'member_site',
            'equipment-rental-software' => 'rentals',
            'languages' => 'languages',
        ];

        foreach ($slugMap AS $k => $v) {
            $urls[] = array('loc' => $this->generateUrl('features', ['slug' => $k]));
        }

        // return response in XML format
        $response = new Response(
            $this->renderView('sitemap.html.twig', array( 'urls' => $urls,
                'hostname' => $hostname)),
            200
        );
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

}