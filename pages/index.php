<?php

/**
 * TreeStructure Addon.
 *
 * @author post[at]thomasgoellner[dot]de Thomas Goellner
 *
 * @package redaxo5
 *
 * @var rex_addon $this
 */

// basic request vars
$category_id = rex_request('category_id', 'int');
$article_id = rex_request('article_id', 'int');
$clang = rex_request('clang', 'int');
$ctype = rex_request('ctype', 'int');

// additional request vars
$artstart = rex_request('artstart', 'int');
$catstart = rex_request('catstart', 'int');
$edit_id = rex_request('edit_id', 'int');
$function = rex_request('function', 'string');

$info = '';
$warning = '';

$category_id = rex_category::get($category_id) ? $category_id : 0;
$article_id = rex_article::get($article_id) ? $article_id : 0;
$clang = rex_clang::exists($clang) ? $clang : rex_clang::getStartId();

// --------------------------------------------- Mountpoints

$mountpoints = rex::getUser()->getComplexPerm('structure')->getMountpoints();
if (count($mountpoints) == 1 && $category_id == 0) {
    // Nur ein Mointpoint -> Sprung in die Kategory
    $category_id = current($mountpoints);
}

// --------------------------------------------- Rechte prüfen
$KATPERM = rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id);

#echo rex_treestructure::isAllowed(15, 'category');
#die();

$stop = false;
if (rex_clang::count() > 1) {
    if (!rex::getUser()->getComplexPerm('clang')->hasPerm($clang)) {
        $stop = true;
        foreach (rex_clang::getAllIds() as $key) {
            if (rex::getUser()->getComplexPerm('clang')->hasPerm($key)) {
                $clang = $key;
                $stop = false;
                break;
            }
        }

        if ($stop) {
            echo rex_view::error('You have no permission to this area');
            exit;
        }
    }
} else {
    $clang = rex_clang::getStartId();
}

$context = new rex_context([
    'page' => 'treestructure',
    'clang' => $clang,
]);

// --------------------- Extension Point
echo rex_extension::registerPoint(new rex_extension_point('PAGE_STRUCTURE_HEADER_PRE', '', [
    'context' => $context,
]));

// --------------------------------------------- TITLE
echo rex_view::title(rex_i18n::msg('title_structure'));

// --------------------------------------------- Languages
echo rex_view::clangSwitchAsButtons($context);

// --------------------------------------------- Header
echo '<div class="rex-breadcrumb"><ol class="breadcrumb"><li>' . rex_i18n::msg('treestructure_title') . '</li></ol></div>';


// DO SOME OWN STUFF
$echo = '';

$translations = array(
    'type_in_name_to_confirm' => rex_i18n::msg('treestructure_type_in_name_to_confirm')
);

$echo = '<form id="treetructure--tree" class="treestructure" method="post" action="' . $context->getUrl() .'" data-url="' . $context->getUrl(['action' => 'jsontree']) .'" data-translations="' . htmlspecialchars(json_encode($translations)) . '"></form>';

// create option icons so we can clone them...
$echo.='<span class="treestructure--options">';
$echo.='<span class="treestructure--options--new-category fa fa-plus-square" title="' . rex_i18n::msg('add_category') . '">' . rex_i18n::msg('add_category') . '</span>';
$echo.='<span class="treestructure--options--new-article fa fa-plus-square-o" title="' . rex_i18n::msg('add_article') . '">' . rex_i18n::msg('article_add') . '</span>';
$echo.='<span class="treestructure--options--expand-all fa fa-caret-square-o-down" title="' . rex_i18n::msg('treestructure_expand_all') . '">' . rex_i18n::msg('treestructure_expand_all') . '</span>';
$echo.='<span class="treestructure--options--collapse-all fa fa-caret-square-o-up" title="' . rex_i18n::msg('treestructure_collapse_all') . '">' . rex_i18n::msg('treestructure_collapse_all') . '</span>';
$echo.='<span class="treestructure--options--edit rex-icon rex-icon-edit" title="' . rex_i18n::msg('change') . '">' . rex_i18n::msg('change') . '</span>';
$echo.='<span class="treestructure--options--view rex-icon rex-icon-view" title="' . rex_i18n::msg('show') . '">' . rex_i18n::msg('show') . '</span>';
$echo.='<span class="treestructure--options--status rex-icon rex-icon-online" title="' . rex_i18n::msg('treestructure_status') . '">' . rex_i18n::msg('treestructure_status') . '</span>';

$echo.='<span class="treestructure--options-extras">';
$echo.='<span class="treestructure--options--delete rex-icon rex-icon-delete" data-confirm="' . rex_i18n::msg('treestructure_confirm_deletion') . '" title="' . rex_i18n::msg('delete') . '">' . rex_i18n::msg('delete') . '</span>';
$echo.='<span class="treestructure--options--metadata rex-icon rex-icon-metainfo" title="' . rex_i18n::msg('metadata') . '">' . rex_i18n::msg('metadata') . '</span>';
$echo.='<span class="treestructure--options--functions rex-icon rex-icon-metafuncs" title="' . rex_i18n::msg('metafuncs') . '">' . rex_i18n::msg('metafuncs') . '</span>';
$echo.='</span>';
$echo.= '</span>';

// -------------- STATUS_TYPE Map
$catStatusTypes = rex_category_service::statusTypes();
$artStatusTypes = rex_article_service::statusTypes();

// --------------------------------------------- API MESSAGES
echo rex_api_function::getMessage();

// --------------------------------------------- KATEGORIE LISTE

$fragment = new rex_fragment();
$fragment->setVar('class', 'treestructure--categories', false);
$fragment->setVar('content', $echo, false);
echo $fragment->parse('core/page/section.php');
