<?php

namespace App\Acf;

use App\Inc\AcfRegistrable;
use App\Inc\BlockRegistrable;
use App\Inc\HookableInterface;
use App\Inc\SingletonTrait;
use Timber\Timber;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

final class BlocksManager implements HookableInterface {
	use SingletonTrait;

	private array $blocks = [];
	private string $custom_category = 'custom-blocks';

	protected function __construct() {
		$this->register_hooks();
	}

	public function register_hooks(): void {
		add_action( 'acf/init', [$this, 'register_blocks']);
		add_filter('block_categories_all', [$this, 'register_block_category'], 10, 2);
	}

	public function register_blocks(): void {
		$this->load_blocks();

		foreach ($this->blocks as $block) {
			// Register ACF fields if the block also implements AcfRegistrable
			if ( $block instanceof AcfRegistrable ) {
				$fields = $block->get_fields();
				if (!empty($fields)) {
					register_extended_field_group($fields);
				}
			}

			$config = $block->get_block_config();

			if (empty($config['category'])) {
				$config['category'] = $this->custom_category;
			}

			$config['render_callback'] = [$this, 'render_block_callback'];

			acf_register_block_type( $config );
		}
	}

	private function load_blocks(): void {
		$block_dir = get_template_directory() . '/src/Acf/Block';

		if (!is_dir($block_dir)) {
			return;
		}

		$files = glob($block_dir . '/*.php');

		if (empty($files)) {
			return;
		}

		foreach ($files as $file) {
			$class_name = 'App\\Acf\\Block\\' . basename($file, '.php');

			if (!class_exists($class_name)) {
				continue;
			}

			$instance = new $class_name();

			if ($instance instanceof BlockRegistrable) {
				$this->blocks[] = $instance;
			}
		}
	}

	/**
	 * Render callback for blocks.
	 *
	 * Uses Timber to render the block template with ACF fields as context.
	 *
	 * @param array  $block The block settings and attributes.
	 * @param string $content The block inner HTML (empty).
	 * @param bool   $is_preview True during AJAX preview.
	 * @param int    $post_id The post ID this block is saved to.
	 */
	public function render_block_callback(array $block, string $content = '', bool $is_preview = false, int $post_id = 0): void {
		$block_name = str_replace('acf/', '', $block['name']);

		$context = Timber::context();
		$context['block'] = $block;
		$context['fields'] = get_fields();
		$context['is_preview'] = $is_preview;
		$context['post_id'] = $post_id;

		$classes = ['block', 'block-' . $block_name];
		if (!empty($block['className'])) {
			$classes[] = $block['className'];
		}
		if (!empty($block['align'])) {
			$classes[] = 'align' . $block['align'];
		}
		$context['block_classes'] = implode(' ', $classes);

		$template_path = 'blocks/' . $block_name . '.twig';
		Timber::render($template_path, $context);
	}

	/**
	 * Register custom block category.
	 *
	 * @param array                   $categories Array of block categories.
	 * @param \WP_Block_Editor_Context $context Block editor context.
	 * @return array Modified array of block categories.
	 */
	public function register_block_category(array $categories, $context): array {
		return array_merge(
			[
				[
					'slug'  => $this->custom_category,
					'title' => __('Custom Blocks', 'eddaoust-timber-starter-theme'),
					'icon'  => 'layout',
				],
			],
			$categories
		);
	}
}
