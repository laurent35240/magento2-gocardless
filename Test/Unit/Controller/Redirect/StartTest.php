<?php


namespace Laurent35240\GoCardless\Test\Unit\Controller\Redirect;


use Laurent35240\GoCardless\Controller\Redirect\Start;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use Laurent35240\GoCardless\Test\TestCase;
use Magento\Framework\Locale\Resolver as LocaleResolver;

class StartTest extends TestCase
{
    public function testExecute()
    {
        $localeResolver = $this->getMockBuilder(LocaleResolver::class)->disableOriginalConstructor()->getMock();

        $controller = new Start(
            $this->getScopeConfigInterfaceMock(),
            $this->getLoggerInterfaceMock(),
            $this->getCheckoutSessionMockWithQuote(),
            $localeResolver,
            $this->getContextMock()
        );

        $expectedRedirectUrl = 'https://pay.gocardless.com/flow/RE123';
        $goCardlessClient = $this->getGoCardlessClientMock();
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