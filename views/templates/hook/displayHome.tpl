{**
 * @author    Axelweb <contact@axelweb.fr>
 * @copyright 2026 Axelweb
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Axelweb
 *
 * Home categories block. Markup follows the Hummingbird conventions (BEM, full-width
 * section wrapping its own .container); styling is left to the theme.
 *}
{$componentName = 'awhomecategories'}

{* Cards get an image only when at least one category has a thumbnail, so the grid stays consistent *}
{$withImages = false}
{foreach $categories as $category}
  {if !empty($category.thumbnail)}
    {$withImages = true}
    {break}
  {/if}
{/foreach}

<section class="{$componentName}" aria-labelledby="{$componentName}-title">
  <div class="container">
    <h2 class="section-title" id="{$componentName}-title">{l s='Our product ranges' d='Modules.Awhomecategories.Shop'}</h2>

    <ul class="{$componentName}__list{if $withImages} {$componentName}__list--with-images{/if}">
      {foreach $categories as $category}
        <li class="{$componentName}__item">
          <a class="{$componentName}__link" href="{$category.url}">
            {if $withImages}
              <span class="{$componentName}__media">
                {if !empty($category.thumbnail.bySize.default_lg.url)}
                  {$image = $category.thumbnail.bySize.default_lg}
                  <picture>
                    {if isset($image.sources.avif)}
                      <source srcset="{$image.sources.avif}" type="image/avif">
                    {/if}
                    {if isset($image.sources.webp)}
                      <source srcset="{$image.sources.webp}" type="image/webp">
                    {/if}
                    <img
                      class="{$componentName}__image"
                      src="{$image.url}"
                      width="{$image.width}"
                      height="{$image.height}"
                      alt=""
                      loading="lazy"
                    >
                  </picture>
                {/if}
              </span>
            {/if}
            <span class="{$componentName}__name">{$category.name|escape:'html':'UTF-8'}</span>
          </a>
        </li>
      {/foreach}
    </ul>
  </div>
</section>
