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
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;

#[Route('/order', name: 'order.')]
#[IsGranted('ROLE_VERIFIED')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, SessionInterface $session, OffersRepository $offersRepository): Response
    {
        if ($id = $request->query->get('id')) {
            $cart[$id] = 1;
        } else {
            $cart = $session->get('cart', []);
        }

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
        $jsonData = $request->request->get('data');
        $data = json_decode($jsonData, true);
        $userId = $request->request->get('userId');

        foreach ($data as $offerId => $quantity) {
            for ($i = 0; $i < $quantity; $i++) {
                $offer = $entityManager->getRepository(Offers::class)->find($offerId);
                $user = $entityManager->getRepository(User::class)->find($userId);

                $payment = new Payment();
                $payment->setOffer($offer);
                $payment->setUser($user);
                $payment->setCreatedAt(new \DateTimeImmutable());
                $payment->setPaymentKey($this->generateRandomKey()); // Générer la clé de paiement
                $generatedKeys[] = $payment->getPaymentKey();

                $entityManager->persist($payment);
            }
        }

        $entityManager->flush();
        $session->remove('cart');
        $session->remove('totalCart');

        $newTickets = [
            'user' => $userId,
            'keys' => $generatedKeys
        ];

        $session->set('tickets_info', $newTickets);
        return $this->redirectToRoute('order.processing');
    }


    #[Route('/processing', name: 'processing')]
    public function paymentProcessing(): Response
    {
        return $this->render('order/processing.html.twig');
    }

    #[Route('/success', name: 'success')]
    public function paymentSuccess(Request $request, EntityManagerInterface $entityManager, Session $session): Response
    {

        $newTickets = $session->get('tickets_info');
        $userId = $newTickets['user'];
        $user = $entityManager->getRepository(User::class)->find($userId);
        $userName = $user->getUsername();
        $userKey = $user->getAccountKey();
        $paymentsKeys = $newTickets['keys'];

        foreach ($paymentsKeys as $key) {
            $ticketKey = $userKey . $key;
            $qrName = $this->generateQRCode($ticketKey, $userName);

            $newTicket = new Ticket();
            $newTicket->setUser($user);
            $newTicket->setConcatenedKey($ticketKey);
            $newTicket->setQrCode($qrName);

            $entityManager->persist($newTicket);
        }

        $entityManager->flush();
        $session->remove('tickets_info');

        return $this->render('order/success.html.twig');
    }

    private function generateRandomKey(): string
    {
        return bin2hex(random_bytes(32));
    }

    private function generateQRCode(string $text, string $user): string
    {
        $nameFile = strtolower($user) . '_' . (new \DateTime())->format('YmdHis') . '_' . uniqid() . '.png';

        $qr_code = QrCode::create($text)
            ->setSize(500)
            ->setMargin(50)
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::High);
        $writer = new PngWriter();
        $result = $writer->write($qr_code);

        $result->saveToFile('images/qrcodes/' . $nameFile);

        return $nameFile;
    }
}
