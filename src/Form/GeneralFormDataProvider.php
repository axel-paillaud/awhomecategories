<?php
/**
 * @author    Axelweb <contact@axelweb.fr>
 * @copyright 2026 Axelweb
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Axelweb
 */
declare(strict_types=1);

namespace Axelweb\AwHomeCategories\Form;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;

/**
 * Provides form data to the Symfony form from the configuration storage.
 * Keys expected/provided:
 *  - categories : int[] (category ids)
 */
class GeneralFormDataProvider implements FormDataProviderInterface
{
    /**
     * @var DataConfigurationInterface
     */
    private $dataConfiguration;

    public function __construct(DataConfigurationInterface $dataConfiguration)
    {
        $this->dataConfiguration = $dataConfiguration;
    }

    /**
     * @return array{categories: int[]}
     */
    public function getData(): array
    {
        return $this->dataConfiguration->getConfiguration();
    }

    /**
     * @param array{categories?: array<int|string>} $data
     *
     * @return array<string> List of error messages (empty if success)
     */
    public function setData(array $data): array
    {
        return $this->dataConfiguration->updateConfiguration($data);
    }
}
