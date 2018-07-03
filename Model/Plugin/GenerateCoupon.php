<?php
namespace Brandastic\NewsletterCoupon\Model\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;

class GenerateCoupon
{
    protected $_productSku = 'Simple1';

    public function beforeSendConfirmationSuccessEmail(\Magento\Framework\Model\AbstractModel $subject)
    {
        if (!$this->codeExistsForSession())
        {
            $code = $this->getCouponCode();
            if ($code !== false) {
                $this->addCodeToSession($code);
            } else {
                return null;
            }
        }

        $subject->setData('coupon_code', $this->getSession()->getData('coupon'));

        return null;
    }

    public function getObjectManager()
    {
        return \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Generate a random string of 8 characters
     * @return string
     */
    protected function getRandomCode()
    {
        $chars = "abcdefghijkmnopqrstuvwxyz023456789";
        srand((double)microtime()*1000000);
        $i = 0;
        $pass = '' ;

        while ($i <= 7) {
            $num = rand() % 33;
            $tmp = substr($chars, $num, 1);
            $pass = $pass . $tmp;
            $i++;
        }

        return $pass;
    }

    protected function getCustomer()
    {
        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $cusomerModel */
        $cusomerModel = $this->getObjectManager()->get('\Magento\Customer\Api\CustomerRepositoryInterface');
        return $cusomerModel;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    protected function getSession()
    {
        /** @var \Magento\Customer\Model\Session $session */
        $session = $this->getObjectManager()->get('\Magento\Customer\Model\Session');
        return $session;
    }

    /**
     * This needs to be updated to first check if the code exists so there is no collision
     *
     * Normally we want to report any failures with this function, we would implement logic for this in the calling function
     */
    protected function getCouponCode()
    {
        $randomCode = $this->getRandomCode();

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->getObjectManager()->get('Magento\Catalog\Api\ProductRepositoryInterface');
        try {
            $product = $productRepository->get($this->_productSku);
        } catch (NoSuchEntityException $e) {
            return false;
        }

        if (!$product->getId())
        {
            return false;
        }

        // using the old crud models since the core class is still using them, but crud models are deprecated so this should use repository and filters
        /** @var \Magento\SalesRule\Model\Rule $coupon */
        $coupon = $this->getObjectManager()->get('Magento\SalesRule\Model\Rule');

        $coupon->setName('Newsletter Coupon')
            ->setDescription('Auto generated from newsletter signup')
            ->setFromDate( date('Y-m-d'))
            ->setToDate('')
            ->setUsesPerCustomer(1)
            ->setCustomerGroupIds('0')
            ->setIsActive('1')
            ->setSimpleAction('by_percent')
            ->setProductIds((string)$product->getId())
            ->setDiscountAmount(15.000)
            ->setDiscountQty(1)
            ->setApplyToShipping(0)
            ->setTimesUsed(1)
            ->setWebsiteIds('1')
            ->setCouponType('2')
            ->setCouponCode($randomCode)
            ->setUsesPerCoupon(NULL);
        try {
            $coupon->save();
        } catch (\Exception $e)
        {
            return false;
        }
        return $coupon->getCouponCode();
    }

    protected function addCodeToSession($code)
    {
        $session = $this->getSession();
        $session->setData('coupon', $code);
    }

    /**
     * @return bool
     */
    protected function codeExistsForSession()
    {
        $session = $this->getSession();
        return $session->getData('coupon') !== null ? true : false;
    }
}