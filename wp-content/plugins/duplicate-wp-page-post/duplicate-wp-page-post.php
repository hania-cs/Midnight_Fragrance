<?php
/*
Plugin Name: Duplicate Page and Post
Plugin URI: https://wordpress.org/plugins/duplicate-wp-page-post/
Description: Quickly clone a page, post or custom post and supports Gutenberg.
Author: Arjun Thakur
Author URI: https://profiles.wordpress.org/arjunthakur#content-plugins
Version: 2.9.4
License: GPLv2 or later
Text Domain: dpp_wpp_page
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! defined( 'DPP_BASE_NAME' ) ) {

	define( 'DPP_BASE_NAME', plugin_basename( __FILE__ ) );
}

if ( ! class_exists( 'dcc_dpp_wpp_page' ) ):
	class dpp_wpp_page {

		/*AutoLoad Hooks*/
		public function __construct() {
			$opt = get_option( 'dpp_wpp_page_options' );
			register_activation_hook( __FILE__, array( &$this, 'dpp_wpp_page_install' ) );
			add_action( 'admin_menu', array( &$this, 'dpp_page_options_page' ) );
			add_filter( 'plugin_action_links', array( &$this, 'dpp_settings_link' ), 10, 2 );
			add_action( 'admin_action_dt_dpp_post_as_draft', array( &$this, 'dt_dpp_post_as_draft' ) );
			add_filter( 'post_row_actions', array( &$this, 'dt_dpp_post_link' ), 10, 2 );
			add_filter( 'page_row_actions', array( &$this, 'dt_dpp_post_link' ), 10, 2 );
			if ( isset( $opt['dpp_posteditor'] ) && $opt['dpp_posteditor'] == 'gutenberg' ) {
				add_action( 'admin_head', array( &$this, 'dpp_wpp_button_guten' ) );
			} else {
				add_action( 'post_submitbox_misc_actions', array( &$this, 'dpp_wpp_page_custom_button' ) );
			}
			add_action( 'wp_before_admin_bar_render', array( &$this, 'dpp_wpp_page_admin_bar_link' ) );
		}


		/*Activation plugin Hook*/
		public function dpp_wpp_page_install() {
			$defaultsettings = array(
				'dpp_post_status'     => 'draft',
				'dpp_post_redirect'   => 'to_list',
				'dpp_post_suffix'     => '',
				'dpp_posteditor'      => 'classic',
				'dpp_post_link_title' => '',
			);
			$opt             = get_option( 'dpp_wpp_page_options' );
			if ( ! $opt['dpp_post_status'] ) {
				update_option( 'dpp_wpp_page_options', $defaultsettings );
			}
		}


		/* Page Title and Dashboard Menu (Setting options) */
		public function dpp_page_options_page() {
			add_options_page( __( 'Duplicate Page and Post', 'dpp_wpp_page' ), __( 'Duplicate post', 'dpp_wpp_page' ), 'manage_options', 'dpp_page_settings', array(
				&$this,
				'dpp_page_settings'
			) );
		}

		/*Include plugin setting file*/
		public function dpp_page_settings() {
			if ( current_user_can( 'manage_options' ) ) {
				include( 'duplicate-wp-page-post-setting.php' );
			}
		}

		/*Important function*/
		public function dt_dpp_post_as_draft() {
			$nonce   = sanitize_text_field( $_REQUEST['nonce'] );
            $getpost = sanitize_text_field( $_GET['post'] );
			$post_id = ( isset( $getpost ) ? intval( $getpost ) : intval( $getpost ) );


			if ( wp_verify_nonce( $nonce, 'dt-duplicate-page-' . $post_id ) && current_user_can( 'edit_posts' ) ) {
				global $wpdb;

				/*sanitize_GET POST REQUEST*/
				//$post_copy = sanitize_text_field( $_POST["post"] );
				//$get_copy = sanitize_text_field( $_GET['post'] );
				$request_copy = sanitize_text_field( $_REQUEST['action'] );

				$opt    = get_option( 'dpp_wpp_page_options' );
				$suffix = ! empty( $opt['dpp_post_suffix'] ) ? ' -- ' . esc_attr( $opt['dpp_post_suffix'] ) : '';

				$post_status = ! empty( $opt['dpp_post_status'] ) ? esc_attr( $opt['dpp_post_status'] ) : 'draft';
				$redirectit  = ! empty( $opt['dpp_post_redirect'] ) ? esc_attr( $opt['dpp_post_redirect'] ) : 'to_list';

				if ( ! ( isset( $getpost ) || isset( $getpost ) || ( isset( $request_copy ) && 'dt_dpp_post_as_draft' == $request_copy ) ) ) {
					wp_die( 'No post!' );
				}
				$returnpage = '';

				$post = get_post( $post_id );

				$current_user    = wp_get_current_user();
				$new_post_author = $current_user->ID;

				/*Create the post Copy */
				if ( isset( $post ) && $post != null ) {
					/* Post data array */
					$args        = array(
						'comment_status' => $post->comment_status,
						'ping_status'    => $post->ping_status,
						'post_author'    => $new_post_author,
						'post_content'   => ( isset( $opt['dpp_posteditor'] ) && $opt['dpp_posteditor'] == 'gutenberg' ) ? wp_slash( $post->post_content ) : $post->post_content,
						'post_excerpt'   => $post->post_excerpt,
						//'post_name' => $post->post_name,
						'post_parent'    => $post->post_parent,
						'post_password'  => $post->post_password,
						'post_status'    => $post_status,
						'post_title'     => $post->post_title . $suffix,
						'post_type'      => $post->post_type,
						'to_ping'        => $post->to_ping,
						'menu_order'     => $post->menu_order

					);
					$new_post_id = wp_insert_post( $args );


					$taxonomies = array_map( 'sanitize_text_field', get_object_taxonomies( $post->post_type ) );
					if ( ! empty( $taxonomies ) && is_array( $taxonomies ) ):
						foreach ( $taxonomies as $taxonomy ) {
							$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
							wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
						}
					endif;

					$post_meta_infos = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=%d", $post_id ) );
					if ( count( $post_meta_infos ) != 0 ) {
						$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
						foreach ( $post_meta_infos as $meta_info ) {

							$meta_key        = sanitize_text_field( $meta_info->meta_key );
							$meta_value      = addslashes( $meta_info->meta_value );
							$sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
						}
						$sql_query .= implode( " UNION ALL ", $sql_query_sel );
						$wpdb->query( $sql_query );
					}

					/*choice redirect */
					if ( $post->post_type != 'post' ):$returnpage = '?post_type=' . $post->post_type; endif;
					if ( ! empty( $redirectit ) && $redirectit == 'to_list' ):esc_url_raw( wp_redirect( admin_url( 'edit.php' . $returnpage ) ) );
                    elseif ( ! empty( $redirectit ) && $redirectit == 'to_page' ):esc_url_raw( wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) ) );
					else:
						wp_redirect( esc_url_raw( admin_url( 'edit.php' . $returnpage ) ) );
					endif;
					exit;
				} else {
					wp_die( 'Error! Post creation failed: ' . $post_id );
				}
			} else {
				wp_die( 'Security check issue, Please try again.' );
			}
		}


		/*Add link to action*/
		public function dt_dpp_post_link( $actions, $post ) {
			$opt         = get_option( 'dpp_wpp_page_options' );
			$link_title  = ! empty( $opt['dpp_post_link_title'] ) ? esc_attr( $opt['dpp_post_link_title'] ) : 'Duplicate';
			$opt         = get_option( 'dpp_wpp_page_options' );
			$post_status = ! empty( $opt['dpp_post_status'] ) ? esc_attr( $opt['dpp_post_status'] ) : 'draft';
			if ( current_user_can( 'edit_posts' ) ) {
				$actions['dpp'] = '<a href="admin.php?action=dt_dpp_post_as_draft&amp;post=' . $post->ID . '&amp;nonce=' . wp_create_nonce( 'dt-duplicate-page-' . $post->ID ) . '" title="Clone this as ' . $post_status . '" rel="permalink">' . $link_title . '</a>';
			}

			return $actions;
		}

		/*Add link to edit Post*/
		public function dpp_wpp_page_custom_button() {
			$opt        = get_option( 'dpp_wpp_page_options' );
			$link_title = ! empty( $opt['dpp_post_link_title'] ) ? esc_attr( $opt['dpp_post_link_title'] ) : 'Duplicate';
			global $post;
			$opt         = get_option( 'dpp_wpp_page_options' );
			$post_status = ! empty( $opt['duplicate_post_status'] ) ? esc_attr( $opt['duplicate_post_status'] ) : 'draft';

			$html = '<div id="major-publishing-actions">';
			$html .= '<div id="export-action">';
			$html .= '<a href="admin.php?action=dt_dpp_post_as_draft&amp;post=' . $post->ID . '&amp;nonce=' . wp_create_nonce( 'dt-duplicate-page-' . $post->ID ) . '" title="Duplicate this as ' . $post_status . '" rel="permalink">' . $link_title . '</a>';
			$html .= '</div>';
			$html .= '</div>';
			echo $html;
		}

		/*
		 * Add the duplicate link to edit screen - gutenberg
		 */
		public function dpp_wpp_button_guten() {
			global $post;
			if ( $post ) {
				$opt         = get_option( 'dpp_wpp_page_options' );
				$post_status = ! empty( $opt['dpp_post_status'] ) ? esc_attr( $opt['dpp_post_status'] ) : 'draft';
				if ( isset( $opt['dpp_posteditor'] ) && $opt['dpp_posteditor'] == 'gutenberg' ) {
					?>
                    <style> .link_gutenberg {
                            text-align: center;
                            margin-top: 15px;
                        }

                        .link_gutenberg a {
                            text-decoration: none;
                            display: block;
                            height: 40px;
                            line-height: 28px;
                            padding: 3px 12px 2px;
                            background: #0073AA;
                            border-radius: 3px;
                            border-width: 1px;
                            border-style: solid;
                            color: #ffffff;
                            font-size: 16px;
                        }

                        .link_gutenberg a:hover {
                            background: #23282D;
                            border-color: #23282D;
                        }</style>
                    <script>jQuery(window).load(function (e) {
                            var dpp_postid = "<?php echo esc_attr( $post->ID ); ?>";
                            var dtnonce = "<?php echo wp_create_nonce( 'dt-duplicate-page-' . $post->ID );?>";
                            var dpp_posttitle = "Duplicate this as <?php echo esc_attr( $post_status ); ?>";
                            var dpp_duplicatelink = '<div class="link_gutenberg">';
                            dpp_duplicatelink += '<a href="admin.php?action=dt_dpp_post_as_draft&amp;post=' + dpp_postid + '&amp;nonce=' + dtnonce + '" title="' + dpp_posttitle + '">Duplicate</a>';
                            dpp_duplicatelink += '</div>';
                            jQuery('.edit-post-post-status').append(dpp_duplicatelink);
                        });</script>
					<?php
				}
			}
		}


		/*Click here to clone Admin Bar*/
		public function dpp_wpp_page_admin_bar_link() {
			global $wp_admin_bar;
			global $post;
			$opt            = get_option( 'dpp_wpp_page_options' );
			$post_status    = ! empty( $opt['dpp_post_status'] ) ? esc_attr( $opt['dpp_post_status'] ) : 'draft';
			$current_object = get_queried_object();
			if ( empty( $current_object ) ) {
				return;
			}
			if ( ! empty( $current_object->post_type ) && ( $post_type_object = get_post_type_object( $current_object->post_type ) ) && ( $post_type_object->show_ui || $current_object->post_type == 'attachment' ) ) {
				$wp_admin_bar->add_menu( array(
					'parent' => 'edit',
					'id'     => 'dpp_this',
					'title'  => __( "Clone this as " . $post_status . "", 'duplicate-wp-page-post' ),
					'href'   => admin_url() . 'admin.php?action=dt_dpp_post_as_draft&amp;post=' . $post->ID . '&amp;nonce=' . wp_create_nonce( 'dt-duplicate-page-' . $post->ID )
				) );
			}
		}


		/*WP Url Redirect*/
		static function dp_redirect( $url ) {
			$result = '<script>window.location.href="' . $url . '"</script>';
			print( $result );
		}

		/*plugin settings page link*/
		function dpp_settings_link( $links, $file ) {
			if ( $file == DPP_BASE_NAME ) {

				$links[] = '<a href="' .
				           admin_url( 'options-general.php?page=dpp_page_settings' ) .
				           '">' . __( 'Settings' ) . '</a>';
			}

			return $links;

		}

	}

	new dpp_wpp_page();

endif;
?>