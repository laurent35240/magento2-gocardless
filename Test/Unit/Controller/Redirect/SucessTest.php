<?php


namespace Laurent35240\GoCardless\Test\Unit\Controller\Redirect;

use Laurent35240\GoCardless\Controller\Redirect\Success;
use Laurent35240\GoCardless\Model\PaymentMethod;
use Laurent35240\GoCardless\Test\TestCase;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use Magento\Framework\ObjectManagerInterface;


class SucessTest extends TestCase
{
    public function testExecute_completeRedirectFlowAndCreatePayment()
    {
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $paymentMethod = $this->getMockBuilder(PaymentMethod::class)->disableOriginalConstructor()->getMock();
        $paymentMethod->expects($this->any())
            ->method('place')
            ->willReturn($order);
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)->getMock();
        $objectManager->expects($this->any())
            ->method('create')
            ->with(PaymentMethod::class)
            ->willReturn($paymentMethod);
        $request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMock();
        $request->expects($this->any())
            ->method('getParam')
            ->with('redirect_flow_id')
            ->willReturn('RE123');
        $redirect = $this->getMockBuilder(\Magento\Framework\App\Response\RedirectInterface::class)->getMock();
        $context = $this->getContextMockWithUrlAndResponse();
        $context->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($objectManager);
        $context->expects($this->any())
            ->method('getRequest')
            ->willReturn($request);
        $context->expects($this->any())
            ->method('getRedirect')
            ->willReturn($redirect);

        $controller = new Success(
            $this->getScopeConfigInterfaceMock(),
            $this->getLoggerInterfaceMock(),
            $this->getCheckoutSessionMockWithQuote(),
            $context
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

        /** @var Response $response */
        $response = $controller->execute();
    }
}