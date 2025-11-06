#!/usr/bin/env php
<?php
/**
 * Generate a new ACF Gutenberg block
 *
 * Usage: composer make:block block-name
 *
 * This script generates:
 * - Block class in src/Acf/Block/ (with ACF fields)
 * - Twig template in views/blocks/
 *
 * @package App
 */

// Check if block name is provided
if ($argc < 2) {
	echo "Usage: composer make:block block-name\n";
	echo "Example: composer make:block hero-section\n";
	exit(1);
}

$block_slug = $argv[1];

// Validate block name format (kebab-case)
if (!preg_match('/^[a-z]+(-[a-z]+)*$/', $block_slug)) {
	echo "Error: Block name must be in kebab-case format (e.g., hero-section)\n";
	exit(1);
}

// Convert kebab-case to PascalCase for class names
function kebab_to_pascal($string) {
	return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
}

// Convert kebab-case to Title Case for labels
function kebab_to_title($string) {
	return ucwords( str_replace('-', ' ', $string));
}

$class_name = kebab_to_pascal($block_slug);
$block_title = kebab_to_title($block_slug);

// Define paths
$theme_dir = dirname(__DIR__);
$block_class_path = $theme_dir . '/src/Acf/Block/' . $class_name . 'Block.php';
$twig_template_path = $theme_dir . '/views/blocks/' . $block_slug . '.twig';

// Check if files already exist
if ( file_exists($block_class_path)) {
	echo "Error: Block class already exists: {$block_class_path}\n";
	exit(1);
}

if (file_exists( $twig_template_path)) {
	echo "Error: Twig template already exists: {$twig_template_path}\n";
	exit( 1 );
}

// Create directories if they don't exist
$dirs = [
	dirname($block_class_path),
	dirname($twig_template_path),
];

foreach ($dirs as $dir) {
	if (!is_dir($dir)) {
		mkdir($dir, 0755, true);
	}
}

// Generate Block class with ACF fields
$block_class_content = <<<PHP
<?php
/**
 * {$block_title} Block
 *
 * ACF Gutenberg block for {$block_title}.
 *
 * @package App\Acf\Block
 */

namespace App\Acf\Block;

use App\Inc\AcfRegistrable;
use App\Inc\BlockRegistrable;
use Extended\ACF\Fields\Text;
use Extended\ACF\Location;

/**
 * Class {$class_name}Block
 */
class {$class_name}Block implements BlockRegistrable, AcfRegistrable {

	/**
	 * Get block configuration.
	 *
	 * @return array Block configuration.
	 */
	public function get_block_config(): array {
		return [
			'name'            => '{$block_slug}',
			'title'           => __( '{$block_title}', 'eddaoust-timber-starter-theme' ),
			'description'     => __( 'A {$block_title} block.', 'eddaoust-timber-starter-theme' ),
			'category'        => 'custom-blocks',
			'icon'            => 'admin-generic',
			'keywords'        => [ '{$block_slug}' ],
			'mode'            => 'preview',
			'supports'        => [
				'align'  => [ 'wide', 'full' ],
				'mode'   => true,
				'jsx'    => false,
			],
			'enqueue_assets'  => function() {
				// No dedicated assets - using Tailwind only
			},
		];
	}

	/**
	 * Get ACF field group configuration.
	 *
	 * @return array ACF field group configuration.
	 */
	public function get_fields(): array {
		return [
			'title'    => '{$block_title} Block Fields',
			'fields'   => [
				Text::make(__('Title', 'eddaoust-timber-starter-theme'), '{$block_slug}_title')
					->required()
					->instructions(__('Enter the title', 'eddaoust-timber-starter-theme'))
					->defaultValue( '{$block_title} Title' ),
			],
			'location' => [
				Location::where('block', 'acf/{$block_slug}'),
			],
		];
	}
}

PHP;

// Generate Twig template
$twig_template_content = <<<TWIG
{#
/**
 * {$block_title} Block Template
 *
 * @var array block - Block configuration
 * @var array fields - ACF fields data
 * @var bool is_preview - Preview mode flag
 * @var string block_classes - CSS classes for the block
 */
#}

<section class="{{ block_classes }}">
	<div class="container mx-auto px-4 py-8">
		{% if fields.{$block_slug}_title %}
			<h2 class="text-3xl font-bold mb-4">
				{{ fields.{$block_slug}_title }}
			</h2>
		{% endif %}

		{# Add your custom markup here #}
		<div class="prose max-w-none">
			<p class="text-gray-600">
				This is the {$block_title} block. Edit the template at <code>views/blocks/{$block_slug}.twig</code>
			</p>
		</div>
	</div>
</section>

TWIG;

// Write files
file_put_contents($block_class_path, $block_class_content);
file_put_contents($twig_template_path, $twig_template_content);

// Success message
echo "\n✅ Block '{$block_slug}' created successfully!\n\n";
echo "Created files:\n";
echo "  📄 {$block_class_path}\n";
echo "  📄 {$twig_template_path}\n\n";
echo "Next steps:\n";
echo "  1. Customize the block configuration in {$class_name}Block.php (get_block_config())\n";
echo "  2. Add ACF fields in {$class_name}Block.php (get_fields())\n";
echo "  3. Design the template in views/blocks/{$block_slug}.twig\n";
echo "  4. The block will be auto-registered on next page load\n\n";

exit( 0 );

