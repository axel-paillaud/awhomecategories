# AwHomeCategories

Displays a selection of categories on the home page (`displayHome`), as a grid of links with an optional image. By [Axelweb](https://axelweb.fr).

Built from [awmodulebase](https://github.com/axel-paillaud/awmodulebase).

## Requirements

- PrestaShop 8.0+
- PHP 8.1+
- Composer (`composer install --no-dev` in the module directory before installing)

## Configuration

Modules > Home categories > Configure (Symfony route `awhomecategories_form_configuration`).

A single field, a category tree (`CategoryChoiceTreeType`): the checked categories are displayed on the home page in the order of the category tree. The selection is stored as a JSON array of ids in `AWHOMECATEGORIES_CATEGORIES`. On install, the active children of the home category are selected.

At render time the module skips categories that are inactive or not accessible to the current customer group, so it behaves like `ps_mainmenu` on a private shop: a visitor who cannot see the categories does not see the block at all.

## Images

A card is illustrated with the category thumbnail (Catalog > Categories > edit > thumbnail), using the theme's `default_lg` image size. When no selected category has a thumbnail, the block falls back to a text-only list so the grid stays consistent.

## Front template

`views/templates/hook/displayHome.tpl` follows the Hummingbird conventions: a full-width `<section class="awhomecategories">` wrapping its own `.container`, BEM classes, no JavaScript, no CSS shipped by the module. Style it in the theme (`src/scss/prestashop/modules/`), or override the template in `themes/<theme>/modules/awhomecategories/views/templates/hook/displayHome.tpl`.

Variables available to the template:

| Variable | Content |
|---|---|
| `$categories` | List of `CategoryLazyArray` (`id`, `name`, `url`, `description`, `thumbnail.bySize.*`, `image.bySize.*`) |

## Project structure

```
awhomecategories/
├── awhomecategories.php      # Main module class (hook, category loading)
├── composer.json
├── config/
│   ├── routes.yml            # Symfony route of the configuration page
│   └── services.yml          # Symfony DI services
├── src/
│   ├── Controller/AdminConfigurationController.php
│   └── Form/
│       ├── GeneralDataConfiguration.php   # Read/write ps_configuration
│       ├── GeneralFormDataProvider.php
│       └── GeneralFormType.php            # Category tree field
├── translations/fr-FR/       # XLIFF translations (new translation system)
└── views/
    ├── js/admin/form.js      # Instantiates the admin ChoiceTree component
    └── templates/
        ├── admin/form.html.twig
        └── hook/displayHome.tpl
```

## License

[Academic Free License 3.0 (AFL-3.0)](https://opensource.org/licenses/AFL-3.0)
