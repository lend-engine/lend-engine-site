<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
//        $this->addFlash('error', "message");
        return $this->render('default/index.html.twig', []);
    }

    /**
     * @Route("/about", name="page_about")
     */
    public function aboutAction(Request $request)
    {
        return $this->render('default/about.html.twig', []);
    }

    /**
     * @Route("/reporting", name="page_reporting")
     */
    public function reportsAction(Request $request)
    {
        return $this->render('default/reports.html.twig', []);
    }

    /**
     * @Route("/automation", name="page_automation")
     */
    public function automationAction(Request $request)
    {
        return $this->render('default/automation.html.twig', []);
    }

    /**
     * @Route("/pricing", name="page_pricing")
     */
    public function pricingAction(Request $request)
    {
//        $this->addFlash('error', "message");
        return $this->render('default/pricing.html.twig', []);
    }

    /**
     * @Route("/software-for-toy-libraries", name="toy_libraries")
     */
    public function toysAction(Request $request)
    {
        return $this->render('industries/toy_libraries.html.twig', []);
    }

    /**
     * @Route("/software-for-tool-libraries", name="tool_libraries")
     */
    public function toolsAction(Request $request)
    {
        return $this->render('industries/tool_libraries.html.twig', []);
    }

    /**
     * @Route("/software-for-lending-libraries", name="lending_libraries")
     */
    public function lendingAction(Request $request)
    {
        return $this->render('industries/lending_libraries.html.twig', []);
    }

    /**
     * @Route("/software-for-plant-and-machinery", name="plant")
     */
    public function plantAction(Request $request)
    {
        return $this->render('industries/plant.html.twig', []);
    }

    /**
     * @Route("/asset-tracking-management-software", name="assets")
     */
    public function assetsAction(Request $request)
    {
        return $this->render('industries/assets.html.twig', []);
    }

    /**
     * @Route("/software-for-lending-sports-equipment", name="sports")
     */
    public function sportsAction(Request $request)
    {
        return $this->render('industries/sports.html.twig', []);
    }

    /**
     * @Route("/software-for-lending-audio-visual-equipment", name="electronics")
     */
    public function electronicsAction(Request $request)
    {
        return $this->render('industries/electronics.html.twig', []);
    }

    /**
     * @Route("/software-for-library-of-things", name="lot")
     */
    public function lotAction(Request $request)
    {
        return $this->render('industries/lot.html.twig', []);
    }

    /**
     * @Route("/demo-site", name="demo_site")
     */
    public function demoSiteAction(Request $request)
    {
        return $this->render('default/demo_site.html.twig', []);
    }

    /**
     * @Route("/privacy-policy", name="privacy")
     */
    public function privacyPolicyAction(Request $request)
    {
        return $this->render('default/privacy.html.twig', []);
    }

    /**
     * @Route("/terms-and-conditions", name="terms")
     */
    public function termsAction(Request $request)
    {
        return $this->render('default/terms.html.twig', []);
    }


}
