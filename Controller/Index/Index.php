<?php
namespace Brandastic\NewsletterCoupon\Controller\Index {

    use Magento\Framework\Data\Form\FormKey;
    use Magento\Catalog\Api\ProductRepositoryInterface;
    use Magento\Quote\Model\QuoteRepository;

    class Index extends \Magento\Framework\App\Action\Action
    {
        protected $_productSku = 'Simple1';

        /**
         * @var QuoteRepository
         */
        protected $_quoteRepository;
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
         * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
         * @param \Magento\Customer\Model\Session $session
         * @param FormKey $formKey
         * @param ProductRepositoryInterface $productRepository
         * @param \Magento\Checkout\Model\Cart $cart
         */
        public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory,
            \Magento\Customer\Model\Session $session,
            FormKey $formKey,
            ProductRepositoryInterface $productRepository,
            \Magento\Checkout\Model\Cart $cart
        )
        {
            parent::__construct($context);
            $this->_cart = $cart;
            $this->_session = $session;
            $this->_formKey = $formKey;
            $this->_productRepository = $productRepository;
            return parent::__construct($context);
        }

        public function execute()
        {
            $this->addProductToCart();
            $this->_redirect('checkout/cart');
        }

        protected function addProductToCart()
        {
            try {
                $product = $this->_productRepository->get($this->_productSku);
            } catch (\Exception $e) {
                return;
            }

            $data = new \Magento\Framework\DataObject([
                'form_key' => $this->_formKey->getFormKey(),
                'product' => $product->getId(),
                'qty' => 1
            ]);

            try {
                if (!$this->_cart->getQuote()->getItemByProduct($product)) {
                    $this->_cart->getQuote()->addProduct($product, $data);
                    $this->applyPromoCode();
                    $this->_cart->save();
                }
            } catch (\Exception $e) {
                return;
            }
        }

        /**
         * Get the promo code from the customer session and apply it
         */
        protected function applyPromoCode()
        {
            $couponCode = $this->_session->getData('coupon');
            if ($couponCode !== NULL) {
                $this->_cart->getQuote()->setCouponCode($couponCode)
                    ->collectTotals();
            }
        }

        protected function _getSession()
        {
            return $this->_session;
        }
    }
}