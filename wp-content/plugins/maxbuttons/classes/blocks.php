<?php
declare(strict_types=1);
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

/** Blocks collection
*
* Class for general block functions - transitional
*/
use \RecursiveDirectoryIterator as RecursiveDirectoryIterator;
use \RecursiveIteratorIterator as RecursiveIteratorIterator;
use \FilesystemIterator as FilesystemIterator;

class maxBlocks
{
	protected static $blocks;  // collection!
	protected static $block_classes;

	protected static $data; // full data array
	protected static $fields = array(); // all fields (@todo - Seems unused)

	protected static $mixins;

	public static function init()
	{

	}

	/** Find the block classes */
	public static function initBlocks()
	{
		$block_paths = apply_filters('mb-block-paths',  array(MB()->get_plugin_path() . "blocks/") );

		$newBlocks = array();
		$templates = array();

		foreach($block_paths as $block_path)
		{
			$dir_iterator = new RecursiveDirectoryIterator($block_path, FilesystemIterator::SKIP_DOTS);
			$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

			foreach ($iterator as $fileinfo)
			{

				$path = $fileinfo->getRealPath();
				// THIS IS PHP > 5.3.6
				//$extension = $fileinfo->getExtension();
				$extension = pathinfo($path, PATHINFO_EXTENSION);

				if ($fileinfo->isFile() )
				{
					if ($extension == 'php')
					{
					 	require_once($path);
					}
					elseif($extension == 'tpl')
					{
						$filename = $fileinfo->getBasename('.tpl');
						$templates[$filename] = array('path' => $path);
					}
				}
			}

		}
			ksort($blockOrder);
			foreach($blockOrder as $prio => $blockArray)
			{
				foreach($blockArray as $block)
				{
					if (isset($blockClass[$block]))
						$newBlocks[$block] = $blockClass[$block];
				}
			}
			$blockClass = $newBlocks;
			if (is_admin())
			{
				// possible issue with some hosters faking is_admin flag.
				if (class_exists( maxUtils::namespaceit('maxBlocks') ) && class_exists( maxUtils::namespaceit('maxBlocks') ) )
				{
					maxField::setTemplates($templates);

				}
				else
				{
					error_log('[MaxButtons] - MaxField class is not set within admin context. This can cause issues when using button editor');
				}
			}

		//$this->loadBlockClasses($blockClass);

		static::$block_classes = array_values($blockClass);
	}

	public static function getBlockClasses()
	{
		if ( is_null(static::$block_classes) )
			self::initBlocks();

		 return static::$block_classes;
	}


	public static function add($block)
	{
		$name = $block->get_name();

		static::$blocks[$name] = $block;
		static::$fields = array_merge(self::$fields, $block->get_fields());
	}

	public static function getFieldDefinition($field_name)
	{
		 if (isset(self::$fields[$field_name]))
		 {
			  return self::$fields[$field_name];
		 }

		 return false;
	}

	/** Temporary store a mixin to be used later for compat across screens
	* @param $name String Name of the mixin, from fields array
	* @param $field String Name of field.
	*/
	public static function addMixin($name, $field, $screen_id)
	{
		if (! isset(self::$mixins[$screen_id]))
		{
			self::$mixins[$screen_id] = array();
		}
		if (! isset(self::$mixins[$screen_id][$name]))
		{
			self::$mixins[$screen_id][$name] = array();
		}

		self::$mixins[$screen_id][$name][] = $field;
	}

	public static function getMixins($name, $screen_id)
	{
		 if (isset(self::$mixins[$screen_id][$name]))
		 	return self::$mixins[$screen_id][$name];
		else
		 return array();

	}

	public static function clearFoundMixins()
	{
		  self::$mixins = array();
	}

}
