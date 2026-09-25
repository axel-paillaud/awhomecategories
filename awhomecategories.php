<?php
/**
 * @author    Axelweb <contact@axelweb.fr>
 * @copyright 2026 Axelweb
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Axelweb
 */
use Axelweb\AwHomeCategories\Form\GeneralDataConfiguration;
use PrestaShop\PrestaShop\Adapter\Presenter\Category\CategoryPresenter;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

class AwHomeCategories extends Module
{
    public function __construct()
    {
        $this->name = 'awhomecategories';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Axelweb';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Home categories', [], 'Modules.Awhomecategories.Admin');
        $this->description = $this->trans('Displays a selection of categories on the home page, as a grid of links with an optional image.', [], 'Modules.Awhomecategories.Admin');

        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall this module?', [], 'Modules.Awhomecategories.Admin');

        $this->ps_versions_compliancy = [
            'min' => '8.0',
            'max' => _PS_VERSION_,
        ];
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    public function install(): bool
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        $installed = parent::install()
            && $this->registerHook('displayHome')
            && Configuration::updateValue(
                GeneralDataConfiguration::AWHOMECATEGORIES_CATEGORIES,
                json_encode($this->getDefaultCategoryIds())
            );

        // Prevent 'Unable to generate a URL for the named route [...]' error,
        // clear Symfony cache
        if ($installed) {
            Tools::clearSf2Cache();
        }

        return $installed;
    }

    public function uninstall(): bool
    {
        return parent::uninstall()
            && Configuration::deleteByName(GeneralDataConfiguration::AWHOMECATEGORIES_CATEGORIES);
    }

    /**
     * Redirect to the module symfony configuration page
     */
    public function getContent(): void
    {
        $route = $this->get('router')->generate('awhomecategories_form_configuration');
        Tools::redirectAdmin($route);
    }

    /**
     * Renders the configured categories on the home page.
     * The block is empty (nothing rendered) when no category is visible for the current customer.
     */
    public function hookDisplayHome(array $params): string
    {
        $categories = $this->getPresentedCategories();

        if (empty($categories)) {
            return '';
        }

        $this->smarty->assign([
            'categories' => $categories,
        ]);

        return $this->fetch('module:awhomecategories/views/templates/hook/displayHome.tpl');
    }

    /**
     * Default selection on install: the active children of the home category, in tree order.
     *
     * @return int[]
     */
    private function getDefaultCategoryIds(): array
    {
        $children = Category::getChildren(
            (int) Configuration::get('PS_HOME_CATEGORY'),
            (int) $this->context->language->id,
            true,
            (int) $this->context->shop->id
        );

        return array_map(static fn (array $child): int => (int) $child['id_category'], $children);
    }

    /**
     * @return int[]
     */
    private function getConfiguredCategoryIds(): array
    {
        $ids = json_decode((string) Configuration::get(GeneralDataConfiguration::AWHOMECATEGORIES_CATEGORIES), true);

        if (!is_array($ids)) {
            return [];
        }

        return array_values(array_filter(array_map('intval', $ids)));
    }

    /**
     * Loads the configured categories, keeps the ones that are active and allowed for the
     * current customer group (private shop friendly), sorted by their position in the tree,
     * and presents them with the core presenter (url, name, thumbnail sizes...).
     *
     * @return \PrestaShop\PrestaShop\Adapter\Presenter\Category\CategoryLazyArray[]
     */
    private function getPresentedCategories(): array
    {
        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;
        $idCustomer = (int) $this->context->customer->id;

        $categories = [];
        foreach ($this->getConfiguredCategoryIds() as $idCategory) {
            $category = new Category($idCategory, $idLang, $idShop);

            if (!Validate::isLoadedObject($category) || !$category->active || !$category->checkAccess($idCustomer)) {
                continue;
            }

            $categories[] = $category;
        }

        usort($categories, static fn (Category $a, Category $b): int => [$a->level_depth, $a->position] <=> [$b->level_depth, $b->position]);

        $presenter = new CategoryPresenter($this->context->link);

        return array_map(
            fn (Category $category) => $presenter->present($category, $this->context->language),
            $categories
        );
    }
}
