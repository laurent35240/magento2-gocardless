<?php


namespace Laurent35240\GoCardless\Test\Unit\Controller\Redirect;

use Laurent35240\GoCardless\Controller\Redirect\Success;
use Laurent35240\GoCardless\Helper\OrderPlace;
use Laurent35240\GoCardless\Test\TestCase;


class SucessTest extends TestCase
{
    public function testExecute_completeRedirectFlowAndCreatePayment()
    {
        $request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMock();
        $request->expects($this->any())
            ->method('getParam')
            ->with('redirect_flow_id')
            ->willReturn('RE123');
        $context = $this->getContextMock($request);

        $orderPlace = $this->getMockBuilder(OrderPlace::class)->disableOriginalConstructor()->getMock();

        $controller = new Success(
            $this->getScopeConfigInterfaceMock(),
            $this->getLoggerInterfaceMock(),
            $this->getCheckoutSessionMockWithQuote(),
            $context,
            $orderPlace
        );

        $goCardlessClient = $this->getGoCardlessClientMock();
        $redirectFlowService = $this->getMockBuilder(\GoCardlessPro\Services\RedirectFlowsService::class)->disableOriginalConstructor()->setMethods(['complete'])->getMock();
        $paymentService = $this->getMockBuilder(\GoCardlessPro\Services\PaymentsService::class)->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $paymentService->expects($this->once())
            ->method('create');
        $redirectFlow = new \GoCardlessPro\Resources\RedirectFlow([
            'redirect_url' => 'https://pay.gocardless.com/flow/RE123',
            'links' => (object) [
                'mandate'   => 'mandateId'
            ]
        ]);
        $redirectFlowService->expects($this->once())
            ->method('complete')
            ->willReturn($redirectFlow);
        $goCardlessClient->expects($this->any())
            ->method('redirectFlows')
            ->willReturn($redirectFlowService);
        $goCardlessClient->expects($this->any())
            ->method('payments')
            ->willReturn($paymentService);
        $controller->setGoCardlessClient($goCardlessClient);

        $controller->execute();
    }
}