<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

use App\Entity\Order;
use App\Repository\ProductRepository;

class CheckoutController extends AbstractController
{
    /**
     * @Route("/checkout")
     */
    public function checkout(ProductRepository $repo, Request $request, SessionInterface $session, MailerInterface $mailer): Response
    {
        $cart = $session->get("cart", []);

        $total = array_sum(array_map(function ($product) {
            return $product->getPrice();
        }, $cart));

        $order = new Order;

        $form = $this->createFormBuilder($order)
            ->add("name", TextType::class)
            ->add("email", TextType::class)
            ->add("address", TextareaType::class)
            ->add("save", SubmitType::class, ["label" => "Confirm order"])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order = $form->getData();
            foreach ($cart as $product) {
                //We don't want to add our product directly to the order or else Doctrine will think it's a new order
                //Instead we use find() to get it from the database
                $order->getProducts()->add($repo->find($product->getId()));
            }
            $entityManager = $this->getDoctrine()->getManager(); //gets the entitymanager used to save our order to the db
            $entityManager->persist($order); //Saves our order to the database 
            $entityManager->flush(); //tells Doctrine to execute all the sql we have told it to do.

            $this->sendEmailConfirmation($order, $mailer);

            $session->set("cart", []);

            return $this->render("confirmation.html.twig");
        }
        return $this->render('checkout.html.twig', [
            "total" => $total,
            "form" => $form->createView()
        ]);
    }

    private function sendEmailConfirmation(Order $order, MailerInterface $mailer)
    {
        $email = (new TemplatedEmail)
            ->from("viktor@example.se")
            ->to(new Address($order->getEmail(), $order->getName()))
            ->subject("Order Confirmation")
            ->htmlTemplate("emails/order.html.twig")
            ->context(["order" => $order]);

        $mailer->send($email);
    }
}
