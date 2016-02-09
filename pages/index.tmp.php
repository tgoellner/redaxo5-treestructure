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
    'category_id' => $category_id,
    'article_id' => $article_id,
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
if (count($mountpoints) > 0 && $category_id == 0) {
}
else if($category_id == 0) {
    $mountpoints = rex_category::getRootCategories();
}
else {
    $mountpoints = [$category_id];
}

foreach($mountpoints as $category) {
    if (! $category instanceof rex_category) {
        $category = rex_category::get(is_object($category) ? $category->getId() : (int) $category);
    }

    if (! $category instanceof rex_category) {
        continue;
    }
 ?>

<div class="category ajax-result">
    <div class="category-heading"><?php echo $category->getName(); ?></div><?php
        $children = $category->getChildren();
        $articles = $category->getArticles();

    if(!empty($children) || (!empty($articles) && count($articles)>1)): ?>
    <div class="category-body">
        <?php if(!empty($children)): ?>
        <div class="category--children">
        <?php foreach($children as $child): ?>
            <div class="category--child">
                <a href="?page=treestructure&category_id=<?php echo $child->getId(); ?>">
                    <?php echo $child->getName(); ?>
                </a>
            </div>
        <?php endforeach; ?>
        </div><!-- .category--children //--><?php endif; ?>

        <?php if(!empty($articles)): ?>
        <div class="category--articles">
        <?php foreach($articles as $article): ?>
            <div class="category--article">
                <?php echo $child->getName(); ?>
            </div>
        <?php endforeach; ?>
        </div><!-- .category--articles //--><?php endif; ?>
    </div><?php endif; ?>
</div><!-- .category //-->

<?php }
$echo = '';
/*
if (count($mountpoints) > 0 && $category_id == 0) {
    foreach($mountpoints as $mp) {
        $echo.= rex_treestructure::getCategoryHtml($mp, true);
    }
} else {
    $echo.= rex_treestructure::getCategoryHtml($category_id, true);
}

# $echo.= rex_treestructure::getCategoryHtml($category_id, true);
*/

// -------------- STATUS_TYPE Map
$catStatusTypes = rex_category_service::statusTypes();
$artStatusTypes = rex_article_service::statusTypes();

// --------------------------------------------- API MESSAGES
echo rex_api_function::getMessage();

// --------------------------------------------- KATEGORIE LISTE

$fragment = new rex_fragment();
$fragment->setVar('heading', rex_i18n::msg('treestructure_categories'), false);
$fragment->setVar('content', $echo, false);
echo $fragment->parse('core/page/section.php');
