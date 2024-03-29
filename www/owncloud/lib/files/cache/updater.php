<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OC\Files\Cache;

/**
 * listen to filesystem hooks and change the cache accordingly
 */
class Updater {

	/**
	 * resolve a path to a storage and internal path
	 *
	 * @param string $path
	 * @return array consisting of the storage and the internal path
	 */
	static public function resolvePath($path) {
		$view = \OC\Files\Filesystem::getView();
		return $view->resolvePath($path);
	}

	static public function writeUpdate($path) {
		/**
		 * @var \OC\Files\Storage\Storage $storage
		 * @var string $internalPath
		 */
		list($storage, $internalPath) = self::resolvePath($path);
		if ($storage) {
			$cache = $storage->getCache($internalPath);
			$scanner = $storage->getScanner($internalPath);
			$scanner->scan($internalPath, Scanner::SCAN_SHALLOW);
			$cache->correctFolderSize($internalPath);
			self::correctFolder($path, $storage->filemtime($internalPath));
		}
	}

	static public function deleteUpdate($path) {
		/**
		 * @var \OC\Files\Storage\Storage $storage
		 * @var string $internalPath
		 */
		list($storage, $internalPath) = self::resolvePath($path);
		if ($storage) {
			$cache = $storage->getCache($internalPath);
			$cache->remove($internalPath);
			$cache->correctFolderSize($internalPath);
			self::correctFolder($path, time());
		}
	}

	static public function renameUpdate($from, $to) {
		/**
		 * @var \OC\Files\Storage\Storage $storageFrom
		 * @var \OC\Files\Storage\Storage $storageTo
		 * @var string $internalFrom
		 * @var string $internalTo
		 */
		list($storageFrom, $internalFrom) = self::resolvePath($from);
		list($storageTo, $internalTo) = self::resolvePath($to);
		if ($storageFrom && $storageTo) {
			if ($storageFrom === $storageTo) {
				$cache = $storageFrom->getCache($internalFrom);
				$cache->move($internalFrom, $internalTo);
				$cache->correctFolderSize($internalFrom);
				$cache->correctFolderSize($internalTo);
				self::correctFolder($from, time());
				self::correctFolder($to, time());
			} else {
				self::deleteUpdate($from);
				self::writeUpdate($to);
			}
		}
	}

	/**
	 * @brief get file owner and path
	 * @param string $filename
	 * @return array with the oweners uid and the owners path
	 */
	private static function getUidAndFilename($filename) {
		$uid = \OC\Files\Filesystem::getOwner($filename);
		\OC\Files\Filesystem::initMountPoints($uid);
		if ( $uid != \OCP\User::getUser() ) {
			$info = \OC\Files\Filesystem::getFileInfo($filename);
			$ownerView = new \OC\Files\View('/'.$uid.'/files');
			$filename = $ownerView->getPath($info['fileid']);
		}
		return array($uid, '/files/'.$filename);
	}

	/**
	 * Update the mtime and ETag of all parent folders
	 *
	 * @param string $path
	 * @param string $time
	 */
	static public function correctFolder($path, $time) {

		if ($path !== '' && $path !== '/') {

			list($owner, $realPath) = self::getUidAndFilename(dirname($path));

			/**
			 * @var \OC\Files\Storage\Storage $storage
			 * @var string $internalPath
			 */

			$view = new \OC\Files\View('/' . $owner);

			list($storage, $internalPath) = $view->resolvePath($realPath);
			$cache = $storage->getCache();
			$id = $cache->getId($internalPath);

			while ($id !== -1) {
				$cache->update($id, array('mtime' => $time, 'etag' => $storage->getETag($internalPath)));
				$realPath = dirname($realPath);
				// check storage for parent in case we change the storage in this step
				list($storage, $internalPath) = $view->resolvePath($realPath);
				if ($internalPath) {
					$cache = $storage->getCache();
					$id = $cache->getId($internalPath);
				} else {
					$id = -1;
				}

			}

		}
	}

	/**
	 * @param array $params
	 */
	static public function writeHook($params) {
		self::writeUpdate($params['path']);
	}

	/**
	 * @param array $params
	 */
	static public function touchHook($params) {
		$path = $params['path'];
		list($storage, $internalPath) = self::resolvePath($path);
		$cache = $storage->getCache();
		$id = $cache->getId($internalPath);
		if ($id !== -1) {
			$cache->update($id, array('etag' => $storage->getETag($internalPath)));
		}
		self::writeUpdate($path);
	}

	/**
	 * @param array $params
	 */
	static public function renameHook($params) {
		self::renameUpdate($params['oldpath'], $params['newpath']);
	}

	/**
	 * @param array $params
	 */
	static public function deleteHook($params) {
		self::deleteUpdate($params['path']);
	}
}
