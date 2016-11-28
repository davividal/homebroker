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
     * @Route("/dashboard/", name="user-dashboard")
     */
    public function dashboardAction()
    {
        $user = $this->getUser();

        $stocks = json_decode(
            $this
                ->get('guzzle.client.homebroker_api')
                ->get('/stock-option/10')
                ->getBody()
        );

        return $this->render(
            'home-broker/dashboard.html.twig',
            [
                'user' => $user,
                'stocks' => $stocks
            ]
        );
    }
}