<?php


namespace Laurent35240\GoCardless\Test\Unit\Gateway\Validator;


use Laurent35240\GoCardless\Gateway\Validator\CurrencyValidator;
use Laurent35240\GoCardless\Test\TestCase;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class CurrencyValidatorTest extends TestCase
{
    public function testValidate_EURIsValid()
    {
        $resultFactory = $this->getMockBuilder(ResultInterfaceFactory::class)->setMethods(['create'])->getMock();
        $resultFactory->expects($this->once())
            ->method('create')
            ->with(
                [
                    'isValid' => true,
                    'failsDescription' => []
                ]
            );
        $validator = new CurrencyValidator($resultFactory);
        $validator->validate(['currency' => 'EUR']);
    }

    public function testValidate_USDIsInValid()
    {
        $resultFactory = $this->getMockBuilder(ResultInterfaceFactory::class)->setMethods(['create'])->getMock();
        $resultFactory->expects($this->once())
            ->method('create')
            ->with(
                [
                    'isValid' => false,
                    'failsDescription' => []
                ]
            );
        $validator = new CurrencyValidator($resultFactory);
        $validator->validate(['currency' => 'USD']);
    }
}