<?php


namespace Laurent35240\GoCardless\Controller\Redirect;


use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Psr\Log\LoggerInterface;

class Start extends AbstractAction
{
    /** @var  LocaleResolver */
    private $localeResolver;


    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        CheckoutSession $checkoutSession,
        LocaleResolver $localeResolver,
        Context $context)
    {
        $this->localeResolver = $localeResolver;
        parent::__construct($scopeConfig, $logger, $checkoutSession, $context);
    }


    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        try {
            $billingAddress = $this->getBillingAddress();
            $client = $this->getGoCardlessClient();
            $redirectFlowParams = [
                'session_token' => $this->getSessionToken(),
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
            return $this->getResponse();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addExceptionMessage($e, 'Can not go to GoCardless: ' . $e->getMessage());
        }

        return $this->_redirect('checkout/cart');
    }

    /**
     * @return \Magento\Quote\Model\Quote\Address
     */
    private function getBillingAddress()
    {
        return $this->getQuote()->getBillingAddress();
    }
}