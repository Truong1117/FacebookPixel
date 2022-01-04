<?php
/**
 * Class PageOptions
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_FacebookPixel
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\FacebookPixel\Model\Config;

/**
 * Class PageOptions
 *
 * @category Sparsh
 * @package  Sparsh_FacebookPixel
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class PageOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'registration_page', 'label' => 'Registration Page'],
            ['value' => 'cms_page', 'label' => 'Cms Page'],
            ['value' => 'search_page', 'label' => 'Search Page'],
            ['value' => 'advanced_search_page', 'label' => 'Advanced Search Page'],
            ['value' => 'category_page', 'label' => 'Category Page'],
            ['value' => 'product_page', 'label' => 'Product Page'],
            ['value' => 'checkout_page', 'label' => 'Checkout Page'],
            ['value' => 'success_page', 'label' => 'Success Page'],
            ['value' => 'account_page', 'label' => 'Account Page']
        ];
    }
}
