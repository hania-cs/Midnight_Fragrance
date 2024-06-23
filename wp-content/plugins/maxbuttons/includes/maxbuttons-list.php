<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

?>


<?php
$page_title = __("Overview","maxbuttons");
$action = $this->getButton('add-new', array('class' => 'page-title-action add-new-h2')); // "<a class='page-title-action add-new-h2' href='" . $this->getButtonLink() . "'>" . __('Add New', 'maxbuttons') . "</a>";
$this->mbadmin->get_header(array("title" => $page_title, "title_action" => $action));
 ?>

			<div class="form-actions">
				<?php echo $this->getButton('add-new', array('class' => 'button-primary')); ?>
			</div>

			<?php foreach ($this->messages as $message) { ?>
				<div class="mb-notice mb-message"><?php echo $message ?></div>
			<?php }
			?>


			<p class="status">
			<?php
				$url = $this->getListLink(); // admin_url() . "admin.php?page=maxbuttons-controller&action=list";
				$trash_url =  $this->getListLink('trash'); // $url . "&view=trash";

				if ($view->listView == 'trash')
				{
					$all_line = "<strong><a href='$url'>"  .  __('All', 'maxbuttons') . "</strong></a>";
					$trash_line = __("Trash", "maxbuttons");
				}
				else
				{
					$all_line = __("All","maxbuttons");
					$trash_line = "<a href='$trash_url'>" . __("Trash","maxbuttons") . "</strong></a>";
				}
			?>
				 <?php echo $all_line ?><span class="count"> (<?php echo $view->published_buttons_count ?>)</span>

				<?php if ($view->trashed_buttons_count > 0) { ?>
					<span class="separator">|</span>
					<?php echo $trash_line ?> <span class="count">(<?php echo $view->trashed_buttons_count ?>)</span>
				<?php } ?>
			</p>
			<?php
			do_action("mb-display-meta");

			?>
			<form method="post">
				<?php wp_nonce_field("button-copy","copy_nonce"); ?>
				<?php wp_nonce_field("button-delete","delete_nonce"); ?>
				<?php wp_nonce_field('button-trash', 'trash_nonce'); ?>
				<?php wp_nonce_field('button-restore', 'restore_nonce'); ?>
				<?php wp_nonce_field('button-empty-trash', 'empty-trash_nonce'); ?>

				<?php if (isset($page_args['paged'])) : ?>
						<input type="hidden" name="paged" value="<?php echo $view->pageArgs['paged'] ?>" />
				<?php endif; ?>

				<input type="hidden" name="view" value="<?php echo $view->listView ?>" />
				<?php wp_nonce_field("mb-list","mb-list-nonce");  ?>

				<select name="bulk-action-select" id="bulk-action-select">
					<option value=""><?php _e('Bulk Actions', 'maxbuttons') ?></option>
				<?php if ($view->listView == 'all'): ?>

					<option value="trash"><?php _e('Move to Trash', 'maxbuttons') ?></option>
				<?php endif;
					if ($view->listView == 'trash'): ?>
						<option value="restore"><?php _e('Restore', 'maxbuttons') ?></option>
						<option value="delete"><?php _e('Delete Permanently', 'maxbuttons') ?></option>
				<?php endif; ?>
				</select>
				<input type="submit" class="button" value="<?php _e('Apply', 'maxbuttons') ?>" />

				<?php if ($view->listView == 'trash'): ?>
					<button type="button" class='button alignright' value='empty-trash' data-buttonaction='empty-trash' data-confirm="<?php _e('Permanently delete all buttons in trash. Are you sure?', 'maxbuttons-pro') ?>"><?php _e('Empty Trash', 'maxbuttons'); ?></button>
				<?php endif; ?>
	 			<?php do_action("mb-display-pagination", $view->pageArgs, 'top'); ?>



<?php  // Sorting
			$link_order =  ($view->pageArgs['order'] == "DESC") ? "ASC" : 'DESC';

			$name_sort_url = esc_url(add_query_arg(array(
				"orderby" => "name",
				"order" => $link_order
			)));
			$id_sort_url = esc_url(add_query_arg(array(
				"orderby" => "id",
				"order" => $link_order
			)));

			$sort_arrow = ( strtolower($view->pageArgs["order"]) == 'desc') ? 'dashicons-arrow-down' : 'dashicons-arrow-up'
