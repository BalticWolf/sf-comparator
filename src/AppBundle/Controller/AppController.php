<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AppController
 * @package AppBundle\Controller
 */
class AppController extends Controller
{
    /**
     * App index action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $products = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Product')
            ->findBy([], [
                'designation' => 'ASC',
            ]);

        // Récupérer la liste des produits
        return $this->render('AppBundle:App:index.html.twig', [
            'products' => $products,
        ]);
    }
    public function productAction($productSlug)
    {
        $product = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Product')
            ->findOneBy(['id' => $productSlug]);
    }
}
