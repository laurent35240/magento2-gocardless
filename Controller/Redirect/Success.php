<?php


namespace Laurent35240\GoCardless\Controller\Redirect;


use Laurent35240\GoCardless\Model\PaymentMethod;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Psr\Log\LoggerInterface;

class Success extends AbstractAction
{
    /** @var  PaymentMethod */
    private $paymentMethod;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        CheckoutSession $checkoutSession,
        Context $context)
    {
        parent::__construct($scopeConfig, $logger, $checkoutSession, $context);
        $quote = $this->getQuote();

        $paymentMethod = $this->_objectManager->create(PaymentMethod::class, ['quote' => $quote]);
        $this->paymentMethod = $paymentMethod;
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

            $this->paymentMethod->place();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addExceptionMessage($e, 'Error processing GoCarless payment: ' . $e->getMessage());
        }

        return $this->_redirect('checkout/cart');
    }

}