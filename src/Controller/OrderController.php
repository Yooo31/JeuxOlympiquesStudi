<?php

namespace App\Controller;

use App\Entity\Offers;
use App\Entity\Payment;
use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\OffersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
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

        return $this->render('order/index.html.twig',
            compact('data', 'totalHt', 'totalTtc')
        );
    }

    #[Route('/pay', name: 'pay')]
    public function pay(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        // $jsonData = $request->request->get('data');
        // $data = json_decode($jsonData, true);
        $userId = $request->request->get('userId');
        $generatedKeys = [
            'c87fb6138be62f5890ae05440a1bd669558fd353d461f2141685f74726c785d2',
            '336a9b3ffc4e782c024c4fc3e8dc6e0a03d18bc72bbfb46c9fe087f0d98937b9',
            '6c954dda2529a649b62992d218fe2a54547c1d201dbae896f1ab210cdc48b57b',
            '358d4ef2da29d0ae1f5896249439db7ac79812a3b636b1d1038acf4b25cecf73',
            '396a2bac1309cbcecd30e120488ae0b66b1308652336bc03d08e933669bed306',
            '310214823660941ecac80a39cd2becd3ff4d97ef299503e9d3bf394d3fa4d5d4'
        ];

        // foreach ($data as $offerId => $quantity) {
        //     for ($i = 0; $i < $quantity; $i++) {
        //         $offer = $entityManager->getRepository(Offers::class)->find($offerId);
        //         $user = $entityManager->getRepository(User::class)->find($userId);

        //         $payment = new Payment();
        //         $payment->setOffer($offer);
        //         $payment->setUser($user);
        //         $payment->setCreatedAt(new \DateTimeImmutable());
        //         $payment->setPaymentKey($this->generateRandomKey()); // Générer la clé de paiement
        //         $generatedKeys[] = $payment->getPaymentKey();

        //         $entityManager->persist($payment);
        //     }
        // }

        // $entityManager->flush();
        // $session->remove('cart');

        $newTickets = [
            'user' => $userId,
            'keys' => $generatedKeys
        ];

        $session->set('tickets_info', $newTickets);
        return $this->redirectToRoute('order.processing');
    }

    #[Route('/processing', name: 'processing')]
    public function paymentProcessing(Request $request, EntityManagerInterface $entityManager, Session $session): Response
    {

        $newTickets = $session->get('tickets_info');
        $userId = $newTickets['user'];
        $user = $entityManager->getRepository(User::class)->find($userId);
        $userKey = $user->getAccountKey();
        $paymentsKeys = $newTickets['keys'];

        // Remove the session
        // if ($userId !== null && $paymentsKeys !== null) {
        //     $session->remove('tickets_info');
        // }


        dd($userId, $user, $userKey, $paymentsKeys);
        foreach ($paymentsKeys as $key) {
            $ticketKey = $userKey . $key;

            $newTicket = new Ticket();
            $newTicket->setUser($user);
            $newTicket->setConcatenedKey($ticketKey);

            var_dump($key);
        }


        return $this->render('order/processing.html.twig', [
            'message' => 'Transaction en cours...'
        ]);
    }

    #[Route('/success', name: 'success')]
    public function paymentSuccess(): Response
    {
        return $this->render('order/success.html.twig', [
            'message' => 'Paiement validé !'
        ]);
    }

    private function generateRandomKey(): string
    {
        return bin2hex(random_bytes(32));
    }

}
