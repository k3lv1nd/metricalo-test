<?php

namespace App\Service;

use App\Entity\PaymentRequest;
use App\Exception\ACIException;
use Doctrine\ORM\EntityManagerInterface;

class ACIService
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ){}

    /**
     * @throws ACIException
     * @throws \Exception
     */
    public function sendPaymentRequest(mixed $postData, PaymentRequest $paymentRequest): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://eu-test.oppwa.com/v1/payments");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            "amount" => $postData['amount'],
            "currency" => $postData['currency'],
            "entityId" => "8a8294174b7ecb28014b9699220015ca",
            "paymentBrand" => "VISA",
            "paymentType" => "DB",
            "card.number" => $postData['card_number'],
            "card.holder" => "Kelvin David",
            "card.expiryMonth" => $postData['card_exp_month'],
            "card.expiryYear" => $postData['card_exp_year'],
            "card.cvv" => $postData['card_cvv'],
        )));

        $headers = [
            "Authorization: Bearer OGE4Mjk0MTc0YjdlY2IyODAxNGI5Njk5MjIwMDE1Y2N8c3k2S0pzVDg=",
            "Content-Type: application/x-www-form-urlencoded"
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new ACIException("Error:" . curl_error($ch) . " (" . curl_errno($ch) . ")");
        }
        $response = json_decode($result, true);
        if(!isset($response['id'])) {
            throw new ACIException($result);
        }
        $paymentRequest->setTransactionId($response['id']);
        $paymentRequest->setCardBin($response['card']['bin']);
        $paymentRequest->setGatewayResponse($response);
        $paymentRequest->setCreatedAt(new \DateTime($response['timestamp']));
        $this->entityManager->persist($paymentRequest);
        $this->entityManager->flush();
        return array(
        "transaction_id" => $paymentRequest->getTransactionId(),
        "created_at" => $paymentRequest->getCreatedAt()->format(DATE_ATOM),
        "amount" => $response['amount'],
        "currency" => $paymentRequest->getCurrency(),
        "card_bin" => $paymentRequest->getCardBin(),
    );

    }
}