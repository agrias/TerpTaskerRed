<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OC\Files\Storage;

/**
 * Specialized version of Local storage for home directory usage
 */
class Home extends Local {
	/**
	 * @var \OC\User\User $user
	 */
	protected $user;

	public function __construct($arguments) {
		$this->user = $arguments['user'];
		$this->datadir = $arguments['datadir'];
		if (substr($this->datadir, -1) !== '/') {
			$this->datadir .= '/';
		}
	}

	public function getId() {
		return 'home::' . $this->user;
	}
}