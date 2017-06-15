<?php


namespace Laurent35240\GoCardless\Controller\Redirect;


use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Psr\Log\LoggerInterface;

class Success extends AbstractAction
{
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        Context $context)
    {
        parent::__construct($scopeConfig, $logger, $context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        try {
            $redirectFlowId = $this->getRequest()->getParam('redirect_flow_id');
            $goCardlessClient = $this->getGoCardlessClient();


            $goCardlessClient->redirectFlows()->complete($redirectFlowId, [
                'params' => [
                    'session_token' => $this->getSessionToken()
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addExceptionMessage($e, 'Error processing GoCarless payment: ' . $e->getMessage());
        }

        return $this->_redirect('checkout/cart');
    }

}