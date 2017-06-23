<?php


namespace Laurent35240\GoCardless\Test\Unit\Controller\Webhook;


use Laurent35240\GoCardless\Controller\Webhook\Index;
use Laurent35240\GoCardless\Test\TestCase;

class IndexTest extends TestCase
{
    public function testExecute_wrongSignature()
    {
        $request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)->disableOriginalConstructor()->getMock();
        $request->expects($this->any())
            ->method('getParam')
            ->with('redirect_flow_id')
            ->willReturn('RE123');
        $context = $this->getContextMock($request);
        $controller = new Index($this->getScopeConfigInterfaceMock(), $this->getLoggerInterfaceMock(), $context);
        $response = $controller->execute();
        $this->assertEquals(498, $response->getStatusCode());
    }
}