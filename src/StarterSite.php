<?php

/**
 * StarterSite class
 * This class is used to add custom functionality to the theme.
 */

namespace App;

use App\Acf\BlocksManager;
use App\Acf\FieldsManager;
use App\Theme\Asset;
use App\Theme\CustomPostType;
use App\Theme\Menu;
use App\Theme\MetaBox;
use App\Theme\Security;
use App\Theme\Taxonomy;
use App\Theme\ThemeSupport;
use Timber\Site;
use Timber\Timber;

/**
 * Class StarterSite.
 */
class StarterSite extends Site {



	/**
	 * StarterSite constructor.
	 */
	public function __construct() {
		Security::get_instance();
		Asset::get_instance();
		ThemeSupport::get_instance();
		Menu::get_instance();
		Taxonomy::get_instance();
		CustomPostType::get_instance();
		MetaBox::get_instance();

		if(class_exists('ACF')) {
			FieldsManager::get_instance();
			BlocksManager::get_instance();
		}

		add_filter( 'timber/context', [ $this, 'add_to_context' ] );
		add_filter( 'timber/twig/filters', [ $this, 'add_filters_to_twig' ] );
		add_filter( 'timber/twig/functions', [ $this, 'add_functions_to_twig' ] );
		add_filter( 'timber/twig/environment/options', [ $this, 'update_twig_environment_options' ] );

		parent::__construct();
	}

	/**
	 * This is where you add some context.
	 *
	 * @param array $context context['this'] Being the Twig's {{ this }}
	 */
	public function add_to_context( $context ) {
		$context['menu']  = Timber::get_menu( 'primary_navigation' );
		$context['site']  = $this;

		return $context;
	}

	/**
	 * This is where you can add your own functions to twig.
	 *
	 * @link https://timber.github.io/docs/v2/hooks/filters/#timber/twig/filters
	 * @param array $filters an array of Twig filters.
	 */
	public function add_filters_to_twig( $filters ) {

		$additional_filters = [
			'myfoo' => [
				'callable' => [ $this, 'myfoo' ],
			],
		];

		return array_merge( $filters, $additional_filters );
	}


	/**
	 * This is where you can add your own functions to twig.
	 *
	 * @link https://timber.github.io/docs/v2/hooks/filters/#timber/twig/functions
	 * @param array $functions an array of existing Twig functions.
	 */
	public function add_functions_to_twig( $functions ) {
		$additional_functions = [
			'get_theme_mod' => [
				'callable' => 'get_theme_mod',
			],
		];

		return array_merge( $functions, $additional_functions );
	}

	/**
	 * Updates Twig environment options.
	 *
	 * @see https://twig.symfony.com/doc/2.x/api.html#environment-options
	 *
	 * @param array $options an array of environment options
	 *
	 * @return array
	 */
	public function update_twig_environment_options( $options ) {
		// $options['autoescape'] = true;

		return $options;
	}
}
