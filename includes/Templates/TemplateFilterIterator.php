<?php

namespace WP_Forge\WP_Scaffolding_Tool\Templates;

/**
 * Class TemplateFilterIterator
 */
class TemplateFilterIterator extends \RecursiveFilterIterator {

	const FILTERS = array(
		'.',
		'..',
		'.git',
	);

	/**
	 * Determine whether or not to iterate into a directory.
	 *
	 * @return bool
	 */
	public function accept() {
		return $this->current()->isDir() &&
			! in_array(
				$this->current()->getFilename(),
				self::FILTERS,
				true
			);
	}

}
