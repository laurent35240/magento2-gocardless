<?php


namespace Laurent35240\GoCardless\Test;


use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Log\LoggerInterface;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{

    /**
     * @return MockObject
     */
    protected function getScopeConfigInterfaceMock(): MockObject
    {
        $scopeConfigInterface = $this->getMockBuilder(ScopeConfigInterface::class)->getMock();
        return $scopeConfigInterface;
    }

    /**
     * @return MockObject
     */
    protected function getLoggerInterfaceMock(): MockObject
    {
        $loggerInterface = $this->getMockBuilder(LoggerInterface::class)->setMethods(['error'])->getMock();
        return $loggerInterface;
    }

    /**
     * @return MockObject
     */
    protected function getCheckoutSessionMockWithQuote(): MockObject
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
     * @return MockObject
     */
    protected function getContextMock(RequestInterface $request = null, ObjectManagerInterface $objectManager = null): MockObject
    {
        $context = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $urlInterface = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)->getMock();
        $context->expects($this->any())
            ->method('getUrl')
        ->willReturn($urlInterface);
        $context->expects($this->any())
            ->method('getResponse')
        ->willReturn(new Response());
        $redirect = $this->getMockBuilder(\Magento\Framework\App\Response\RedirectInterface::class)->getMock();
        $context->expects($this->any())
            ->method('getRedirect')
            ->willReturn($redirect);
        if ($request) {
            $context->expects($this->any())
                ->method('getRequest')
                ->willReturn($request);
        }
        if ($objectManager) {
            $context->expects($this->any())
                ->method('getObjectManager')
                ->willReturn($objectManager);
        }
        return $context;
    }

    /**
     * @return MockObject
     */
    protected function getGoCardlessClientMock(): MockObject
    {
        $goCardlessClient = $this->getMockBuilder(\GoCardlessPro\Client::class)->disableOriginalConstructor()->setMethods(['redirectFlows', 'payments'])->getMock();
        return $goCardlessClient;
    }
}