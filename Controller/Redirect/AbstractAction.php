<?php


namespace Laurent35240\GoCardless\Controller\Redirect;


use GoCardlessPro\Client;
use GoCardlessPro\Environment;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Action\Context;
use Psr\Log\LoggerInterface;

abstract class AbstractAction extends Action
{
    /** @var  ScopeConfigInterface */
    protected $scopeConfig;
    /** @var  LoggerInterface */
    protected $logger;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        Context $context)
    {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return Client
     */
    protected function getGoCardlessClient()
    {
        $accessToken = $this->scopeConfig->getValue('payment/gocardless/access_token');
        $sandboxEnvironment = (bool)$this->scopeConfig->getValue('payment/gocardless/sandbox_environment');
        $environment = $sandboxEnvironment ? Environment::SANDBOX : Environment::LIVE;
        $client = new Client([
            'access_token' => $accessToken,
            'environment' => $environment
        ]);
        return $client;
    }

    protected function getSessionToken()
    {
        return session_id();
    }
}