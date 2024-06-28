<?php

namespace App\Controller;

use App\Entity\PaymentRequest;
use App\Enumerations\PaymentType;
use App\Service\ACIService;
use App\Service\Shift4Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PaymentRequestController extends AbstractController
{

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly Shift4Service $shift4Service,
        private readonly ACIService $ACIService,
        private readonly EntityManagerInterface $entityManager
    ){}

    #[Route('/payment/request/{type}', name: 'app_payment_request', methods: ['POST'])]
    public function new(Request $request, string $type): JsonResponse
    {
        try{
            $requestData = $request->getContent();
            $postData = json_decode($requestData, true);
            $errors = $this->validateRequest($type, $postData);
            if (empty($errors)) {
                $paymentRequest = (new PaymentRequest())
                    ->setType($type)
                    ->setAmount($postData['amount'])
                    ->setCurrency($postData['currency'])
                    ->setCreatedAt(new \DateTime())
                    ->setStatus('pending');
                $this->entityManager->persist($paymentRequest);
                $this->entityManager->flush();
                match ($type) {
                    PaymentType::TYPE_ACI => $response = $this->ACIService->sendPaymentRequest($postData, $paymentRequest),
                    PaymentType::TYPE_SHIFT4 => $response = $this->shift4Service->sendPaymentRequest($postData, $paymentRequest),
                    default => throw new \Exception('Given payment type not recognized')
                };
                return $this->json($response);
            }
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        } catch(\Exception $e) {
            return $this->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param string $type
     * @param array $requestData
     * @return array
     */
    private function validateRequest(string $type, array $requestData): array
    {
        $errors = array();
        if(!in_array($type, PaymentType::getCollection())) {
            $errors['type'] = "Invalid payment request type provided!";
            return $errors;
        }
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $constraints = new Collection([
            'amount' => [
                new NotBlank(),
                new Positive(),
                new Regex([
                    'pattern' => '/^\d+$/',
                    'message' => 'Amount should be a valid integer.',
                ]),
            ],
            'currency' => [
                new NotBlank(),
            ],
            'card_number' => [
                new NotBlank(),
            ],
            'card_exp_year' => [
                new NotBlank(),
            ],
            'card_exp_month' => [
                new NotBlank(),
            ],
            'card_cvv' => [
                new NotBlank(),
            ],
        ]);

        $violations = $this->validator->validate($requestData, $constraints);
        foreach ($violations as $violation) {
            $entryErrors = (array) $propertyAccessor->getValue($errors, $violation->getPropertyPath());
            $entryErrors[] = $violation->getMessage();
            $propertyAccessor->setValue($errors, $violation->getPropertyPath(), $entryErrors);
        }
        return $errors;
    }
}
