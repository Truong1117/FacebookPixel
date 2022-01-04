<?php
/**
 * Class Data
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_FacebookPixel
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\FacebookPixel\Helper;

/**
 * Class Data
 *
 * @category Sparsh
 * @package  Sparsh_FacebookPixel
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var null|int
     */
    protected $taxType = null;

    /**
     * @var null|int
     */
    protected $taxaCatalogConfig = null;

    /**
     * @var null|\Magento\Store\Model\Store
     */
    protected $store = null;

    /**
     * @var null|int
     */
    protected $storeId = null;

    /**
     * @var null|string
     */
    protected $baseCurrencyCode = null;

    /**
     * @var null|string
     */
    protected $currentCurrencyCode = null;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $catalogHelper;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context        $context
     * @param \Magento\Store\Model\StoreManagerInterface   $storeManager
     * @param \Magento\Tax\Model\Config                    $taxConfig
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Catalog\Helper\Data                 $catalogHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Catalog\Helper\Data $catalogHelper
    ) {
        $this->scopeConfig          = $context->getScopeConfig();
        $this->storeManager = $storeManager;
        $this->taxConfig = $taxConfig;
        $this->json = $json;
        $this->catalogHelper = $catalogHelper;

        parent::__construct($context);
    }

    /**
     * @param null $scope
     *
     * @return mixed
     */
    public function getPixelId($scope = null)
    {
        return $this->scopeConfig->getValue(
            'sparsh_facebook_pixel/general/pixel_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * @param null $scope
     *
     * @return array
     */
    public function getDisablePageList($scope = null)
    {
        $lists = $this->scopeConfig->getValue(
            'sparsh_facebook_pixel/event_tracking/disable_code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scope
        );
        if ($lists) {
            return explode(',', $lists);
        } else {
            return [];
        }
    }

    /**
     * @param null $scope
     *
     * @return mixed
     */
    public function isIncludeTax($scope = null)
    {
        return $this->scopeConfig->getValue(
            'tax/calculation/price_includes_tax',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * @param null $scope
     *
     * @return mixed
     */
    public function isCategoryView($scope = null)
    {
        return $this->scopeConfig->getValue(
            'sparsh_facebook_pixel/event_tracking/category_view',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * @param null $scope
     *
     * @return mixed
     */
    public function isViewProductContent($scope = null)
    {
        return $this->scopeConfig->getValue(
            'sparsh_facebook_pixel/event_tracking/product_view',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * @param null $scope
     *
     * @return mixed
     */
    public function isAddToWishList($scope = null)
    {
        return $this->scopeConfig->getValue(
            'sparsh_facebook_pixel/event_tracking/add_to_wishlist',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * @param null $scope
     *
     * @return mixed
     */
    public function isSearchEnable($scope = null)
    {
        return $this->scopeConfig->getValue(
            'sparsh_facebook_pixel/event_tracking/search',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * @param null $scope
     *
     * @return mixed
     */
    public function isPurchaseEnable($scope = null)
    {
        return $this->scopeConfig->getValue(
            'sparsh_facebook_pixel/event_tracking/purchase',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * @param $product
     *
     * @return float|mixed|string|null
     */
    public function getProductPrice($product)
    {
        switch ($product->getTypeId()) {
            case 'bundle':
                $includeTax = (bool) $this->getDisplayTaxFlag();
                $price =  $this->getFinalPrice(
                    $product,
                    $product->getPriceModel()
                    ->getTotalPrices($product, 'min', $includeTax, 1)
                );
                break;
            case 'configurable':
                $price = $this->getConfigurableProductPrice($product);
                break;
            case 'grouped':
                $assocProducts = $product->getTypeInstance(true)->getAssociatedProductCollection($product)
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('tax_class_id')->addAttributeToSelect('tax_percent');
                $minPrice = INF;
                foreach ($assocProducts as $assocProduct) {
                    $minPrice = min($minPrice, $this->getFinalPrice($assocProduct));
                }
                $price =  $minPrice;
                break;
            default:
                $price = $this->getFinalPrice($product);
        }

        return $price;
    }

    /**
     * @return int|null
     */
    private function getDisplayTaxFlag()
    {
        if ($this->taxType === null) {
            $flag = $this->isTaxConfig()->getPriceDisplayType($this->getStoreId());

            if ($flag == 1) {
                $this->taxType = 0;
            } else {
                $this->taxType = 1;
            }
        }

        return $this->taxType;
    }

    /**
     * @param $product
     * @param null    $price
     *
     * @return float|null
     *
     * @throws \Exception
     */
    private function getFinalPrice($product, $price = null)
    {

        if ($price === null) {
            $price = $product->getFinalPrice();
        }

        if ($price === null) {
              $price = $product->getData('special_price');
        }
         $productType = $product->getTypeId();
        if (($this->getBaseCurrencyCode() !== $this->getCurrentCurrencyCode()) && $productType != 'configurable') {
              $price = $this->getStore()->getBaseCurrency()->convert($price, $this->getCurrentCurrencyCode());
        }

        $productType = $product->getTypeId();
        if ($productType != 'configurable' && $productType != 'bundle') {
            if ($this->getDisplayTaxFlag() && !$this->getCatalogTaxFlag()) {
                $price = $this->catalogHelper->getTaxPrice(
                    $product,
                    $price,
                    true,
                    null,
                    null,
                    null,
                    $this->getStoreId(),
                    false,
                    false
                );
            }
        }
        if ($productType != 'bundle') {
            if (!$this->getDisplayTaxFlag() && $this->getCatalogTaxFlag()) {
                $price = $this->catalogHelper->getTaxPrice(
                    $product,
                    $price,
                    false,
                    null,
                    null,
                    null,
                    $this->getStoreId(),
                    true,
                    false
                );
            }
        }
        return $price;
    }

    /**
     * @param $product
     *
     * @return float|null
     *
     * @throws \Exception
     */
    private function getConfigurableProductPrice($product)
    {
        if ($product->getFinalPrice() === 0) {
            $simpleCollection = $product->getTypeInstance()->getUsedProducts($product);

            foreach ($simpleCollection as $simpleProduct) {
                if ($simpleProduct->getPrice() > 0) {
                    return $this->getFinalPrice($simpleProduct);
                }
            }
        }

        return $this->getFinalPrice($product);
    }

    /**
     * @return int|null
     */
    private function getCatalogTaxFlag()
    {
        if ($this->taxaCatalogConfig === null) {
            $this->taxaCatalogConfig = (int) $this->isIncludeTax();
        }

        return $this->taxaCatalogConfig;
    }

    /**
     * @return string|null
     */
    public function getCurrentCurrencyCode()
    {
        if ($this->currentCurrencyCode === null) {
            $this->currentCurrencyCode = strtoupper(
                $this->getStore()->getCurrentCurrencyCode()
            );
        }

        return $this->currentCurrencyCode;
    }

    /**
     * @return string|null
     */
    public function getBaseCurrencyCode()
    {
        if ($this->baseCurrencyCode === null) {
            $this->baseCurrencyCode = strtoupper(
                $this->getStore()->getBaseCurrencyCode()
            );
        }

        return $this->baseCurrencyCode;
    }

    /**
     * @param $str
     *
     * @return mixed
     */
    public function escapeSingleQuotes($str)
    {
        return str_replace("'", "\'", $str);
    }

    /**
     * @param  $data
     * @return bool|false|string
     */
    public function serializes($data)
    {
        $result = $this->json->serialize($data);
        if (false === $result) {
            throw new \InvalidArgumentException('Unable to serialize value.');
        }
        return $result;
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface|\Magento\Store\Model\Store|null
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        if ($this->store === null) {
            $this->store = $this->storeManager->getStore();
        }

        return $this->store;
    }

    /**
     * @return int|null
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        if ($this->storeId === null) {
            $this->storeId = $this->getStore()->getId();
        }
        return $this->storeId;
    }

    /**
     * @return \Magento\Tax\Model\Config
     */
    public function isTaxConfig()
    {
        return $this->taxConfig;
    }

    /**
     * @return mixed
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }
}
