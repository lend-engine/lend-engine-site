<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class LoginController extends Controller
{
    /**
     * @Route("/go-to-login", name="login")
     */
    public function loginShowAction(Request $request)
    {
        return $this->render('default/login.html.twig', [

        ]);
    }
}