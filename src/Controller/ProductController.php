<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\ProductRepository;

class ProductController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function homepage(ProductRepository $repo): Response
    {
        $bikes = $repo->findBy([]); //<-find by e.g. ['name' => 'ProductName1']
        return $this->render('homepage.html.twig', [
            "bikes" => $bikes
        ]);
    }
    /**
     * @Route("/products/{id}")
     */
    public function details($id, Request $request, ProductRepository $repo, SessionInterface $session): Response
    {
        $bike = $repo->find($id);
        if ($bike === null) {
            throw $this->createNotFoundException('The product does not exist');
        }

        // Add to cart logic
        $cart = $session->get("cart", []); //gets cart or empty array
        if ($request->isMethod("POST")) {
            $cart[$bike->getId()] = $bike;
            $session->set('cart', $cart);
        }

        $isInCart = array_key_exists($bike->getId(), $cart);

        return $this->render("details.html.twig", [
            "bike" => $bike,
            "inCart" => $isInCart
        ]);
    }
}
