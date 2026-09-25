<?php
/**
 * @author    Axelweb <contact@axelweb.fr>
 * @copyright 2026 Axelweb
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Axelweb
 */
declare(strict_types=1);

namespace Axelweb\AwHomeCategories\Form;

use PrestaShopBundle\Form\Admin\Type\CategoryChoiceTreeType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\FormBuilderInterface;

class GeneralFormType extends TranslatorAwareType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('categories', CategoryChoiceTreeType::class, [
                'label' => $this->trans('Categories to display', 'Modules.Awhomecategories.Admin'),
                'help' => $this->trans('Selected categories are displayed on the home page, in the order of the category tree. Inactive categories and categories the customer group cannot access are hidden automatically. Upload a category thumbnail (Catalog > Categories) to illustrate a card.', 'Modules.Awhomecategories.Admin'),
                'multiple' => true,
                'required' => false,
            ]);
    }
}
