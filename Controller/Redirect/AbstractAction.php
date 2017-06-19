<?php


namespace Laurent35240\GoCardless\Controller\Redirect;


use GoCardlessPro\Client;
use GoCardlessPro\Environment;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Action\Context;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

abstract class AbstractAction extends Action
{
    /** @var  ScopeConfigInterface */
    protected $scopeConfig;
    /** @var  LoggerInterface */
    protected $logger;
    /** @var  null|Quote */
    protected $quote;
    /** @var  CheckoutSession */
    protected $checkoutSession;

    /** @var  Client */
    private $goCardlessClient;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        CheckoutSession $checkoutSession,
        Context $context)
    {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    /**
     * @return Client
     */
    protected function getGoCardlessClient()
    {
        if (!$this->goCardlessClient) {
            $accessToken = $this->scopeConfig->getValue('payment/gocardless/access_token');
            $sandboxEnvironment = (bool)$this->scopeConfig->getValue('payment/gocardless/sandbox_environment');
            $environment = $sandboxEnvironment ? Environment::SANDBOX : Environment::LIVE;
            $client = new Client([
                'access_token' => $accessToken,
                'environment' => $environment
            ]);
            $this->goCardlessClient = $client;
        }
        return $this->goCardlessClient;
    }

    public function setGoCardlessClient(Client $goCardlessClient)
    {
        $this->goCardlessClient = $goCardlessClient;
    }

    protected function getSessionToken()
    {
        return session_id();
    }

    /**
     * @return Quote
     * @throws \Exception
     */
    protected function getQuote()
    {
        if (!$this->quote) {
            if (!$this->checkoutSession) {
                throw new \Exception('No checkout session');
            }
            $this->quote = $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }
}