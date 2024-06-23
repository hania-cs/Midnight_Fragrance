<?php
declare(strict_types=1);
namespace MaxButtons\Upgrader;
defined('ABSPATH') or die('No direct access permitted');

/** Limited Class to Check a License and get PRO going.
*/
class upgradeLicense
{
	protected static $instance;

	protected $product_id = "maxbuttons-pro";
	protected $license_key = null;
	protected $license_activated = null;
	protected $license_lastcheck = null;
	protected $license_expires = null;

	protected $api_url = 'https://www.maxbuttons.com';
//	protected $update_url = 'https://maxbuttons.com/maxupdate';

	protected $is_valid = false;
	protected $is_expired = true;


	public function __construct()
	{

	}

	public static function getInstance()
	{
		if (is_null(self::$instance))
			self::$instance = new upgradeLicense();

		return self::$instance;
	}


	public function is_activated()
	{
		return $this->license_activated;
	}

	public function is_valid()
	{
		return $this->is_valid;
	}

	public function is_expired()
	{
		return $this->is_expired;
	}

	protected function get_api_args()
	{
		$args = array(
				"item_name" => $this->product_id,
				"url" => home_url(),
			);
			return $args;
	}

	/** Check the given license against the system */
	public function activate_license($license_key)
	{
		$error = false; // error handling

		$args = $this->get_api_args();
		$args["license"] = $license_key;
		$args["edd_action"] = 'activate_license';

		$free_created = get_option("MBFREE_CREATED");
		$free_url = get_option("MBFREE_HOMEURL");

		if ($free_created != '')
			$args["free_created"] = $free_created;
		if ($free_url != '')
			$args["free_url"] = $free_url;

		//$api_url = add_query_arg($args, $this->api_url);

		//header('Content-Type: application/json');

		if ($error) // errors before the request
		{
			 return $error_body;
			//echo json_encode($error_body);
		//	exit();
		}

		$data = $this->do_api_post($args);

		if (isset($data->license) && $data->license == 'valid')
		{
			$expires = strval($data->expires);
			$expires = strtotime($expires);
			$result  = array("status" => 'success'); // clean output
			update_option('maxbuttons_pro_license_key', $license_key, true);
			update_option('maxbuttons_pro_license_expires', $expires );
			update_option('maxbuttons_pro_license_activated', true, true);

			wp_cache_flush(); // some hosts have aggro-caches here, hopefully this will helps against them.
	//		echo json_encode($result);
	//		exit();
		  $data->success = true;
		  return $data;
		}
		else
		{
			$data->success = false;
			return $this->handle_error($data);
		}

	}

	public function getPackageUrl($license_key)
	{
			$args = $this->get_api_args();
			$args["license"] = $license_key;
			$args["edd_action"] = 'get_version';

			$data = $this->do_api_post($args);

			if ($data->package && strlen($data->package) > 0)
				 return $data->package;
			else
				 return false;

	}


	protected function do_api_post($args)
	{
			$result = wp_remote_post( $this->api_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $args ) );

			if ( is_wp_error( $result ) || 200 !== wp_remote_retrieve_response_code( $result ) )
			{
				$error = $result->get_error_message();

				$message =  ( is_wp_error( $result ) && ! empty( $error ) ) ? $error : __( 'An connection error occurred, please try again.', 'maxbuttons-pro');
				$result = array('status' => 'error',
								'error' => $message,
								"additional_info" => $message,
								);
				echo json_encode($result);

			}

			$data = json_decode( wp_remote_retrieve_body( $result ) );

			return $data;
	}


	protected function handle_error($data)
	{
			//$new_result = new \stdclass; // clean output;
			//.$new_result["status"] = "error";
			//$new_result["error"] = (isset($data->error)) ? $data->error : '';
			$data->status = 'error';

			switch( $data->error ) {
					case 'expired' :
						$message = sprintf(
							__( 'Your license key expired on %s.', 'maxbuttons-pro' ),
							date_i18n( get_option( 'date_format' ), strtotime( strval($data->expires), current_time( 'timestamp' ) ) )
						);
						break;
					case 'revoked' :
					case 'disabled' :
						$message = __( 'Your license key has been disabled.','maxbuttons-pro');
						break;
					case 'missing' :
						$message = __( 'Invalid license. Please check the license code. ', 'maxbuttons-pro');
						break;
					case 'invalid' :
					case 'site_inactive' :
						$message = __( 'Your license is not active for this URL.', 'maxbuttons-pro' );
						break;
					case 'item_name_mismatch' :
						$message = sprintf( __( 'This appears to be an invalid license key for %s.', 'maxbuttons-pro' ), 'MaxButtons PRO' );
						break;
					case 'no_activations_left':
						$message = __( 'Your license key has reached its activation limit.','maxbuttons-pro' );
						break;
					default :
						$message = __( 'An error occurred, please try again.', 'maxbuttons-pro' );
						break;
			}
			$data->additional_info = $message ;
	//		$result = $new_result;

			return $data;
		//echo json_encode($result);
	//	exit();
	}


	public function get_remote_license($license_key)
	{
		$args = array(
				"edd_action" => "check_license",
				"license" => $license_key,
				"item_name" => $this->product_id,
				"url" => home_url(),
		);


		$request = wp_remote_post($this->api_url,  array( 'body' => $args, 'timeout' => 15, 'sslverify' => false ) );
		if(is_wp_error($request))
		{
			// failed - defer check three hours - prevent check license flood
		//	$this->update_license_checked_time( (3*HOUR_IN_SECONDS) );
			error_log("MBPRO - License server failed to respond");
			return __("HTTP Request failed", 'maxbuttons');
		}

		$data = json_decode( wp_remote_retrieve_body( $request ) );

		if (isset($data->expires))
		{
			if ($data->expires === 'lifetime')
				$expires = time() + YEAR_IN_SECONDS;
			else
				$expires = strtotime($data->expires);

			update_option('maxbuttons_pro_license_expires', $expires );
		}

		// this is probably not correct! valid || expired?
		$active_statuses = array('valid', 'expired', 'inactive');
		if (isset($data->license) && in_array($data->license, $active_statuses)  )
		{
			return $data;
//			return true;
		}
		else
		{
			$data->error = 'license-not-valid';
			return $this->handle_error($data);
		}
	}


} // Class
