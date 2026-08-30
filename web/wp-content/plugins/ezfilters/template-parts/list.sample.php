<?php
	global $wp_query;
	$query = isset($args['query']) ? $args['query'] : $wp_query;

	global $ezFilters;
	$currentSearch = $ezFilters->getCurrentSearch();
	$isCurrentSearchEmpty = $ezFilters->isCurrentSearchEmpty();
?>

<?php if (!defined('DOING_AJAX') || !DOING_AJAX): ?>
	<div class="js-ezfilters-result">
<?php endif ?>
	<div class="container ezfilters-list">
		<p>
			<?php if (!$isCurrentSearchEmpty): ?>
				<?php if ($query->found_posts == 0): ?>
					Aucun élément ne correspondent à votre recherche
				<?php elseif($query->found_posts == 1): ?>
					1 élément correspond à votre recherche
				<?php else: ?>
					<?= $query->found_posts ?> éléments correspondent à votre recherche
				<?php endif ?>
			<?php else: ?>
				<?php if ($query->found_posts == 0): ?>
					Aucun élément
				<?php elseif($query->found_posts == 1): ?>
					1 élément
				<?php else: ?>
					<?= $query->found_posts ?> éléments
				<?php endif ?>
			<?php endif ?>
		</p>
		<ul>
			<?php foreach ($query->posts as $item): ?>
				<li>
					<a href="<?= get_the_permalink($item) ?>">
						<?= get_the_title($item) ?>
					</a>
				</li>
			<?php endforeach ?>
		</ul>
	</div>

	<?php $ezFilters->renderPaging($args); ?>

<?php if (!defined('DOING_AJAX') || !DOING_AJAX): ?>
	</div>
<?php endif ?>

<style>
	.ezfilters-list {
		p {
			margin-bottom: 2rem;
			margin-top: 4rem;
		}

		ul {
			display: flex;
			flex-wrap: wrap;
			margin-top: 4rem;
			margin-bottom: 4rem;
			list-style: initial;

			& > * {
				width: 100%;
			}
		}
	}
</style>