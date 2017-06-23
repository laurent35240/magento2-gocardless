<?php


namespace Laurent35240\GoCardless\Controller;


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
}