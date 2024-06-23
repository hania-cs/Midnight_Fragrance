<?php
declare(strict_types=1);
namespace MaxButtons;
/**  Buttons class - handles paging issues and sanity check for individual buttons
*/
class maxButtons
{
	protected static $loadedButtons = array(); // loaded button in current scope
	/*

	array [ index ] [ button_id ] [data - document_id, done (bool )
	*/
	protected static $documentArray = array();  // given out document ID's

	// override to give out next document id. This is useful when buttons are set but not displayed directly.
	protected static $doNext = false;
	protected static $current_doc_id = null;
	protected static $current_button_id = null;

	protected static $instance = null;

	public static function getInstance()
	{
		if (is_null(self::$instance))
			self::$instance = MB()->getClass('button');

		return self::$instance;
	}

	public static function buttonLoad($args)
	{
		$button_id = $args["button_id"];
		self::$loadedButtons[] = $button_id;
		$document_id = self::getDocumentID(array("button_id" => $button_id));
		self::$documentArray[] = array($button_id => array('document_id' => $document_id , 'done' => false));
	}

/* @todo Seems not in use
	public static function forceNextID()
	{
		self::$doNext = true;
		self::$current_doc_id = null;
		self::$current_button_id = null;
	}
*/
	public static function getDocumentID($args)
	{
		$button_id = $args["button_id"];

		if (! is_null(self::$current_doc_id) && self::$current_button_id == $button_id )
			return self::$current_doc_id;


		if (self::$doNext == false)
		{
			foreach(self::$documentArray as $index => $ids)
			{
				foreach($ids as $doc_button_id => $doc_vars)
				{
					if ($doc_button_id == $button_id)
					{
						if (! $doc_vars["done"])
							return $doc_vars["document_id"];
					}

				}

			}
		}
		// if not found in documentarray make a new one
		$loaded = self::$loadedButtons;
		end($loaded);
		$i = 0;

		foreach($loaded as $btn_id)
		{
			if ($btn_id == $button_id)
				$i++;
		}
		$i--; // minus the current added button..

		//$index = key($loaded); // find last index
		if ($i == 0)
			$document_id = $button_id;
		else
			$document_id = $button_id . "_" . $i;

		self::$doNext = false;
		self::$current_doc_id = $document_id;
		self::$current_button_id = $button_id;
		return $document_id;

	}

	public static function buttonDone($args)
	{
		$button_id = $args["button_id"];
		$document_id = $args["document_id"];

		foreach(self::$documentArray as $index => $data)
		{
			foreach($data as $doc_button_id => $doc_data)
			{
				if ($doc_button_id == $button_id && $doc_data["document_id"] == $document_id)
				{
					self::$documentArray[$index][$button_id]["done"] = true;
				}
			}
		}
	}

	public static function ajax_action()
	{
		$action = sanitize_text_field($_POST['button_action']);
		$check = wp_verify_nonce($_POST['nonce'], 'button-' . $action);
		$button_id = isset($_POST['button_id']) ? intval($_POST["button_id"]) : -1;
		$button = MB()->getClass('button');
		$paged = isset($_POST['paged']) ? intval($_POST['paged']) : '';

		$redirect = admin_url() . 'admin.php';
		$page = 'maxbuttons-controller';

		if ($paged)
			$redirect = add_query_arg('paged', $paged, $redirect);

		if  (! $check)
			exit('Invalid Nonce');

		$maxbuttons_capabilities = MB()->get_user_level();
		if (! current_user_can($maxbuttons_capabilities))
		{
			exit('No access rights to do this');
		}


		switch($action)
		{
			case "delete":
				$button->delete($button_id);
				$redirect = add_query_arg(array('action' => 'list', 'message' => '1delete'), $redirect);
				//$redirect_url = admin_url() . 'admin.php?page=maxbuttons-controller&action=list&message=1delete';
			break;

			case "trash":
				$result = $button->set($button_id);
			 	if ($result)
					$button->setStatus('trash');
				$redirect = add_query_arg(array('action' => 'list', 'message' => '1'), $redirect);
				//$redirect_url = admin_url() . 'admin.php?page=maxbuttons-controller&action=list&message=1';
			break;
			case "restore":
				$set = $button->set($button_id,'','trash');
				$button->setStatus("publish");
				$redirect = add_query_arg(array('action' => 'list', 'message' => '1restore', 'status' => 'trash'), $redirect);
				//$redirect_url = admin_url() . 'admin.php?page=maxbuttons-controller&action=list&status=trash&message=1restore';
			break;
			case "copy":
				$button->set($button_id);
				$new_id = $button->copy();
				$redirect = add_query_arg(array('action' => 'edit', 'id' => $new_id, 'copied' => 1), $redirect);
				//$redirect_url = admin_url() . 'admin.php?page=maxbuttons-controller&action=button&id=' . $new_id;
			break;
			case 'empty-trash':
				$mbadmin = MB()->getClass("admin");
				$args = array('status' => 'trash', 'limit' => -1  );
				$buttons = $mbadmin->getButtons($args);

				foreach($buttons as $btnar)
				{
						$button_id = intval($btnar['id']);
						$button->delete($button_id);
				}
				$redirect = add_query_arg(array('action' => 'list', 'message' => 'empty-trash'), $redirect);
		//	$redirect_url = admin_url() . 'admin.php?page=maxbuttons-controller&action=list&message=empty-trash';
			break;
			default:
				// Nothing, this is just compat.
			break;

		}

		$redirect = add_query_arg('page', $page, $redirect);

		if (isset($redirect)) {
			$response = array('redirection' => $redirect);
			echo json_encode($response);
			exit();
		}

		exit(true);
	}


	public static function generate_css()
	{
		global $wpdb;
		$id = isset($_REQUEST['id']) ? sanitize_text_field($_REQUEST['id']) : false;

		if (! $id)
			return;

		$idar = explode(',', $id);
		$idar = array_filter($idar, 'intval');

		$placeholders = implode(',', array_fill(0, count($idar), '%d' ));
		$data = array_merge($idar, array('publish')); // id's + status

		$sql = "SELECT cache FROM " . maxUtils::get_table_name() . " WHERE id in($placeholders) and status ='%s'";
		$sql = $wpdb->prepare($sql, $data);

		$row = $wpdb->get_col($sql);

		header( "Content-type: text/css; charset: UTF-8" );
		foreach($row as $content)
		{
			echo $content;
		}
		exit();
	}

} // class
