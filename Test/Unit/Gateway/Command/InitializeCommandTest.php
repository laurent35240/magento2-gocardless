<?php


namespace Laurent35240\GoCardless\Test\Unit\Gateway\Command;


use Laurent35240\GoCardless\Gateway\Command\InitializeCommand;
use Laurent35240\GoCardless\Test\TestCase;
use Magento\Framework\DataObject;

class InitializeCommandTest extends TestCase
{
    public function testExecute_statusPendingPayment()
    {
        $command = new InitializeCommand();
        $commandSubject = [
            'stateObject' => new DataObject(),
        ];

        $command->execute($commandSubject);
        $this->assertEquals('pending_payment', $commandSubject['stateObject']->getStatus());
        $this->assertEquals('pending_payment', $commandSubject['stateObject']->getState());
    }
}