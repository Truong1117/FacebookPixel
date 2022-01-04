<?php
/**
 * Class FacebookPixelInit
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_FacebookPixel
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\FacebookPixel\Block;

/**
 * Class FacebookPixelInit
 *
 * @category Sparsh
 * @package  Sparsh_FacebookPixel
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class FacebookPixelInit extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    protected $checkoutSession;
    /**
     * @var \Magento\Framework\Session\SessionManager
     */
    protected $session;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Sparsh\FacebookPixel\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $catalogHelper;

    /**
     * FacebookPixelInit constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Sparsh\FacebookPixel\Helper\Data                $helper
     * @param \Magento\Framework\Registry                      $coreRegistry
     * @param \Magento\Catalog\Helper\Data                     $catalogHelper
     * @param \Magento\Checkout\Model\SessionFactory           $checkoutSession
     * @param \Magento\Framework\Session\SessionManager        $session
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Sparsh\FacebookPixel\Helper\Data $helper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Checkout\Model\SessionFactory $checkoutSession,
        \Magento\Framework\Session\SessionManager $session,
        array $data = []
    ) {
        $this->storeManager  = $context->getStoreManager();
        $this->helper        = $helper;
        $this->coreRegistry  = $coreRegistry;
        $this->catalogHelper = $catalogHelper;
        $this->checkoutSession = $checkoutSession;
        $this->session = $session;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getFacebookPixelData()
    {
        $data = [];
        $data['id'] = $this->helper->getPixelId();
        $data['action_name'] = $this->getRequest()->getFullActionName();

        return $data;
    }

    /**
     * @return int|string
     */
    public function getDisabled()
    {
        $data   = $this->getFacebookPixelData();
        $actionName = $data['action_name'];
        $disablePageList = $this->helper->getDisablePageList();

        if ($actionName == 'customer_account_create' && in_array('registration_page', $disablePageList)) {
            return 'disable';
        } elseif (($actionName == 'cms_index_index' || $actionName == 'cms_page_view') && in_array('cms_page', $disablePageList)) {
            return 'disable';
        } elseif ($actionName == 'catalogsearch_result_index' && in_array('search_page', $disablePageList)) {
            return 'disable';
        } elseif (($actionName == 'catalogsearch_advanced_result' || $actionName == 'catalogsearch_advanced_index') && in_array('advanced_search_page', $disablePageList)
        ) {
            return 'disable';
        } elseif ($actionName == 'catalog_category_view' && in_array('category_page', $disablePageList)) {
            return 'disable';
        } elseif ($actionName == 'catalog_product_view' && in_array('product_page', $disablePageList)) {
            return 'disable';
        } elseif (in_array($actionName, ['checkout_index_index','onepagecheckout_index_index','onestepcheckout_index_index','opc_index_index']) && in_array('checkout_page', $disablePageList)) {
            return 'disable';
        } elseif (($actionName == 'checkout_onepage_success' || $actionName == 'onepagecheckout_index_success') && in_array('success_page', $disablePageList)
        ) {
            return 'disable';
        } elseif ($actionName == 'customer_account_index' && in_array('account_page', $disablePageList)) {
            return 'disable';
        } else {
            return 'enable';
        }
    }

    /**
     * @return array
     */
    private function getProductData()
    {
        if (!$this->helper->isViewProductContent()) {
            return [];
        }

        $currentProduct = $this->coreRegistry->registry('current_product');
        $data = [];
        $data['enable'] = true;
        $data['content_name']     = $this->helper->escapeSingleQuotes($currentProduct->getName());
        $data['content_ids']      = $this->helper->escapeSingleQuotes($currentProduct->getSku());
        $data['content_type']     = 'product';
        $data['value']            = $this->formatPrice($this->helper->getProductPrice($currentProduct));
        $data['currency']         = $this->helper->getCurrentCurrencyCode();

        return $data;
    }

    /**
     * @return bool|false|int|string
     */
    public function getProduct()
    {
        $productData['enable'] = false;
        $data   = $this->getFacebookPixelData();
        $action = $data['action_name'];
        if ($action == 'catalog_product_view') {
            if ($this->getProductData() !== null) {
                $productData = $this->getProductData();
            }
        }
        return $this->helper->serializes($productData);
    }

    /**
     * @param $price
     * @param string $currencyCode
     *
     * @return string
     */
    private function formatPrice($price, $currencyCode = '')
    {
        $formatedPrice = number_format($price, 2);
        if ($currencyCode) {
            return $formatedPrice . ' ' . $currencyCode;
        } else {
            return $formatedPrice;
        }
    }

    /**
     * @return bool|false|int|string
     */
    public function getCategory()
    {
        $categoryData['enable'] = false;
        $data   = $this->getFacebookPixelData();
        $action = $data['action_name'];
        if ($action == 'catalog_category_view') {
            if ($this->getCategoryData() !== null) {
                $categoryData = $this->getCategoryData();
            }
        }
        return $this->helper->serializes($categoryData);
    }

    /**
     * @return array
     */
    private function getCategoryData()
    {
        if (!$this->helper->isCategoryView()) {
            return [];
        }
        $currentCategory = $this->coreRegistry->registry('current_category');
        $data = [];
        $data['enable'] = true;
        $data['content_name']     = $this->helper->escapeSingleQuotes($currentCategory->getName());
        $data['content_ids']      = $this->helper->escapeSingleQuotes($currentCategory->getId());
        $data['content_type']     = 'category';
        $data['currency']         = $this->helper->getCurrentCurrencyCode();
        return $data;
    }

    /**
     * @return int|string
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAddToWishList()
    {
        $add_to_wishlist['enable'] = false;
        if ($this->helper->isAddToWishList() && $this->session->hasData('add_to_wishlist')) {
            $add_to_wishlist = $this->session->getData('add_to_wishlist');
            $add_to_wishlist['enable'] = true;
            $this->session->unsetData('add_to_wishlist');
        }
        return $this->helper->serializes($add_to_wishlist);
    }

    /**
     * @return int|string
     */
    public function getSearchData()
    {
        $searchdata['enable'] = false;
        if ($this->helper->isSearchEnable()) {
            $searchdata['enable'] = true;
        }
        return $this->helper->serializes($searchdata);
    }

    /**
     * @return array|int
     */
    public function getOrder()
    {
        $orderData['enable'] = false;
        $data   = $this->getFacebookPixelData();
        $action = $data['action_name'];
        if ($action == 'checkout_onepage_success' || $action == 'onepagecheckout_index_success' || $action == 'multishipping_checkout_success') {
            $orderData = $this->getOrderData();
        }
        return $this->helper->serializes($orderData);
    }

    /**
     * @return bool|false|int|string
     */
    public function getOrderData()
    {
        $order = $this->checkoutSession->create()->getLastRealOrder();
        $orderId = $order->getIncrementId();

        if ($orderId && $this->helper->isPurchaseEnable()) {
            $customerEmail = $order->getCustomerEmail();
            if ($order->getShippingAddress()) {
                $addressData = $order->getShippingAddress();
            } else {
                $addressData = $order->getBillingAddress();
            }

            if ($addressData) {
                $customerData = $addressData->getData();
            } else {
                $customerData = null;
            }
            $product = [
                'content_ids' => [],
                'contents' => [],
                'value' => "",
                'currency' => "",
                'num_items' => 0,
                'email' => "",
                'address' => []
            ];

            $num_item = 0;
            foreach ($order->getAllVisibleItems() as $item) {
                $product['contents'][] = [
                    'id' => $item->getSku(),
                    'name' => $item->getName(),
                    'quantity' => (int)$item->getQtyOrdered(),
                    'item_price' => $item->getPrice()
                ];
                $product['content_ids'][] = $item->getSku();
                $num_item += round($item->getQtyOrdered());
            }
            $data = [
                'enable' => true,
                'content_ids' => $product['content_ids'],
                'contents' => $product['contents'],
                'content_type' => 'product',
                'value' => number_format(
                    $order->getGrandTotal(),
                    2,
                    '.',
                    ''
                ),
                'num_items' => $num_item,
                'currency' => $order->getOrderCurrencyCode(),
                'email' => $customerEmail,
                'phone' => $this->getValueUsingKey($customerData, 'telephone'),
                'firstname' => $this->getValueUsingKey($customerData, 'firstname'),
                'lastname' => $this->getValueUsingKey($customerData, 'lastname'),
                'city' => $this->getValueUsingKey($customerData, 'city'),
                'country' => $this->getValueUsingKey($customerData, 'country_id'),
                'st' => $this->getValueUsingKey($customerData, 'region'),
                'zipcode' => $this->getValueUsingKey($customerData, 'postcode')
            ];
            return $data;
        } else {
            $data['enable'] = false;
            return $data;
        }
    }

    /**
     * @param $array
     * @param $key
     *
     * @return string
     */
    protected function getValueUsingKey($array, $key)
    {
        if (!empty($array) && isset($array[$key])) {
            return $array[$key];
        }
        return '';
    }
}
