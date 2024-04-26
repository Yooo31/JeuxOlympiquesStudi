<?php

namespace App\Controller;

use App\Repository\OffersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order', name: 'order.')]
#[IsGranted('ROLE_VERIFIED')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(SessionInterface $session, OffersRepository $offersRepository): Response
    {
        $cart = $session->get('cart', []);


        $data = [];
        $totalHt = 0;
        $totalTtc = 0;

        foreach ($cart as $id => $quantity) {
            $offer = $offersRepository->find($id);
            $data[] = [
                'offer' => $offer,
                'quantity' => $quantity,
            ];

            $totalHt += $offer->getPricing() * $quantity;
            $totalTtc = $totalHt * 1.15;
        }

        return $this->render('order/index.html.twig',
            compact('data', 'totalHt', 'totalTtc')
        );
    }
}
