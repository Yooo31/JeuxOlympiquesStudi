<?php

namespace App\Controller;

use App\Entity\Offers;
use App\Repository\OffersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/cart', name: 'cart.')]
#[IsGranted('ROLE_USER')]
class CartController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(SessionInterface $session, OffersRepository $offersRepository): Response
    {
        $cart = $session->get('cart', []);


        $data = [];
        $total = 0;

        foreach ($cart as $id => $quantity) {
            $offer = $offersRepository->find($id);
            $data[] = [
                'offer' => $offer,
                'quantity' => $quantity,
            ];

            $total += $offer->getPricing() * $quantity;
        }

        return $this->render('cart/index.html.twig',
            compact('data', 'total')
        );
    }

    #[Route('/add/{id}', name: 'add', requirements: ['id' => Requirement::DIGITS])]
    public function addToCart(Offers $offer, SessionInterface $session): Response
    {
        $id = $offer->getId();
        $cart = $session->get('cart', []);
        $totalCart = $session->get('totalCart', []);

        if (empty($cart[$id])) {
            $cart[$id] = 1;
        } else {
            $cart[$id]++;
        }

        if (empty($totalCart['count'])) {
            $totalCart['count'] = 1;
        } else {
            $totalCart['count']++;
        }

        $session->set('cart', $cart);
        $session->set('totalCart', $totalCart);

        $this->addFlash('success', 'Produit ajouté au panier');
        return $this->redirectToRoute('cart.index');
    }

    #[Route('/remove/{id}', name: 'remove', requirements: ['id' => Requirement::DIGITS])]
    public function removeOneToCart(Offers $offer, SessionInterface $session): Response
    {
        $id = $offer->getId();
        $cart = $session->get('cart', []);
        $totalCart = $session->get('totalCart', []);

        if (!empty($cart[$id])) {
            if ($cart[$id] > 1) {
                $cart[$id]--;
            } else {
                unset($cart[$id]);
            }
        }

        if (!empty($totalCart['count'])) {
            if ($totalCart['count'] > 1) {
                $totalCart['count']--;
            } else {
                unset($totalCart['count']);
            }
        }

        $session->set('cart', $cart);
        $session->set('totalCart', $totalCart);

        $this->addFlash('success', 'Un produit a été retiré du panier');
        return $this->redirectToRoute('cart.index');
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => Requirement::DIGITS])]
    public function deleteCart(Offers $offer, SessionInterface $session): Response
    {
        $id = $offer->getId();
        $cart = $session->get('cart', []);
        $totalCart = $session->get('totalCart', []);

        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }

        if (!empty($totalCart['count'])) {
            unset($totalCart['count']);
        }

        $session->set('cart', $cart);
        $session->set('totalCart', $totalCart);

        $this->addFlash('success', 'Le produit a été retiré du panier');
        return $this->redirectToRoute('cart.index');
    }
}
