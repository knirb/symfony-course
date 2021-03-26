<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\ProductRepository;

class CartController extends AbstractController
{
    /**
     * @Route("/cart")
     */
    public function details(Request $request, SessionInterface $session): Response
    {
        $cart = $session->get("cart", []);

        if ($request->isMethod("POST")) {
            unset($cart[$request->request->get('id')]);
            $session->set('cart', $cart);
        }

        $total = array_sum(array_map(function ($product) {
            return $product->getPrice();
        }, $cart));

        return $this->render('cart.html.twig', [
            "cart" => $cart,
            "total" => $total
        ]);
    }
}
