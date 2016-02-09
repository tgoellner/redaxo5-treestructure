<?php
	$hasChildren = !empty($cat['children']) || (!empty($cat['articles']) && count($cat['articles'])>1);
	$isRoot = ! (int) $cat['id'] > 0;

	$articleHtml = '';
	if(!empty($cat['articles'])) {
		$articleHtml.= '<div class="category--articles list-group">';
		foreach($cat['articles'] as $article) {
			if(!$article->isStartArticle()) {
				$articleHtml.= rex_treestructure::getArticleHtml($article, true);
			}
		}
		$articleHtml.= '</div><!-- .category--articles //-->';
	}

?>

<?php if((int) $cat['id'] <= 0): ?>

<div class="panel panel-default category is--root">
	<div class="panel-heading">
        <?php echo $cat['name']; ?>
      </div>
	
	<?php if($hasChildren): ?>
	<div class="panel-body">
		<?php if(!empty($cat['children'])): ?>
		<div class="list-group category--children">
		<?php foreach($cat['children'] as $child): ?>
			<?php rex_treestructure::getCategoryHtml($child); ?>
		<?php endforeach; ?>
		</div><!-- .category--children //--><?php endif; ?>

		<?php if(!empty($articleHtml)) echo $articleHtml; ?>
	</div><?php endif; ?>
</div><!-- .category //-->

<?php else: ?>

<div class="list-group-item category">
	<div class="list-group-heading">
		<?php if($hasChildren): ?>
        <a data-toggle="collapse" href="#category-<?php echo $cat['id']; ?>">
    	<?php endif; ?>
        <?php echo $cat['name']; ?></a>
		<?php if($hasChildren): ?>
        </a>
    	<?php endif; ?>
      </div>
	
	<?php if($hasChildren): ?>
	<div id="category-<?php echo $cat['id']; ?>" class="collapse">
		<?php if(!empty($cat['children'])): ?>
		<div class="list-group category--children">
		<?php foreach($cat['children'] as $child): ?>
			<?php rex_treestructure::getCategoryHtml($child); ?>
		<?php endforeach; ?>
		</div><!-- .category--children //--><?php endif; ?>

		<?php if(!empty($articleHtml)) echo $articleHtml; ?>
	</div><?php endif; ?>
</div><!-- .category //-->

<?php endif; ?>