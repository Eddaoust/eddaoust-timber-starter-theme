<?php

namespace App\Inc;

interface BlockRegistrable {

	/**
	 * Get block configuration.
	 *
	 * Should return an array with block configuration:
	 * - name: Block slug (e.g., 'hero')
	 * - title: Block title
	 * - description: Block description
	 * - category: Block category
	 * - icon: Dashicon name
	 * - keywords: Array of keywords
	 * - mode: Display mode (edit, preview, auto)
	 * - supports: Array of features (align, mode, etc.)
	 *
	 * @return array Block configuration.
	 */
	public function get_block_config(): array;
}
