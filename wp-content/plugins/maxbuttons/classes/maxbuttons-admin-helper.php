<?php
declare(strict_types=1);
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

/* Helper class for uniform elements in admin pages */

class maxAdmin
{
	protected static $tabs = null;

	public static function init()
	{
		add_action('mb-display-logo', array(maxUtils::namespaceit('maxAdmin'),'logo'));
		add_action('mb-display-title', array(maxUtils::namespaceit("maxAdmin"),'rate_us'), 20);
		add_action('mb-display-tabs', array(maxUtils::namespaceit('maxAdmin'),'tab_menu'));
		add_action('mb-display-ads', array(maxUtils::namespaceit('maxAdmin'), 'display_ads'));
		add_action('mb-display-pagination', array(maxUtils::namespaceit('maxAdmin'), 'display_pagination'), 10, 2);
		add_action('mb-display-collection-welcome', array(maxUtils::namespaceit('maxAdmin'), 'displayCollectionWelcome'));
	}

	public static function logo()
	{
		$version = self::getAdVersion();
		$url = self::getCheckoutURL();
	?>

			<?php printf(__('Upgrade to MaxButtons Pro today!  %sClick Here%s', 'maxbuttons'), '<a class="simple-btn" href="' . $url . '&utm_source=mbf-dash' . $version . '&utm_medium=mbf-plugin&utm_content=click-here&utm_campaign=cart' . $version . '" target="_blank">', '</a>' ) ?>


	<?php
	}

	public static function tab_items_init()
	{
			self::$tabs = array(
						"list" => array("name" =>  __('Buttons', 'maxbuttons'),
										 "link" => "page=maxbuttons-controller",
										 "active" => "maxbuttons-controller", ),
						"pro" => array( "name" => __('Upgrade to Pro', 'maxbuttons'),
										 "link" => "page=maxbuttons-pro",
										 "active" => "maxbuttons-pro",
										 ),
						"settings" => array("name" => __('Settings', 'maxbuttons'),
										 "link" => "page=maxbuttons-settings",
										 "active" => "maxbuttons-settings",
										 "userlevel" => 'manage_options'  ),
						"support" => array("name" => __('Support', 'maxbuttons'),
										 "link" => "page=maxbuttons-support",
										 "active" => "maxbuttons-support",
										 "userlevel" => 'manage_options'
										 )
			);

			if ( maxInstall::hasAddOn('socialshare'))
			{
				unset(self::$tabs['collection']);
			}
	}

	public static function tab_menu()
	{
		 self::tab_items_init();
	?>
			<h2 class="tabs">
				<span class="spacer"></span>
		<?php foreach (self::$tabs as $tab => $tabdata) {
			if (isset($tabdata["userlevel"]) && ! current_user_can($tabdata["userlevel"]))
				continue;

			$link = admin_url() . "admin.php?" . $tabdata["link"];
			$name = $tabdata["name"];
			$active = '';
			if ($tabdata["active"] == $_GET["page"])
				$active = "nav-tab-active";

				echo "<a class='nav-tab $active' href='$link'>$name</a>";

		}
		echo "</h2>";
	}

	public static function getAdversion()
	{
		$version = MAXBUTTONS_VERSION_NUM;
		$version = str_replace('.','',$version);
		return $version;

	}

	public static function getCheckoutURL()
	{
	 return $url = 'https://maxbuttons.com/checkout/?edd_action=add_to_cart&download_id=24035';
	}


	public static function do_review_notice () {

		$current_user_id = get_current_user_id();
		$version = MAXBUTTONS_VERSION_NUM;

		$review = get_user_meta( $current_user_id, 'maxbuttons_review_notice' , true );
			if ($review == '')
			{
			//$created = get_option("MBFREE_CREATED");
			//$show = time() + (7* DAY_IN_SECONDS);
			$show = time() + (1* DAY_IN_SECONDS); // Changed
			update_user_meta($current_user_id, 'maxbuttons_review_notice', $show);
			return;
			}

			$display_review = false;

			if ($review == 'off')
			{	return; // no show

			}
			elseif (is_numeric($review))
			{
				$now = time();
				if ($now > $review)
				{
				$display_review = true;

				}
			}

			// load style / script. It's seperated since it should show everywhere in admin.
			if ($display_review)
			{
					add_action( 'admin_notices', array( maxUtils::namespaceit('maxAdmin'), 'mb_review_notice'));
					// registered in admin scripts
					MB()->load_library('review_notice');
			}

	}

	public static function setReviewNoticeStatus()
	{
		$status = isset($_POST["status"]) ? sanitize_text_field($_POST["status"]) : '';
		$current_user_id = get_current_user_id();

		$updated = false;

		if ($status == 'off')
		{
			$updated = true;
			update_user_meta($current_user_id, 'maxbuttons_review_notice', 'off');

		}
		if ($status == 'later')
		{
			$updated = true;
			$later = time() + (14 * DAY_IN_SECONDS );

			update_user_meta($current_user_id, 'maxbuttons_review_notice', $later);
		}

		echo json_encode(array("updated" => $updated)) ;

		exit();
	}

