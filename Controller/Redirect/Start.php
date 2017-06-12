<?php


namespace Laurent35240\GoCardless\Controller\Redirect;


class Start extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
        $this->getResponse()->setRedirect('http://www.gocardless.com');

        return;
    }
}