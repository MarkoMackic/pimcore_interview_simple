<?php

namespace App\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends FrontendController
{
    /**
     * @Param Request
     * @Param AuthenticationUtils
     * @Route("/login", name="login")
     */
    public function login(Request $req, AuthenticationUtils $au): Response
    {
        $error = $au->getLastAuthenticationError();

        $lastUname = $au->getLastUsername();

        return $this->render('default/login.html.twig', [
            'error' => $error,
            'last_username' => $lastUname
        ]);
    }
}