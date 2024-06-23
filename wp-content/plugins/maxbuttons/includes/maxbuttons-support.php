<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

//$theme = wp_get_theme();
//$browser = maxbuttons_get_browser();

function maxbuttons_system_label($label, $value, $spaces_between) {
	$output = "<label>$label</label>";
	return "<div class='info'>" . $output . trim($value) . "</div>" ;
}

?>
<?php
$support_link = apply_filters("mb-support-link", 'http://wordpress.org/support/plugin/maxbuttons');

$admin = MB()->getClass('admin');
$page_title = __("Support","maxbuttons");
$action = "<a href='$support_link' class='page-title-action add-new-h2' target='_blank'>" . __("Go to support","maxbuttons") . "</a>";
$admin->get_header(array("title" => $page_title, "title_action" => $action) );
?>

		<div class='support tiles'>
				<div>
					<a href='https://wordpress.org/plugins/maxbuttons/#faq' target="_blank">Frequently Asked Questions</a>
				</div>

				<div><?php printf(__('%sSupport Forums%s.', 'maxbuttons'), "<a href='$support_link' target='_blank'>" , '</a>') ?></div>

		</div>

    		<h4><?php _e('You may be asked to provide the information below to help troubleshoot your issue.', 'maxbuttons') ?></h4>

    <div class='system_info'>
----- Begin System Info ----- <br />


<?php echo maxbuttons_system_label('WordPress Version:', get_bloginfo('version'), 4) ?>

<?php echo maxbuttons_system_label('PHP Version:', PHP_VERSION, 10) ?>

<?php
	global $wpdb;
	$mysql_version = $wpdb->db_version();

  echo maxbuttons_system_label('MySQL Version:', $mysql_version, 8) ?>

<?php echo maxbuttons_system_label('Web Server:', $_SERVER['SERVER_SOFTWARE'], 11) ?>

<?php echo maxbuttons_system_label('WordPress URL:', get_bloginfo('wpurl'), 8) ?>

<?php echo maxbuttons_system_label('Home URL:', get_bloginfo('url'), 13) ?>

<?php echo maxbuttons_system_label('PHP cURL Support:', function_exists('curl_init') ? 'Yes' : 'No', 5) ?>

<?php echo maxbuttons_system_label('PHP GD Support:', function_exists('gd_info') ? 'Yes' : 'No', 7) ?>
<?php echo maxbuttons_system_label('PHP Memory Limit:', ini_get('memory_limit'), 5) ?>

<?php echo maxbuttons_system_label('PHP Post Max Size:', ini_get('post_max_size'), 4) ?>

<?php echo maxbuttons_system_label('PHP Upload Max Size:', ini_get('upload_max_filesize'), 2) ?>

<?php echo maxbuttons_system_label('WP_DEBUG:', defined('WP_DEBUG') ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set', 13) ?>
<?php echo maxbuttons_system_label('Multi-Site Active:', is_multisite() ? 'Yes' : 'No', 4) ?>

<?php echo maxbuttons_system_label('Operating System:', $view->browser['platform'], 5) ?>
<?php echo maxbuttons_system_label('Browser:', $view->browser['name'] . ' ' . $view->browser['version'], 14) ?>
<?php echo maxbuttons_system_label('User Agent:', $view->browser['user_agent'], 11) ?>

Active Theme:
<?php echo maxbuttons_system_label('-', $view->theme->get('Name') . ' ' . $view->theme->get('Version'), 1) ?>
<?php echo maxbuttons_system_label('', $view->theme->get('ThemeURI'), 2) ?>

Active Plugins:
<?php
//$plugins = get_plugins();
//$active_plugins = get_option('active_plugins', array());

foreach ($view->plugins as $plugin_path => $plugin) {
	// Only show active plugins
	if (in_array($plugin_path, $view->active_plugins)) {
		echo maxbuttons_system_label('-', $plugin['Name'] . ' ' . $plugin['Version'], 1);

		if (isset($view->plugin['PluginURI'])) {
			echo maxbuttons_system_label('', $view->plugin['PluginURI'], 2);
		}

		echo "\n";
	}
}
?>
----- End System Info -----
 </div>
        </div>
        <div class="ad-wrap">
     		<?php do_action("mb-display-ads"); ?>
    </div>

<?php $admin->get_footer(); ?>
