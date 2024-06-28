<?php

namespace App\Command;

use App\Entity\PaymentRequest;
use App\Enumerations\PaymentType;
use App\Service\ACIService;
use App\Service\Shift4Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'metricalo:payment:request',
    description: 'Makes a payment request to the specified external system',
)]
class PaymentRequestCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Shift4Service $shift4Service,
        private readonly ACIService $ACIService,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('type', InputArgument::REQUIRED, 'Payment type[shift4/aci]');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $type = $input->getArgument('type');
        if(!in_array($type, PaymentType::getCollection())) {
            $io->error('Type: Unrecognized payment type');
            return Command::FAILURE;
        }
        $helper = $this->getHelper('question');
        $question = new Question('Please Enter the payment amount: ', '');
        $amount = (int)$helper->ask($input, $output, $question);
        if($amount === 0) {
            $io->error('Amount: Invalid value for amount');
            return Command::FAILURE;
        }
        $question = new Question('Please Enter the currency: ', '');
        $currency = $helper->ask($input, $output, $question);
        if(!$this->validateInput($currency, 'currency', $io)) {
            return Command::FAILURE;
        }
        $question = new Question('Please Enter the card number: ', '');
        $cardNumber = $helper->ask($input, $output, $question);
        if(!$this->validateInput($cardNumber, 'card number', $io)) {
            return Command::FAILURE;
        }
        $question = new Question('Please Enter the card expiry month: ', '');
        $expMonth = $helper->ask($input, $output, $question);
        if(!$this->validateInput($expMonth, 'expiry month', $io)) {
            return Command::FAILURE;
        }
        $question = new Question('Please Enter the card expiry year: ', '');
        $expYear = $helper->ask($input, $output, $question);
        if(!$this->validateInput($expYear, 'expiry year', $io)) {
            return Command::FAILURE;
        }

        $question = new Question('Please Enter the card cvv: ', '');
        $cvv = (int)$helper->ask($input, $output, $question);
        if($cvv === 0) {
            $io->error('CVV: Invalid value for card cvv');
            return Command::FAILURE;
        }
        $paymentRequest = (new PaymentRequest())
            ->setType($type)
            ->setAmount($amount)
            ->setCurrency($currency)
            ->setCreatedAt(new \DateTime())
            ->setStatus('pending');
        $this->entityManager->persist($paymentRequest);
        $this->entityManager->flush();
        $data = array(
            "amount" => $amount,
            "currency" => $currency,
            "card_number" => $cardNumber,
            "card_exp_month" => $expMonth,
            "card_exp_year" => $expYear,
            "card_cvv" => $cvv
        );
        try{
            match ($type) {
                PaymentType::TYPE_ACI => $response = $this->ACIService->sendPaymentRequest($data, $paymentRequest),
                PaymentType::TYPE_SHIFT4 => $response = $this->shift4Service->sendPaymentRequest($data, $paymentRequest),
                default => throw new \Exception('Given payment type not recognized')
            };
            $io->success(print_r($response, true));
            return Command::SUCCESS;
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * @param string $input
     * @param string $inputName
     * @param SymfonyStyle $io
     * @return bool
     */
    private function validateInput(string $input, string $inputName, SymfonyStyle $io)
    {
        if($input === '') {
            $io->error($inputName . ': Invalid value for ' . $inputName);
            return false;
        }
        return true;
    }


}
