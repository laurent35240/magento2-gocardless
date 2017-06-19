<?php


namespace Laurent35240\GoCardless\Test\Unit\Controller\Redirect;


use Laurent35240\GoCardless\Controller\Redirect\Start;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Locale\Resolver as LocaleResolver;

class StartTest extends TestCase
{
    public function testExecute()
    {
        $scopeConfigInterface = $this->getMockBuilder(ScopeConfigInterface::class)->getMock();
        $loggerInterface = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $billingAddress = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)->disableOriginalConstructor()->getMock();
        $quote = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()->getMock();
        $quote->expects($this->any())
            ->method('getBillingAddress')
            ->willReturn($billingAddress);
        $checkoutSession = $this->getMockBuilder(CheckoutSession::class)->disableOriginalConstructor()->getMock();
        $checkoutSession->expects($this->any())
            ->method('getQuote')
            ->willReturn($quote);
        $localeResolver = $this->getMockBuilder(LocaleResolver::class)->disableOriginalConstructor()->getMock();
        $context = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $urlInterface = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)->getMock();
        $context->expects($this->any())
            ->method('getUrl')
            ->willReturn($urlInterface);
        $context->expects($this->any())
            ->method('getResponse')
            ->willReturn(new Response());
        $controller = new Start($scopeConfigInterface, $loggerInterface, $checkoutSession, $localeResolver, $context);

        $expectedRedirectUrl = 'https://pay.gocardless.com/flow/RE123';
        $goCardlessClient = $this->getMockBuilder(\GoCardlessPro\Client::class)->disableOriginalConstructor()->setMethods(['redirectFlows'])->getMock();
        $redirectFlow = new \GoCardlessPro\Resources\RedirectFlow([
            'redirect_url' => $expectedRedirectUrl,
        ]);
        $redirectFlowService = $this->getMockBuilder(\GoCardlessPro\Services\RedirectFlowsService::class)->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $redirectFlowService->expects($this->any())
            ->method('create')
            ->willReturn($redirectFlow);
        $goCardlessClient->expects($this->any())
            ->method('redirectFlows')
            ->willReturn($redirectFlowService);
        $controller->setGoCardlessClient($goCardlessClient);

        /** @var Response $response */
        $response = $controller->execute();
        $this->assertEquals('Location: ' . $expectedRedirectUrl, $response->getHeader('Location')->toString());
    }
}