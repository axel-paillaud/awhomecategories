/**
 * @author    Axelweb <contact@axelweb.fr>
 * @copyright 2026 Axelweb
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Axelweb
 */

// The category tree (CategoryChoiceTreeType) is rendered server-side but its
// expand/collapse behaviour lives in the admin theme ChoiceTree component,
// which core pages instantiate themselves.
// No enableAutoCheckChildren() here: a parent is a display choice on its own,
// checking it must not select its whole subtree.
$(function () {
  const { ChoiceTree } = window.prestashop.component;

  $('.js-choice-tree-container').each(function () {
    new ChoiceTree('#' + this.id);
  });
});
