<?php
/**
 * 2007-2015 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Adapter\Category\CategoryProductSearchProvider;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;

class Ps_FeederrssModuleFrontController extends ModuleFrontController
{
    private function getProducts($idCategory, $nProducts, $orderBy, $orderWay)
    {
        $category = new Category($idCategory);

        $searchProvider = new CategoryProductSearchProvider(
            $this->getTranslator(),
            $category
        );

        $context = new ProductSearchContext($this->context);

        $query = new ProductSearchQuery();
        $query
            ->setResultsPerPage($nProducts)
            ->setPage(1)
        ;

        $query->setSortOrder(new SortOrder('product', $orderBy, $orderWay));

        $result = $searchProvider->runQuery(
            $context,
            $query
        );

        $assembler = new ProductAssembler($this->context);
        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new ProductListingPresenter(
            new ImageRetriever(
                $this->context->link
            ),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );

        $productsForTemplate = array();

        $products = $result->getProducts();

        if (!empty($products)) {
            foreach ($products as $rawProduct) {
                $productsForTemplate[] = $presenter->present(
                    $presentationSettings,
                    $assembler->assembleProduct($rawProduct),
                    $this->context->language
                );
            }
        }

        return $productsForTemplate;
    }

    private function getSmartyVariables()
    {
        $id_category = (int)Tools::getValue('id_category');
        $id_category = $id_category ? $id_category : Configuration::get('PS_HOME_CATEGORY');

        $number = (int)Tools::getValue('n', 10);
        $number = $number > 10 ? 10 : $number;

        $orderBy = Tools::getProductsOrder('by', Tools::getValue('orderby'));
        $orderWay = Tools::getProductsOrder('way', Tools::getValue('orderway'));

        $products = $this->getProducts($id_category, $number, $orderBy, $orderWay);

        return array(
            'products' => $products,
            'currency' => new Currency((int)$this->context->currency->id),
            'affiliate' => (Tools::getValue('ac') ? '?ac=' . (int)Tools::getValue('ac') : ''),
            'metas' => Meta::getMetaByPage('index', (int)$this->context->language->id),
            'shop_uri' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__,
            'shop_name' => Configuration::get('PS_SHOP_NAME'),
            'shop_email' => Configuration::get('PS_SHOP_EMAIL'),
            'language_iso' => $this->context->language->iso_code,
            'logo' => $this->context->link->getMediaLink(_PS_IMG_ . Configuration::get('PS_LOGO')),
        );
    }

    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign($this->getSmartyVariables());

        header("Content-Type:text/xml; charset=utf-8");
        $this->setTemplate('module:ps_feeder/views/template/front/rss.tpl');
    }
}
