<?php
/**
 * MultidatabaseContentFile Model
 * 汎用データベースコンテンツデータ ファイルに関するモデル処理
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MultidatabaseContent', 'MultidatabaseContent.Model');

/**
 * MultidatabaseContentFile Model
 *
 * @author Tomoyuki OHNO (Ricksoft, Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @package NetCommons\Multidatabases\Model
 *
 */
class MultidatabaseContentFile extends MultidatabasesAppModel {

/**
 * Custom database table name
 *
 * @var string
 */
	public $useTable = false;

/**
 * パスワードを設定する
 *
 * @param int $contentId  コンテンツID
 * @param array $passwords パスワード配列
 * @return bool
 * @throws InternalErrorException
 */
	public function saveAuthKey($contentId, $passwords) {
		$this->loadModels([
			'AuthorizationKeys' => 'AuthorizationKeys.AuthorizationKey',
		]);

		$baseDat = [
			'model' => 'MultidatabaseContent',
			'content_id' => $contentId
		];

		foreach ($passwords as $key => $val) {
			if (! $this->AuthorizationKeys->saveAuthorizationKey(
				$baseDat['model'],
				$baseDat['content_id'],
				$val,
				$key
			)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}

		return true;
	}

/**
 * 認証情報を取得する
 *
 * @param int $contentId  コンテンツID
 * @param string $field パスワードフィールド
 * @return bool|array
 */
	public function getAuthKey($contentId, $field) {
		$this->loadModels([
			'AuthorizationKeys' => 'AuthorizationKeys.AuthorizationKey',
		]);

		$options = [
			'conditions' => [
				'model' => 'MultidatabaseContent',
				'content_id' => $contentId,
				'additional_id' => $field
			],
			'recursive' => 0,
		];

		$authKey = $this->AuthorizationKeys->find('first', $options);

		if (! $authKey) {
			return false;
		}

		if (! isset($authKey['AuthorizationKey'])) {
			return false;
		}

		return $authKey['AuthorizationKey'];
	}

/**
 * Get File download URL
 * ファイルダウンロードURLを出力
 *
 * @return string
 */
	public function getFileURL() {
		$contentKey = $this->request->params['pass'][0];
		$options['field'] = $this->request->params['pass'][1];
		$options['size'] = Hash::get($this->request->params['pass'], 2, 'medium');
		return $this->Download->doDownload($contentKey, $options);
	}

/**
 * Get File Info
 * ファイル情報を出力
 *
 * @param array $content コンテンツ配列
 * @param string $fieldName フィールド名
 * @return array
 */
	public function getFileInfo($content, $fieldName = '') {
		$UploadFile = ClassRegistry::init('Files.UploadFile');
		$pluginKey = 'multidatabases';
		if (empty($fieldName)) {
			$files = [];
			for ($i = 1; $i <= 100; $i++) {
				$tmp = $UploadFile->getFile(
					$pluginKey,
					$content['MultidatabaseContent']['id'],
					'value' . $i . '_attach'
				);
				if (!empty($tmp)) {
					$files[] = $tmp;
				}
			}
			return $files;
		} else {
			$file = $UploadFile->getFile(
				$pluginKey,
				$content['MultidatabaseContent']['id'],
				$fieldName . '_attach'
			);
			return $file;
		}
	}

/**
 * Get File Info By ID
 * ファイル情報を出力(IDより)
 *
 * @param int $id コンテンツID
 * @param string $fieldName フィールド名
 * @return array|bool
 */
	public function getFileInfoById($id, $fieldName = '') {
		$this->loadModels([
			'AuthorizationKeys' => 'AuthorizationKeys.AuthorizationKey',
		]);

		$content = $this->MultidatabaseContent->getEditData([
			'MultidatabaseContent.id' => $id,
			'MultidatabaseContent.is_active' => true,
			'MultidatabaseContent.is_latest' => true,
		]);

		if (! $content) {
			return false;
		}
		return $this->getFileInfo($content, $fieldName);
	}

/**
 * Get File Info By ContentKey
 * ファイル情報を出力(コンテンツKeyより)
 *
 * @param string $key コンテンツKey
 * @param string $fieldName フィールド名
 * @return array|bool
 */
	public function getFileInfoByContentKey($key, $fieldName = '') {
		$this->loadModels([
			'MultidatabaseContent' => 'Multidatabases.MultidatabaseContent',
			'AuthorizationKeys' => 'AuthorizationKeys.AuthorizationKey',
		]);

		$content = $this->MultidatabaseContent->getEditData([
			'MultidatabaseContent.key' => $key,
			'MultidatabaseContent.is_active' => 1,
			'MultidatabaseContent.is_latest' => 1,
		]);

		if (! $content) {
			return false;
		}
		return $this->getFileInfo($content, $fieldName);
	}

/**
 * Remove File(s)
 * ファイルを削除する
 *
 * @param array $content コンテンツ配列
 * @param string $fieldName フィールド名
 * @return bool
 */
	public function removeFile($content, $fieldName = '') {
		$fileInfo = $this->getFileInfo($content, $fieldName);
		return $this->__removeFile($fileInfo, $fieldName);
	}

/**
 * Remove File(s)
 * ファイルを削除する（コンテンツIDより）
 *
 * @param int $id コンテンツID
 * @param string $fieldName フィールド名
 * @return bool
 */
	public function removeFileById($id, $fieldName = '') {
		$fileInfo = $this->getFileInfoById($id, $fieldName);

		if (empty($fileInfo)) {
			return true;
		}

		return $this->__removeFile($fileInfo, $fieldName);
	}

/**
 * Remove File(s)
 * ファイルを削除する（コンテンツKeyより）
 *
 * @param string $key コンテンツKey
 * @param string $fieldName フィールド名
 * @return bool
 */
	public function removeFileByContentKey($key, $fieldName = '') {
		$fileInfo = $this->getFileInfoByContentKey($key, $fieldName);

		if (empty($fileInfo)) {
			return true;
		}

		return $this->__removeFile($fileInfo, $fieldName);
	}

/**
 * Remove File(s)
 * ファイルを削除する（コンテンツKeyより）
 *
 * @param string $contentKey コンテンツKey
 * @return bool
 */
	public function removeFilesByContentKey($contentKey) {
		$UploadFile = ClassRegistry::init('Files.UploadFile');
		$pluginKey = 'multidatabases';

		$options = [
			'recursive' => -1,
			'fields' => [
				'UploadFile.id',
			],
			'conditions' => [
				'UploadFile.plugin_key' => $pluginKey,
				'UploadFile.content_key' => $contentKey,
			],
			'callbacks' => false,
		];

		$files = $UploadFile->find('all', $options);

		foreach ($files as $file) {
			$UploadFile->deleteUploadFile($file['UploadFile']['id']);
		}

		return true;
	}

/**
 * RemoveFile(s) Base
 * ファイルを削除する
 *
 * @param array $fileInfo アップロードファイル情報
 * @param string $fieldName フィールド名
 * @return bool
 */
	private function __removeFile($fileInfo, $fieldName) {
		$UploadFile = ClassRegistry::init('Files.UploadFile');

		if (empty($fileInfo)) {
			return false;
		}

		if (empty($fieldName)) {
			foreach ($fileInfo as $val) {
				$UploadFile->removeFile($val['UploadFilesContent']['content_id'], $val['UploadFile']['id']);
			}
		} else {
			$UploadFile->removeFile(
				$fileInfo['UploadFilesContent']['content_id'], $fileInfo['UploadFile']['id']);
		}

		return true;
	}

/**
 * 添付ファイル削除を行う
 *
 * @param array $removeAttachFields 削除対象フィールド配列
 * @param string $contentKey コンテンツキー
 * @return void
 * @throws InternalErrorException
 */
	public function removeAttachFile($removeAttachFields, $contentKey) {
		// ファイルを削除する
		if (!empty($removeAttachFields)) {
			foreach ($removeAttachFields as $val) {
				if (! $this->removeFileByContentKey($contentKey, $val)) {
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
			}
		}
	}
}
