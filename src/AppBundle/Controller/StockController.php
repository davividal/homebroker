<?php
/**
 * Created by PhpStorm.
 * User: davi
 * Date: 26.11.16
 * Time: 16:12
 */

namespace AppBundle\Controller;

use GuzzleHttp\Exception\ClientException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
                $response = $client->get(
                    sprintf(
                        '/stock-option/%d/%d/buy?quantity=%d',
                        $this->getUser()->getId(),
                        $trade['stockOption']->id,
                        $trade['quantity']
                    )
                );

                $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('confirm-buy'))
                    ->add('stockOption', HiddenType::class, ['data' => $trade['stockOption']->id])
                    ->add('quantity', HiddenType::class, ['data' => $trade['quantity']])
                    ->add('save', SubmitType::class, ['label' => 'Confirmar compra'])
                    ->getForm();

                return $this->render(
                    'home-broker/confirm-buy.html.twig',
                    [
                        'form' => $form->createView(),
                        'details' => json_decode($response->getBody()),
                    ]
                );
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

    /**
     * @Route("/dashboard/buy-stocks/confirm", name="confirm-buy")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function confirmBuyAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('confirm-buy'))
            ->add('stockOption', HiddenType::class)
            ->add('quantity', HiddenType::class)
            ->add('save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);


        $client = $this->get('guzzle.client.homebroker_api');

        if ($form->isSubmitted()) {
            $trade = $form->getData();

            try {
                $response = $client->post(
                    sprintf(
                        '/stock-option/%d/%d/buy?quantity=%d',
                        $this->getUser()->getId(),
                        $trade['stockOption'],
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
    }

    /**
     * @Route("/dashboard/sell-stocks/{stock_id}", name="sell-stocks")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sellAction(Request $request, $stock_id)
    {
        $url = sprintf(
            '/stock-option/%d/%d/sell',
            $this->getUser()->getId(),
            $stock_id
        );

        $transaction = json_decode(
            $this
                ->get('guzzle.client.homebroker_api')
                ->get($url)
                ->getBody()
        );

        $form = $this->createFormBuilder()
            ->add('quantity', IntegerType::class, ['label' => 'Quantidade para vender'])
            ->add('save', SubmitType::class, ['label' => 'Vender'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (0 === $form->get('quantity')->getData()) {
                $form->get('quantity')->addError(new FormError('Quantidade não disponível para venda!'));
            }

            if ($form->get('quantity')->getData() > $transaction->trade->quantity) {
                $form->get('quantity')->addError(new FormError('Quantidade não disponível para venda!'));
            }

            if ($form->isValid()) {
                return $this->redirectToRoute(
                    'confirm-sell-stocks',
                    [
                        'stock_id' => $stock_id,
                        'quantity' => $form->get('quantity')->getData()
                    ]
                );
            }
        }

        return $this->render(
            'home-broker/sell-stocks.html.twig',
            [
                'form' => $form->createView(),
                'transaction' => $transaction,
            ]
        );
    }

    /**
     * @Route("/dashboard/confirm-sell-stocks/{stock_id}", name="confirm-sell-stocks")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmSellAction(Request $request, $stock_id)
    {
        $quantity = $request->get('quantity');

        $url = sprintf(
            '/stock-option/%d/%d/sell?quantity=%d',
            $this->getUser()->getId(),
            $stock_id,
            $quantity
        );

        $transaction = json_decode(
            $this
                ->get('guzzle.client.homebroker_api')
                ->get($url)
                ->getBody()
        );

        $form = $this->createFormBuilder()
            ->add('quantity', HiddenType::class, ['data' => $quantity])
            ->add('save', SubmitType::class, ['label' => 'Vender'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this
                    ->get('guzzle.client.homebroker_api')
                    ->post($url);

                $this->addFlash('notice', 'Ações vendidas com sucesso!');

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
            'home-broker/confirm-sell.html.twig',
            [
                'form' => $form->createView(),
                'transaction' => $transaction,
            ]
        );
    }
}