?>

				<div class="button-list preview-buttons">

					<div class="heading">
						<span class='col col_check'><input type="checkbox" name="bulk-action-all" id="bulk-action-all" /></span>
						<span class='col col_button'>
							<a href="<?php echo $id_sort_url ?>">
							<?php _e('Button', 'maxbuttons') ?>
							<?php if ($view->pageArgs["orderby"] == 'id')
								 echo "<span class='dashicons $sort_arrow'></span>";
							?>
							</a>
						</span>
						<span class="col col_name manage-column column-name sortable <?php echo strtolower($link_order) ?>">
							<a href="<?php echo $name_sort_url ?>">
							<span><?php _e('Name and Description', 'maxbuttons') ?></span>
							<?php if ($view->pageArgs["orderby"] == 'name')
								 echo "<span class='dashicons $sort_arrow'></span>";
							?>

							</a>
						</span>
						<span class='col col_shortcode'><?php _e('Shortcode', 'maxbuttons') ?></span>
					</div> <!-- heading -->

					<?php
						foreach ($view->published_buttons as $b):
						$id = $b['id'];

						if($view->listView == 'trash')
							$this->button->set($id,'','trash');
						else
							$this->button->set($id);

							$this->view->button_id = $id;

					?>
						<div class='button-row'>
						<span class="col col_check"><input type="checkbox" name="button-id[]" id="button-id-<?php echo $id ?>" value="<?php echo $id ?>" /></span>
						<span class="col col_button"><div class="shortcode-container">
										<?php
										$this->button->display( array("mode" => "preview") );
										?>
								</div>
								<div class="actions">

								<?php if($view->listView == 'all') : ?>
								<a href="<?php echo $this->getButtonLink($id); ?>"><?php _e('Edit', 'maxbuttons') ?></a>
									<span class="separator">|</span>

									<?php echo $this->getButton('copy', array('class' => 'maxmodal')); ?>

									<span class="separator">|</span>
									<?php echo $this->getButton('trash', array('class' => 'maxmodal')); ?>

								<?php endif;
								if ($view->listView == 'trash'):
								?>
								<a href="javascript:void(0);" data-buttonaction='restore' data-buttonid="<?php echo $id ?>"><?php _e('Restore', 'maxbuttons') ?></a>
								<span class="separator">|</span>
								<a href="javascript:void(0);" data-buttonaction='delete' data-buttonid="<?php echo $id ?>"><?php _e('Delete Permanently', 'maxbuttons') ?></a>
								<?php endif; ?>
								</div>


						</span>
						<span class="col col_name"><a class="button-name" href="<?php echo $this->getButtonLink($id); ?>"><?php echo $this->button->getName() ?></a>
									<br />
									<p><?php echo $this->button->getDescription() ?></p>
									<p><?php echo $this->getButtonScreenInfo(); ?></p>
						</span>
						<span class="col col_shortcode">[maxbutton id="<?php echo $id ?>"] <br /><strong><?php _e('or', 'maxbuttons'); ?></strong><br />
									[maxbutton name="<?php echo $this->button->getName() ?>"]

									<?php
									if ($this->button->getUpdated(false) > 0) : ?>
										<span class='last-update'>Updated <?php echo $this->button->getUpdated(); ?></span>
									<?php endif; ?>
								</span>
						</div>
					<?php endforeach;

					// buttons ?>

				</div> <!-- button-list -->
			</form>

	<div class=''>
			<?php
			if (count($view->published_buttons) == 0):
					include('maxbuttons-welcome.php');
			endif;
			?>
	</div>


 			<?php do_action("mb-display-pagination", $view->pageArgs, 'bottom'); ?>



	</div>
	<div class="ad-wrap">
		<?php do_action("mb-display-ads"); ?>
	</div>

<?php $this->mbadmin->get_footer(); ?>
