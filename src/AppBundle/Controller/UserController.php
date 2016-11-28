<?php
/**
 * Created by PhpStorm.
 * User: davi
 * Date: 26.11.16
 * Time: 17:48
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;


class UserController extends Controller
{
    /**
     * @Route("/dashboard", name="user-dashboard")
     */
    public function dashboardAction()
    {
        $user = $this->getUser();

        $stocks = json_decode(
            $this
                ->get('guzzle.client.homebroker_api')
                ->get('/stock-option/'.$user->getId())
                ->getBody()
        );

        return $this->render(
            'home-broker/dashboard.html.twig',
            [
                'user' => $user,
                'stocks' => $stocks,
            ]
        );
    }

    /**
     * @Route("/login", name="login")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'home-broker/login.html.twig',
            [
                'last_username' => $lastUsername,
                'error' => $error
            ]
        );
    }
}