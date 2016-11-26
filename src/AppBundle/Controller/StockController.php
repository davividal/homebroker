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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class StockController extends Controller
{
    /**
     * @Route("/dashboard/stock-options", name="stock-options")
     */
    public function indexAction()
    {
        $stockOptions = $this->get('doctrine')->getRepository('AppBundle:StockOption')->findAll();

        return $this->render(
            'home-broker/stock-list.html.twig',
            [
                'stockOptions' => $stockOptions
            ]
        );
    }

    /**
     * @Route("/dashboard/buy-stocks", name="buy-stocks")
     */
    public function buyAction(Request $request)
    {
        /** @var Trade $trade */
        $trade = new Trade();

        $form = $this->createFormBuilder($trade)
            ->add(
                'stockOption',
                EntityType::class,
                [
                    'class' => 'AppBundle:StockOption',
                    'label' => 'Empresa'
                ]
            )
            ->add('quantity', IntegerType::class, ['label' => 'Quantidade'])
            ->add('save', SubmitType::class, ['label' => 'Comprar'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $trade = $form->getData();
            $trade->setUser($this->getUser());

            $trade->setPaid($trade->getStockOption()->getValue());


            $newBalance = $this->getUser()->getBalance() - $transactionValue;

            $this->getUser()->setBalance($newBalance);

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
             $em = $this->getDoctrine()->getManager();
             $em->persist($trade);
             $em->flush();

            return $this->redirectToRoute('user-dashboard');
        }

        return $this->render(
            'home-broker/buy-stocks.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }
}