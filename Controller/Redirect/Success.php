<?php


namespace Laurent35240\GoCardless\Controller\Redirect;


use Laurent35240\GoCardless\Model\PaymentMethod;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Psr\Log\LoggerInterface;

class Success extends AbstractRedirectAction
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

            $redirectFlow = $goCardlessClient->redirectFlows()->complete($redirectFlowId, [
                'params' => [
                    'session_token' => $this->getSessionToken()
                ]
            ]);

            $mandateId = $redirectFlow->links->mandate;

            $order = $this->paymentMethod->place();
            if (!$order) {
                throw new \Exception('Order not created');
            }

            $goCardlessClient->payments()->create([
                'params' => [
                    'amount' => round($order->getGrandTotal() * 100),
                    'currency' => $order->getOrderCurrencyCode(),
                    'reference' => $order->getIncrementId(),
                    'links' => [
                        'mandate'   => $mandateId
                    ]
                ]
            ]);

            // prepare session to success or cancellation page
            $this->checkoutSession->clearHelperData();
            $quoteId = $this->getQuote()->getId();
            $this->checkoutSession->setLastQuoteId($quoteId);
            $this->checkoutSession->setLastSuccessQuoteId($quoteId);
            $this->checkoutSession->setLastOrderId($order->getId());
            $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
            $this->checkoutSession->setLastOrderStatus($order->getStatus());

            return $this->_redirect('checkout/onepage/success');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addExceptionMessage($e, 'Error processing GoCardless payment: ' . $e->getMessage());
        }

        return $this->_redirect('checkout/cart');
    }

}