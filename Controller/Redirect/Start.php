<?php


namespace Laurent35240\GoCardless\Controller\Redirect;


use GoCardlessPro\Client;
use GoCardlessPro\Environment;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class Start extends Action
{
    /** @var  ScopeConfigInterface */
    private $scopeConfig;

    /** @var  LoggerInterface */
    private $logger;

    /** @var  CheckoutSession */
    private $checkoutSession;

    /** @var  LocaleResolver */
    private $localeResolver;

    /** @var  null|Quote */
    private $quote;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        CheckoutSession $checkoutSession,
        LocaleResolver $localeResolver,
        Context $context)
    {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->localeResolver = $localeResolver;
        parent::__construct($context);
    }


    public function execute()
    {
        $accessToken = $this->scopeConfig->getValue('payment/gocardless/access_token');
        try {
            if ($accessToken) {
                $billingAddress = $this->getBillingAddress();
                $sandboxEnvironment = (bool)$this->scopeConfig->getValue('payment/gocardless/sandbox_environment');
                $environment = $sandboxEnvironment ? Environment::SANDBOX : Environment::LIVE;
                $client = new Client([
                    'access_token' => $accessToken,
                    'environment' => $environment
                ]);
                $redirectFlowParams = [
                    'session_token' => session_id(),
                    'success_redirect_url' => $this->_url->getUrl('gocardless/redirect/success'),
                    'prefilled_customer' => [
                        'address_line1' => (string) $billingAddress->getStreetLine(1),
                        'address_line2' => (string) $billingAddress->getStreetLine(2),
                        'address_line3' => (string) $billingAddress->getStreetLine(3),
                        'city' => (string) $billingAddress->getCity(),
                        'company_name' => (string) $billingAddress->getCompany(),
                        'country_code' => (string) $billingAddress->getCountryId(),
                        'email' => (string) $billingAddress->getEmail(),
                        'family_name' => (string) $billingAddress->getLastname(),
                        'given_name' => (string) $billingAddress->getFirstname(),
                        'language' => (string) $this->localeResolver->getLocale(),
                        'postal_code' => (string) $billingAddress->getPostcode(),
                        'region' => (string) $billingAddress->getRegion(),
                    ]
                ];
                $redirectFlow = $client->redirectFlows()->create([
                    'params' => $redirectFlowParams
                ]);
                $redirectUrl = $redirectFlow->redirect_url;
                $this->getResponse()->setRedirect($redirectUrl);
                return;
            } else {
                $this->logger->error('Missing access token');
                $this->messageManager->addErrorMessage(
                    __('We can\'t start GoCardless.')
                );

            }
        } catch (\Exception $e) {
            throw $e;
            $this->logger->error($e->getMessage());
            $this->messageManager->addExceptionMessage($e, 'Can not go to GoCardless: ' . $e->getMessage());
        }

        $this->_redirect('checkout/cart');
    }

    /**
     * @return Quote
     * @throws \Exception
     */
    private function getQuote()
    {
        if (!$this->quote) {
            if (!$this->checkoutSession) {
                throw new \Exception('No checkout session');
            }
            $this->quote = $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }

    /**
     * @return \Magento\Quote\Model\Quote\Address
     */
    private function getBillingAddress()
    {
        return $this->getQuote()->getBillingAddress();
    }
}