<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Repository\OffersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

            $formData[] = [
                'offer' => $offer,
                'quantity' => $quantity,
            ];

            $totalHt += $offer->getPricing() * $quantity;
            $totalTtc = $totalHt * 1.15;
        }

        // dd($data, $totalHt, $totalTtc);

        return $this->render('order/index.html.twig',
            compact('data', 'totalHt', 'totalTtc')
        );
    }

    #[Route('/pay', name: 'pay')]
    public function pay(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->request->get('data'), true);
        $userId = $request->request->get('userId');

        dd($data, $userId);

        foreach ($data as $element) {
            $offerId = $element['offer'];
            $quantity = $element['quantity'];

            // Enregistrer chaque élément en base de données
            for ($i = 0; $i < $quantity; $i++) {
                $payment = new Payment();
                $payment->setUser($userId);
                $payment->setOffer($offerId);
                $payment->setPaymentKey($this->generateRandomKey()); // Générer la clé de paiement
                
                dd($payment);
                // $entityManager->persist($payment);
            }
        }

        $entityManager->flush();

        // Redirection ou autre action après le paiement
    }

    private function generateRandomKey(): string
    {
        return bin2hex(random_bytes(16));
    }

}
