<?php


namespace Laurent35240\GoCardless\Test;


use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getScopeConfigInterfaceMock(): \PHPUnit_Framework_MockObject_MockObject
    {
        $scopeConfigInterface = $this->getMockBuilder(ScopeConfigInterface::class)->getMock();
        return $scopeConfigInterface;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLoggerInterfaceMock(): \PHPUnit_Framework_MockObject_MockObject
    {
        $loggerInterface = $this->getMockBuilder(LoggerInterface::class)->getMock();
        return $loggerInterface;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCheckoutSessionMockWithQuote(): \PHPUnit_Framework_MockObject_MockObject
    {
        $billingAddress = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)->disableOriginalConstructor()->getMock();
        $quote = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()->getMock();
        $quote->expects($this->any())
            ->method('getBillingAddress')
        ->willReturn($billingAddress);
        $checkoutSession = $this->getMockBuilder(CheckoutSession::class)->disableOriginalConstructor()->getMock();
        $checkoutSession->expects($this->any())
            ->method('getQuote')
        ->willReturn($quote);
        return $checkoutSession;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContextMockWithUrlAndResponse(): \PHPUnit_Framework_MockObject_MockObject
    {
        $context = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $urlInterface = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)->getMock();
        $context->expects($this->any())
            ->method('getUrl')
        ->willReturn($urlInterface);
        $context->expects($this->any())
            ->method('getResponse')
        ->willReturn(new Response());
        return $context;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getGoCardlessClientMock(): \PHPUnit_Framework_MockObject_MockObject
    {
        $goCardlessClient = $this->getMockBuilder(\GoCardlessPro\Client::class)->disableOriginalConstructor()->setMethods(['redirectFlows', 'payments'])->getMock();
        return $goCardlessClient;
    }
}