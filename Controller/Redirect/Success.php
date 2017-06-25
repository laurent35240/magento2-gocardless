<?php


namespace Laurent35240\GoCardless\Controller\Redirect;


use Laurent35240\GoCardless\Helper\OrderPlace;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Psr\Log\LoggerInterface;

class Success extends AbstractRedirectAction
{
    /** @var  OrderPlace */
    private $orderPlace;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        CheckoutSession $checkoutSession,
        Context $context,
        OrderPlace $orderPlace
    ) {
        parent::__construct($scopeConfig, $logger, $checkoutSession, $context);
        $this->orderPlace = $orderPlace;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $quote = $this->checkoutSession->getQuote();
        try {
            $redirectFlowId = $this->getRequest()->getParam('redirect_flow_id');
            $goCardlessClient = $this->getGoCardlessClient();

            $redirectFlow = $goCardlessClient->redirectFlows()->complete($redirectFlowId, [
                'params' => [
                    'session_token' => $this->getSessionToken()
                ]
            ]);

            $mandateId = $redirectFlow->links->mandate;

            $orderId = $this->orderPlace->execute($quote);

            $goCardlessClient->payments()->create([
                'params' => [
                    'amount' => round($quote->getGrandTotal() * 100),
                    'currency' => $quote->getQuoteCurrencyCode(),
                    'reference' => $orderId,
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
            $this->checkoutSession->setLastOrderId($orderId);

            return $this->_redirect('checkout/onepage/success');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addExceptionMessage($e, 'Error processing GoCardless payment: ' . $e->getMessage());
        }

        return $this->_redirect('checkout/cart');
    }

}