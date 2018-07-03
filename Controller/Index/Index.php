<?php
namespace Brandastic\NewsletterCoupon\Controller\Index;

use Magento\Framework\Data\Form\FormKey;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_productSku = 'Simple1';

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_cart;

    /**
     * @var FormKey
     */
    protected $_formKey;

    /**
     * @var mixed
     */
    protected $_session, $_cartSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context
    )
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_cartSession = $objectManager->get(\Magento\Checkout\Model\Session::class);
        $this->_cart = $this->_getQuote();
        $this->_session = $objectManager->get('Magento\Customer\Model\Session');
        $this->_formKey = $objectManager->get(FormKey::class);
        $this->_productRepository = $objectManager->get(ProductRepositoryInterface::class);
        return parent::__construct($context);
    }

    public function execute()
    {
        $this->addProductToCart();
        $this->applyPromoCode();
        $this->_redirect('checkout');
    }

    protected function addProductToCart()
    {
        try{
            $product = $this->_productRepository->get($this->_productSku);
        } catch (\Exception $e)
        {
            return;
        }

        $params = array(
            'form_key' => $this->_formKey->getFormKey(),
            'product' => $product->getId(),
            'qty'   => 1
        );

        $data = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\DataObject::class);

        $data->addData($params);
        try{
            // check if already in cart before adding
            if (!$this->_cart->getItemByProduct($product))
            {
                $this->_cart->addProduct($product, $data);
            }
        } catch (\Exception $e)
        {
            return;
        }
    }

    /**
     * Get the promo code from the customer session and apply it
     */
    protected function applyPromoCode()
    {
        $couponCode = $this->_getSession()->getData('coupon');
        if ($couponCode !== NULL)
        {
            $this->_getQuote()->setCouponCode($couponCode)
                ->collectTotals()
                ->save();
        }
    }

    protected function _getSession()
    {
        return $this->_session;
    }

    protected function _getCartSession()
    {
        return $this->_cartSession;
    }

    protected function _getQuote()
    {
        return $this->_getCartSession()->getQuote();
    }
}