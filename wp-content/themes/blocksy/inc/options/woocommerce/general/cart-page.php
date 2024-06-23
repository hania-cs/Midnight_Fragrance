<?php
/**
 * Cart page options
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */

$options = [

	blocksy_rand_md5() => [
		'label' => __('Cart Page', 'blocksy'),
		'type' => 'ct-panel',
		'setting' => ['transport' => 'postMessage'],
		'inner-options' => [

			apply_filters(
				'blocksy_customizer_options:woocommerce:cart_page:before',
				[]
			),

			'cart_page_image_ratio' => [
				'label' => __('Image Ratio', 'blocksy'),
				'type' => 'ct-ratio',
				'view' => 'inline',
				'value' => '1/1',
				'divider' => 'top:full',
				'sync' => blocksy_sync_whole_page([
					'loader_selector' => '.ct-cart-form .woocommerce-cart-form'
				]),
			],

			'cart_page_image_size' => [
				'label' => __('Image Size', 'blocksy'),
				'type' => 'ct-select',
				'value' => 'woocommerce_thumbnail',
				'view' => 'text',
				'design' => 'inline',
				'divider' => 'top',
				'choices' => blocksy_ordered_keys(
					blocksy_get_all_image_sizes()
				),
				'sync' => blocksy_sync_whole_page([
					'loader_selector' => '.ct-cart-form .woocommerce-cart-form'
				]),
			],

		]
	],

];