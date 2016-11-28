<?php
/**
 * Created by PhpStorm.
 * User: davi
 * Date: 26.11.16
 * Time: 16:12
 */

namespace AppBundle\Controller;

use AppBundle\Entity\StockOption;
use AppBundle\Entity\Trade;
use GuzzleHttp\Exception\ClientException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class StockController extends Controller
{
    /**
     * @Route("/dashboard/stock-options", name="stock-options")
     */
    public function indexAction()
    {
        $stockOptions = json_decode(
            $this
                ->get('guzzle.client.homebroker_api')
                ->get('/stock-option')
                ->getBody()
        );

        return $this->render(
            'home-broker/stock-list.html.twig',
            [
                'stockOptions' => $stockOptions,
            ]
        );
    }

    /**
     * @Route("/dashboard/buy-stocks", name="buy-stocks")
     */
    public function buyAction(Request $request)
    {
        /** @var Trade $trade */
        // $trade = new Trade();

        $stockOptions = json_decode(
            $this
                ->get('guzzle.client.homebroker_api')
                ->get('/stock-option')
                ->getBody()
        );

        $form = $this->createFormBuilder()
            ->add(
                'stockOption',
                ChoiceType::class,
                [
                    'choices' => $stockOptions,
                    'choice_label' => 'company',
                    'label' => 'Empresa',
                ]
            )
            ->add('quantity', IntegerType::class, ['label' => 'Quantidade'])
            ->add('save', SubmitType::class, ['label' => 'Comprar'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trade = $form->getData();

            $client = $this->get('guzzle.client.homebroker_api');

            try {
                $response = $client->post(
                    sprintf(
                        '/stock-option/%d/%d/buy?quantity=%d',
                        $this->getUser()->getId(),
                        $trade['stockOption']->id,
                        $trade['quantity']
                    )
                );

                $this->addFlash('notice', 'Ações compradas com sucesso!');

                return $this->redirectToRoute('user-dashboard');
            } catch (ClientException $e) {
                $response = $e->getResponse();

                if ($response->getStatusCode() !== 200) {
                    $this->addFlash(
                        'error',
                        json_decode($response->getBody())->error
                    );
                }
            }
        }

        return $this->render(
            'home-broker/buy-stocks.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}