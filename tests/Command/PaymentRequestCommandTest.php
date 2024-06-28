<?php
namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PaymentRequestCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('metricalo:payment:request');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs([200, 'EUR', '4200000000000000', '11', '2027', 333]);
        $commandTester->execute([
            'type' => 'aci',
        ]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('transaction_id', $output);
        $this->assertStringContainsString('created_at', $output);
        $this->assertStringContainsString('amount', $output);
        $this->assertStringContainsString('currency', $output);
        $this->assertStringContainsString('card_bin', $output);

    }
}