	public static function display_ads()
	{
		$plugin_url = MB()->get_plugin_url();
		$ad_url = $plugin_url . '/images/ads/';
		$version = self::getAdVersion();
		$url = self::getCheckoutURL();
	?>

        <div class="ads image-ad">
		<a  href="<?php echo $url ?>&utm_source=mbf-dash<?php echo $version ?>&utm_medium=mbf-plugin&utm_content=MBF-sidebar&utm_campaign=cart<?php echo $version ?>" target="_blank" >
        	<img src="<?php echo $plugin_url ?>/images/max_ad.png" width="300">
            </a>
        </div>

				<div class='text-block ads'>
				<h1>New from MaxButtons :</h1>
				<h2><?php _e('Upgrade directly from this plugin', 'maxbuttons') ?></h2>
				 <p>Since version 9, it's much easier to upgrade to the PRO
					 <ol><li><a  href="<?php echo $url ?>&utm_source=mbf-dash<?php echo $version ?>&utm_medium=mbf-plugin&utm_content=MBF-sidebar&utm_campaign=cart<?php echo $version ?>" target="_blank" >Buy a license on MaxButtons.com</a></li>

					 <li><a href="<?php echo admin_url('admin.php?page=maxbuttons-pro') ?>">Enter license code straight in this plugin</a></li>
					 <li>Done!</li>
				 </ol>
				  </p>

					<h2>Link Manager</h2>
					<p>
						Keep track of your buttons, links and the pages where they are used. Includes simple view and click statistics.
					</p>

						<img src="<?php echo $plugin_url ?>/images/gopro/linkmanager.png" width="298">

				</div>



        <div class="ads image-ad">
        	<a href="http://www.maxbuttons.com/pricing/?utm_source=mbf-dash<?php echo $version ?>&utm_medium=mbf-plugin&utm_content=EBWG-sidebar-22&utm_campaign=inthecart<?php echo $version ?>" target="_blank"><img src="<?php echo $plugin_url ?>/images/ebwg_ad.png" /></a>

        </div>

        <div class="ads image-ad">
            <a href="https://wordpress.org/plugins/maxgalleria/?utm_source=mbf-dash<?php echo $version ?>&utm_medium=mbf_plugin&utm_content=MG_sidebar&utm_campaign=MG_promote" target="_blank">
            <img src="<?php echo $plugin_url ?>/images/mg_ad.png" /></a>
        </div>

        <?php
	}

	/** Display Rating Links
	*
	* 	Displays rating links via mb-display-title hook.
	*/
	public static function rate_us()
	{
		$output = '';

		$output .= "<div>";
		$output .= sprintf("Enjoying MaxButtons? Please %s rate us ! %s",
			"<a href='https://wordpress.org/support/view/plugin-reviews/maxbuttons#postform'>",
			"</a>"
			);
		$output .= "</div>";
		echo $output;
	}


	public static function display_pagination($page_args, $location = 'top')
	{

		$mbadmin =  MB()->getClass("admin");
		$pag = $mbadmin->getButtonPages($page_args);



		//extract($pag);
		self::show_pagination($pag, $location);

	}

	public static function show_pagination($args, $location)
	{
	  $first_url = $args['first_url'];
		$first = $args['first'];
		$base = $args['base'];
		$first_url = $args['first_url'];
		$last  = $args['last'];
		$last_url = $args['last_url'];
		$next_url = $args['next_url'];
		$prev_url = $args['prev_url'];
		$prev_page = $args['prev_page'];
		$next_page = $args['next_page'];
		$total = $args['total'];
		$current = $args['current'];

		if ($first == $last)
		{	return; }

	?>
	<div class='tablenav <?php echo $location ?>'>
		<div class="tablenav-pages">
		<span class="displaying-num"><?php echo $total ?> items</span>
		<span class="pagination-links">

		<?php if (! $first_url): ?>
		<a class="tablenav-pages-navspan button first-page disabled" href='#'>«</a>
		<?php else: ?>
			<a href="<?php echo $first_url ?>" data-page="1" title="<?php _e("Go to the first page","maxbuttons") ?>" class="tablenav-pages-navspan button first-page <?php if (!$first_url) echo "disabled"; ?>">«</a>
		<?php endif;  ?>

		<?php if (! $prev_url): ?>
		<a class="tablenav-pages-navspan button prev-page disabled" href='#'>‹</a>
		<?php else : ?>
			<a href="<?php echo $prev_url ?>" data-page="<?php echo $prev_page ?>" title="<?php _e("Go to the previous page","maxbuttons"); ?>" class="tablenav-pages-navspan button prev-page <?php if (!$prev_url) echo "disabled"; ?>">‹</a>
		<?php endif; ?>

		<span class="paging-input"><input data-url="<?php echo $base ?>" class='input-paging' data-current="<?php echo $current ?>" min="1" max="<?php echo $last ?>" type="number" name='paging-number' size="1" value="<?php echo $current ?>"> <?php _e("of","maxbuttons") ?> <span class="total-pages"><?php echo $last ?>
		</span></span>

		<?php if (! $next_url): ?>
			<a class="tablenav-pages-navspan button next-page disabled" href='#'>›</a>
		<?php else: ?>
			<a href="<?php echo $next_url ?>" data-page="<?php echo $next_page ?>" title="<?php _e("Go to the next page","maxbuttons") ?>" class=" tablenav-pages-navspan button next-page <?php if (!$next_url) echo "disabled"; ?>">›</a>
		<?php endif; ?>

		<?php if (! $last_url): ?>
		<a class="tablenav-pages-navspan button last-page disabled" href='#'>»</a></span>
	 	<?php else: ?>
	 		<a href="<?php echo $last_url ?>" data-page="<?php echo $last ?>" title="<?php _e("Go to the last page","maxbuttons") ?>" class="tablenav-pages-navspan button last-page <?php if (!$last_url) echo "disabled"; ?>">»</a></span>
	 	<?php endif; ?>

		</div> <!-- tablenav-pages -->
	</div> <!-- tablenav -->

	<?php
	}

