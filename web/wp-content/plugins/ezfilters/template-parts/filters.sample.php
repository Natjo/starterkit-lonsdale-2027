<?php
	global $ezFilters;
	$postType = $ezFilters->getPostType();

	if (empty($ezFilters->datas[$postType]['filters'])) {
		return;
	}

	$currentSearch = $ezFilters->getCurrentSearch();
?>

<form
	class="container ezfilters-filters js-ezfilters-form"
	action="<?= get_post_type_archive_link($postType) ?>"
	data-module="ezfilters"
	data-post-type="<?= $postType ?>"
>
	<ul>
		<?php foreach ($ezFilters->datas[$postType]['filters'] as $slug => $filter): ?>
			<div>
				<label for="<?= $slug ?>">
					<?= $filter['title'] ?>
				</label>

				<?php if ($filter['type'] == "keywords"): ?>
					<input
						type="text"
						id="<?=$slug ?>"
						name="<?= $slug ?>"
						<?php if (!empty($currentSearch[$slug])): ?>
							value="<?= $currentSearch[$slug] ?>"
						<?php endif ?>
					>
				<?php else: ?>
					<ul>
						<?php foreach ($filter['datas'] as $data): ?>
							<li>
								<input
									type="<?= $filter['multiple_choice'] ? 'checkbox' : 'radio' ?>"
									id="<?= $slug . '-' . $data['value'] ?>"
									name="<?= $slug ?>[]"
									value="<?= $data['value'] ?>"
									<?php if (in_array($data['value'], $currentSearch[$slug])): ?>
										checked
									<?php endif ?>
								>
								<label for="<?= $slug . '-' . $data['value'] ?>">
									<?= $data['title'] ?>
								</label>
							</li>
						<?php endforeach ?>
					</ul>
				<?php endif ?>
			</div>
		<?php endforeach ?>
	</ul>

	<button type="submit">
		Valider
	</button>

	<button type="button" class="js-ezfilters-reset-btn">
		Réinitialiser
	</button>
</form>

<style>
	.ezfilters-filters {
		display: flex;
		align-items: center;
		justify-content: center;
		margin-top: 4rem;
		flex-direction:  column;
		gap: 1rem;

		& > ul {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 1rem;
			width: 100%;

			& > * {
				width: 25%;
				display: flex;
				flex-direction: column;

				& > * {
					width: 100%;
				}

				ul {
					display: flex;
					gap: 2rem;
				}
			}
		}
	}
</style>