<?php

namespace App\Service;

use App\Entity\PaymentRequest;
use Doctrine\ORM\EntityManagerInterface;
use Shift4\Exception\MappingException;
use Shift4\Request\ChargeRequest;
use Shift4\Shift4Gateway;

class Shift4Service
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ){}

    /**
     * @throws MappingException
     */
    public function sendPaymentRequest(array $postData, PaymentRequest $paymentRequest): array
    {
        $shift4 = new Shift4Gateway();
        $data = array(
            "amount" => $postData['amount'],
            "currency" => $postData['currency'],
            "card" => array(
                'number' => $postData['card_number'],
                'expMonth' => $postData['card_exp_month'],
                'expYear' => $postData['card_exp_year'],
                'cvc' => $postData['card_cvv'],
            ),
            "description" => "Test charge"
        );
        $chargeRequest = new ChargeRequest($data);
        $charge = $shift4->createCharge($chargeRequest);
        $paymentRequest->setTransactionId($charge->getId());
        $paymentRequest->setStatus($charge->getStatus());
        $paymentRequest->setCardBin($charge->getCard()->getFirst6());
        $paymentRequest->setGatewayResponse($charge->toArray());
        $paymentRequest->setCreatedAt((new \DateTime())->setTimestamp($charge->getCreated()));

        $this->entityManager->persist($paymentRequest);
        $this->entityManager->flush();

        return array(
            'transaction_id' => $charge->getId(),
            'created_at' => $paymentRequest->getCreatedAt()->format(DATE_ATOM),
            'amount' => $charge->getAmount(),
            'currency' => $charge->getCurrency(),
            'card_bin' => $paymentRequest->getCardBin(),
        );

    }
}