    public static function mb_review_notice() {
	   if( current_user_can( 'manage_options' ) ) {  ?>

			 <?php /* OLD REVIEW NOTICE
			<div class="updated notice maxbuttons-notice">
		      <div class='review-logo'></div>
		      <div class='mb-notice'>
		      	<p class='title'><?php _e("Rate us Please!","maxbuttons"); ?></p>
		     	<p><?php _e("Your rating is the simplest way to support MaxButtons. We really appreciate it!","maxbuttons"); ?></p>

				  <ul class="review-notice-links">
				    <li> <span class="dashicons dashicons-smiley"></span><a data-action='off' href="javascript:void(0)"><?php _e("I've already left a review","maxbuttons"); ?></a></li>
				    <li><span class="dashicons dashicons-calendar-alt"></span><a data-action='later' href="javascript:void(0)"><?php _e("Maybe Later","maxbuttons"); ?></a></li>
				    <li><span class="dashicons dashicons-external"></span><a target="_blank" href="https://wordpress.org/support/view/plugin-reviews/maxbuttons?filter=5#postform"><?php _e("Sure! I'd love to!","maxbuttons"); ?></a></li>
				  </ul>
		      </div>
		      <a class="dashicons dashicons-dismiss close-mb-notice" href="javascript:void(0)" data-action='off'></a>

		  </div>
			*/ ?>


			<div class="updated notice maxbuttons-notice">
		      <div class='review-logo'></div>
		      <div class='mb-notice'>
		      	<p class='title'><?php _e("Is there a feature you would like for us to add to <b>MaxButtons</b>? <br> Let us know.","maxbuttons"); ?></p>
		     	<p><?php printf(__("Send your suggestions to %s","maxbuttons"), '<a href="mailto:support@maxfoundry.com?subject=Suggestion for MaxButtons">support@maxfoundry.com</a>'); ?></p>


		      </div>
		      <a class="dashicons dashicons-dismiss close-mb-notice" href="javascript:void(0)" data-action='off'></a>

		  </div>
		<?php
		}
	  }



	public static function displayCollectionWelcome()
	{
	?>
		<div class="collection welcome">
	<h2><?php _e("Welcome to MaxButtons Social Sharing", "maxbuttons"); ?></h2>
	<p><?php _e("Social Sharing sets are collections of buttons that are primarily used to promote your social media profiles on your site.","maxbuttons"); ?></p>
	<p><?php _e("MaxButtons comes with 5 terrific free sets of Social Sharing buttons for you to use: Notched Box Social Share, Modern Social Share, Round White Social Share, Social Share Squares, Minimalistic Share Buttons.  You can also add any other button you have made to a collection of Social Sharing buttons.","maxbuttons"); ?></p>

<p><?php _e("After clicking the Get Started link below you’ll come to the ‘Select your buttons’ page.  Here you will see the listing of free Social Sharing sets plus all of the buttons that you have on your site can be used in the collection that you are putting together.","maxbuttons"); ?></p>

<p><?php _e("You build your Social Sharing set by selecting the buttons you want in your collection.  Then click the Add selected buttons button in the lower right.  Aside from being included in your collection your selected buttons are now included with all of the other buttons on your site.  You can edit those buttons by going to the Buttons section in the Nav bar on the left.","maxbuttons"); ?></p>

<p><?php printf(__("By upgrading to %sMaxButtons Pro%s you get an 13 additional Social Sharing button sets along with the ability to build your own Social Sharing sets using your own icons, using Google Fonts in your Social Sharing buttons along with all of the features that come with our premium product.","maxbuttons"), "<a href='https://www.maxbuttons.com' target='_blank'>", "</a>"); ?></p>

<p><strong><?php _e("Click Get Started and we will have your social media icons up and running on your site super quick!","maxbuttons"); ?></strong></p>

<p><?php _e("The Max Foundry Team", "maxbuttons"); ?></p>

	<p><a class="page-title-action " href="<?php echo admin_url() ?>admin.php?page=maxbuttons-collections&action=edit&collection=social">
	<?php _e("Get Started","maxbuttons"); ?></a></p>

	</div>

	<?php
	}

} // class
