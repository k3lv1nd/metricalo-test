<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PaymentRequestControllerTest extends WebTestCase
{
    public function testPaymentRequestACIPostActionSuccess(): void
    {
        $client = static::createClient();
        $data = [
            'amount' => '92',
            'currency' => 'EUR',
            'card_number' => '4200000000000000',
            "card_exp_month" => '11',
            "card_exp_year" => '2027',
            "card_cvv" => '123'
        ];
        $client->request(
            'POST',
            '/payment/request/aci',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('transaction_id', $responseData);
        $this->assertArrayHasKey('created_at', $responseData);
        $this->assertArrayHasKey('amount', $responseData);
        $this->assertArrayHasKey('currency', $responseData);
        $this->assertArrayHasKey('card_bin', $responseData);
    }

    public function testPaymentRequestShift4PostActionSuccess(): void
    {
        $client = static::createClient();
        $data = [
            'amount' => '92',
            'currency' => 'EUR',
            'card_number' => '4200000000000000',
            "card_exp_month" => '11',
            "card_exp_year" => '2027',
            "card_cvv" => '123'
        ];
        $client->request(
            'POST',
            '/payment/request/shift4',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('transaction_id', $responseData);
        $this->assertArrayHasKey('created_at', $responseData);
        $this->assertArrayHasKey('amount', $responseData);
        $this->assertArrayHasKey('currency', $responseData);
        $this->assertArrayHasKey('card_bin', $responseData);
    }
    public function testPaymentRequestPostActionInvalidInput(): void
    {
        $client = static::createClient();
        $data = [
            'amount' => 92,
            'currency' => 'EUR',
            'card_number' => '4200000000000000',
            "card_exp_month" => '11',
            "card_exp_year" => '2027',
            "card_cvv" => 123
        ];
        $client->request(
            'POST',
            '/payment/request/shi4',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        unset($data['card_cvv']);
        $client->request(
            'POST',
            '/payment/request/shift4',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }
}