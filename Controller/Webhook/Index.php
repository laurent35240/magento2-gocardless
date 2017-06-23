<?php


namespace Laurent35240\GoCardless\Controller\Webhook;


use Laurent35240\GoCardless\Controller\AbstractAction;
use Magento\Framework\App\ResponseInterface;

class Index extends AbstractAction
{

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        // Getting and checking webhook signature
        $webhookSecret = $this->scopeConfig->getValue('payment/gocardless/webhook_secret');
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        $rawPayload = $request->getContent();
        $providedSignature = $request->getHeader('Webhook-Signature');
        $calculatedSignature = hash_hmac("sha256", $rawPayload, $webhookSecret);

        if ($providedSignature != $calculatedSignature) {
            $this->logger->error('Wrong signature received from GoCardless webhook');
            /** @var \Magento\Framework\App\Response\Http $response */
            $response = $this->getResponse();
            $response->setBody('Invalid signature');
            $response->setCustomStatusCode(498);
            return $response;
        }
        return $this->getResponse();
    }
}