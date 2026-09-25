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
use PrestaShop\PrestaShop\Core\ConfigurationInterface;

/**
 * Configuration is used to save data to configuration table and retrieve from it.
 * The category selection is stored as a JSON array of category ids.
 */
final class GeneralDataConfiguration implements DataConfigurationInterface
{
    public const AWHOMECATEGORIES_CATEGORIES = 'AWHOMECATEGORIES_CATEGORIES';

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return array{categories: int[]}
     */
    public function getConfiguration(): array
    {
        return [
            'categories' => $this->getIdList(static::AWHOMECATEGORIES_CATEGORIES),
        ];
    }

    public function updateConfiguration(array $configuration): array
    {
        $errors = [];

        if (!$this->validateConfiguration($configuration)) {
            $errors[] = 'Invalid configuration payload.';

            return $errors;
        }

        $categories = $this->parseIds((array) ($configuration['categories'] ?? []));

        $this->configuration->set(static::AWHOMECATEGORIES_CATEGORIES, json_encode($categories));

        // empty = ok
        return $errors;
    }

    /**
     * Ensure the parameters passed are valid.
     *
     * @return bool Returns true if no exception are thrown
     */
    public function validateConfiguration(array $configuration): bool
    {
        return !isset($configuration['categories']) || is_array($configuration['categories']);
    }

    /**
     * @return int[]
     */
    private function getIdList(string $key): array
    {
        $ids = json_decode((string) $this->configuration->get($key), true);

        return is_array($ids) ? $this->parseIds($ids) : [];
    }

    /**
     * Keeps unique positive integers only.
     *
     * @param array<int|string> $values
     *
     * @return int[]
     */
    private function parseIds(array $values): array
    {
        $ids = array_map(static fn ($value): int => (int) $value, $values);
        $ids = array_filter($ids, static fn (int $id): bool => $id > 0);

        return array_values(array_unique($ids));
    }
}
