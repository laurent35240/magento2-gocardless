<?php


namespace Laurent35240\GoCardless\Gateway\Validator;



use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

class CurrencyValidator extends AbstractValidator
{
    const ALLOWED_CURRENCY_CODES = ['GBP', 'EUR', 'SEK'];

    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $currencyCode = $validationSubject['currency'];
        $isValid = in_array($currencyCode, self::ALLOWED_CURRENCY_CODES);
        return $this->createResult($isValid);
    }

}