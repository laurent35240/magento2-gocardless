<?php


namespace Laurent35240\GoCardless\Helper;


use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;

class OrderPlace
{
    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Data
     */
    private $checkoutHelper;

    /**
     * Constructor
     *
     * @param CartManagementInterface $cartManagement
     * @param Session $customerSession
     * @param Data $checkoutHelper
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        Session $customerSession,
        Data $checkoutHelper
    ) {
        $this->cartManagement = $cartManagement;
        $this->customerSession = $customerSession;
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * Execute operation
     *
     * @param Quote $quote
     * @return int orderId
     * @throws LocalizedException
     */
    public function execute(Quote $quote)
    {
        if ($this->getCheckoutMethod($quote) === Onepage::METHOD_GUEST) {
            $this->prepareGuestQuote($quote);
        }

        $this->disabledQuoteAddressValidation($quote);

        $quote->collectTotals();
        return $this->cartManagement->placeOrder($quote->getId());
    }

    /**
     * Get checkout method
     *
     * @param Quote $quote
     * @return string
     */
    private function getCheckoutMethod(Quote $quote)
    {
        if ($this->customerSession->isLoggedIn()) {
            return Onepage::METHOD_CUSTOMER;
        }
        if (!$quote->getCheckoutMethod()) {
            if ($this->checkoutHelper->isAllowedGuestCheckout($quote)) {
                $quote->setCheckoutMethod(Onepage::METHOD_GUEST);
            } else {
                $quote->setCheckoutMethod(Onepage::METHOD_REGISTER);
            }
        }

        return $quote->getCheckoutMethod();
    }

    /**
     * Prepare quote for guest checkout order submit
     *
     * @param Quote $quote
     * @return void
     */
    private function prepareGuestQuote(Quote $quote)
    {
        $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(Group::NOT_LOGGED_IN_ID);
    }

    /**
     * Make sure addresses will be saved without validation errors
     *
     * @param Quote $quote
     * @return void
     */
    private function disabledQuoteAddressValidation(Quote $quote)
    {
        $billingAddress = $quote->getBillingAddress();
        $billingAddress->setData('should_ignore_validation', true);

        if (!$quote->getIsVirtual()) {
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setData('should_ignore_validation', true);
            if (!$billingAddress->getEmail()) {
                $billingAddress->setSameAsBilling(1);
            }
        }
    }
}