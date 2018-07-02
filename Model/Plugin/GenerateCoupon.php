<?php
namespace Brandastic\NewsletterCoupon\Model\Plugin;

class GenerateCoupon
{
    public function beforeSendConfirmationSuccessEmail(\Magento\Model\Newsletter\Subscriber $subject)
    {

        return null;
    }
}