<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>
<div class="wrap dpp_page_settings">
    <h1><?php _e( 'Plugin Settings', 'duplicate-wp-page-post' ) ?></h1>
	<?php
	$dpp_options = array();
	$opt         = get_option( 'dpp_wpp_page_options' );
    $getinstruct = sanitize_text_field($_GET['instruct']);
	$instruct    = isset( $getinstruct ) ? $getinstruct : '';
	if ( isset( $_POST['submit_dpp_wpp_page'] ) && wp_verify_nonce( $_POST['dpp_nonce_field'], 'dpp_page_action' ) ):
		_e( "<strong>changes saving..</strong>", 'duplicate-wp-page-post' );
		$dpp_nosave = array( 'submit_dpp_wpp_page' );
		foreach ( $dpp_nosave as $noneed ):
			unset( $_POST[ $noneed ] );
		endforeach;
		foreach ( $_POST as $key => $val ):
			$dpp_options[ $key ] = sanitize_text_field( $val );
		endforeach;
		$dpp_settings_save = update_option( 'dpp_wpp_page_options', $dpp_options );
		if ( $dpp_settings_save ) {
			dpp_wpp_page::dp_redirect( 'options-general.php?page=dpp_page_settings&instruct=1' );
		} else {
			dpp_wpp_page::dp_redirect( 'options-general.php?page=dpp_page_settings&instruct=2' );
		}endif;
	if ( ! empty( $instruct ) && $instruct == 1 ):
		_e( '<div id="message" class="updated notice notice-success is-dismissible">
          <p>Changes Saved!</p>
          <button type="button" class="notice-dismiss">
             <span class="screen-reader-text">Ignore this notice.</span>
          </button>
       </div>', 'duplicate-wp-page-post' );
    elseif ( ! empty( $instruct ) && $instruct == 2 ):
		_e( '<div id="message" class="error notice notice-error is-dismissible">
          <p>Changes not saved!</p>
          <button type="button" class="notice-dismiss">
             <span class="screen-reader-text">Ignore this notice.</span>
          </button>
       </div>', 'duplicate-wp-page-post' );
	endif;
	?>
    <div id="dpp-stuff">
        <div id="dpp-post-body" class="metabox-holder columns-2">
            <div id="dpp-post-body-content" style="position: relative;">
                <form style="padding: 10px; border: 1px solid #333;" action="" method="post" name="dpp_wpp_page_form">
					<?php wp_nonce_field( 'dpp_page_action', 'dpp_nonce_field' ); ?>
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th scope="row"><label
                                        for="dpp_posteditor">Select Editor<br><em>Default: Classic Editor</em></label>
                            </th>
                            <td>
                                <select id="dpp_posteditor" name="dpp_posteditor">
                                    <option value="classic" <?php echo ( isset( $opt['dpp_posteditor'] ) && $opt['dpp_posteditor'] == 'classic' ) ? "selected = 'selected'" : ''; ?>><?php _e( 'Classic Editor', 'duplicate-wp-page-post' ); ?></option>
                                    <option value="gutenberg" <?php echo ( isset( $opt['dpp_posteditor'] ) && $opt['dpp_posteditor'] == 'gutenberg' ) ? "selected = 'selected'" : ''; ?>><?php _e( 'Gutenberg Editor', 'duplicate-wp-page-post' ); ?></option>
                                </select>
                                <p>Please select which editor you are using.<br> If you are using Gutenberg, select
                                    gutenberg editor otherwise it will not show Duplicate button on edit screen.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label
                                        for="dpp_post_status">Post Status<br><em>Default: Draft</em></label>
                            </th>
                            <td>
                                <select id="dpp_post_status" name="dpp_post_status">
                                    <option value="draft" <?php echo ( $opt['dpp_post_status'] == 'draft' ) ? "selected = 'selected'" : ''; ?>><?php _e( 'Draft', 'duplicate-wp-page-post' ); ?></option>
                                    <option value="publish" <?php echo ( $opt['dpp_post_status'] == 'publish' ) ? "selected = 'selected'" : ''; ?>><?php _e( 'Publish', 'duplicate-wp-page-post' ); ?></option>
                                    <option value="private" <?php echo ( $opt['dpp_post_status'] == 'private' ) ? "selected = 'selected'" : ''; ?>><?php _e( 'Private', 'duplicate-wp-page-post' ); ?></option>
                                    <option value="pending" <?php echo ( $opt['dpp_post_status'] == 'pending' ) ? "selected = 'selected'" : ''; ?>><?php _e( 'Pending', 'duplicate-wp-page-post' ); ?></option>
                                </select>
                                <p>Please select any post status you want to assign for duplicate post.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label
                                        for="dpp_post_redirect">Redirect<br><em>Default: To current list.</em><br>(After
                                    click on <strong>Duplicate</strong></label>
                            </th>
                            <td>
                                <select id="dpp_post_redirect" name="dpp_post_redirect">
                                    <option value="to_list" <?php echo ( $opt['dpp_post_redirect'] == 'to_list' ) ? "selected = 'selected'" : ''; ?>><?php _e( 'All Post List', 'duplicate-wp-page-post' ); ?></option>
                                    <option value="to_page" <?php echo ( $opt['dpp_post_redirect'] == 'to_page' ) ? "selected = 'selected'" : ''; ?>><?php _e( 'Direct Edit', 'duplicate-wp-page-post' ); ?></option>
                                </select>
                                <p>Please select any post redirection, redirect you to selected after click on
                                    duplicate.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label
                                        for="dpp_post_suffix">Duplicate Post Suffix<br><em>Default: Empty</em></label>
                            </th>

                            <td>
                                <input type="text" class="regular-text"
                                       value="<?php echo ! empty( $opt['dpp_post_suffix'] ) ? esc_attr( $opt['dpp_post_suffix'] ) : '' ?>"
                                       id="dpp_post_suffix" name="dpp_post_suffix">
                                <p>Add a suffix for duplicate page and post. It will show after title.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label
                                        for="dpp_post_link_title">Duplicate Link Text<br><em>Default:
                                        Duplicate</em></label>
                            </th>
                            <td>
                                <input type="text" class="regular-text"
                                       value="<?php echo ! empty( $opt['dpp_post_link_title'] ) ? esc_attr( $opt['dpp_post_link_title'] ) : '' ?>"
                                       id="dpp_post_link_title" name="dpp_post_link_title">
                                <p>It will show above text on duplicate page/post link button instead of default
                                    (Duplicate)</p>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <p class="submit"><input type="submit" value="Save Settings" class="button button-primary"
                                             id="submit" name="submit_dpp_wpp_page"></p>
                </form>
            </div>
        </div>
        <div>
            <h3><a href="https://wordpress.org/support/plugin/duplicate-wp-page-post/reviews/?filter=5#new-post">Please
                    review us</a> if you like the plugin.</h3>
        </div>
    </div>
</div>