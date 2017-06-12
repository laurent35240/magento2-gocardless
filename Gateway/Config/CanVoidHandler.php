<?php


namespace Laurent35240\GoCardless\Gateway\Config;


use Magento\Payment\Gateway\Config\ValueHandlerInterface;

class CanVoidHandler implements ValueHandlerInterface
{

    /**
     * Retrieve method configured value
     *
     * @param array $subject
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function handle(array $subject, $storeId = null)
    {
        return true;
    }
}