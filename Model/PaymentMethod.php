<?php


namespace Laurent35240\GoCardless\Model;


use Magento\Sales\Model\Order;

class PaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    const ALLOWED_CURRENCY_CODES = ['GBP', 'EUR', 'SEK'];
    const ACTION_AUTHORIZE_CAPTURE = 'authorize_capture';

    protected $_code = 'gocardless';

    /**
     * @var string
     */
    protected $_formBlockType = 'Magento\Paypal\Block\Express\Form';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Magento\Paypal\Block\Payment\Info';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isGateway = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canOrder = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapturePartial = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canVoid = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseCheckout = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canFetchTransactionInfo = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canReviewPayment = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /** @var \Magento\Quote\Model\Quote  */
    private $quote;

    /** @var \Magento\Quote\Model\QuoteManagement  */
    private $quoteManagement;

    /** @var \Magento\Customer\Model\Session  */
    private $customerSession;

    /** @var \Magento\Checkout\Helper\Data  */
    private $checkoutData;

    public function __construct(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->customerSession = $customerSession;
        $this->checkoutData = $checkoutData;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function canUseForCurrency($currencyCode)
    {
        return in_array($currencyCode, self::ALLOWED_CURRENCY_CODES);
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param string $paymentAction
     * @param \Magento\Framework\DataObject $stateObject
     * @return void
     */
    public function initialize($paymentAction, $stateObject)
    {
        switch ($paymentAction) {
            case self::ACTION_AUTHORIZE_CAPTURE:
                /** @var \Magento\Sales\Model\Order\Payment $payment */
                $payment = $this->getInfoInstance();
                /** @var Order $order */
                $order = $payment->getOrder();
                $order->setCanSendNewEmailFlag(false);
                $payment->setAmountAuthorized($order->getTotalDue());
                $payment->setBaseAmountAuthorized($order->getBaseTotalDue());

                $stateObject->setData('state', Order::STATE_PENDING_PAYMENT);
                $stateObject->setData('status', Order::STATE_PENDING_PAYMENT);
                $stateObject->setData('is_notified', false);
                break;
            default:
                break;
        }
    }

    /**
     * PLace the order when customer returns from GoCardless page
     */
    public function place()
    {
        if ($this->getCheckoutMethod() == \Magento\Checkout\Model\Type\Onepage::METHOD_GUEST) {
            $this->prepareGuestQuote();
        }

        $this->quote->collectTotals();
        $order = $this->quoteManagement->submit($this->quote);
        return $order;
    }

    /**
     * Get checkout method
     *
     * @return string
     */
    private function getCheckoutMethod()
    {
        if ($this->getCustomerSession()->isLoggedIn()) {
            return \Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER;
        }
        if (!$this->quote->getCheckoutMethod()) {
            if ($this->checkoutData->isAllowedGuestCheckout($this->quote)) {
                $this->quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
            } else {
                $this->quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER);
            }
        }
        return $this->quote->getCheckoutMethod();
    }

    /**
     * Get customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * Prepare quote for guest checkout order submit
     */
    private function prepareGuestQuote()
    {
        $quote = $this->quote;
        $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
    }
}