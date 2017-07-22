<?php
/**
 * MultidatabaseMetadataEditCnv Model
 * 汎用データベースメタデータ定義に関するモデル処理（値変換処理関連）
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * MultidatabaseMetadataEditCnv Model
 *
 * @author Tomoyuki OHNO (Ricksoft, Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @package NetCommons\Multidatabases\Model
 *
 */
class MultidatabaseMetadataEditCnv extends MultidatabasesAppModel {

/**
 * Custom database table name
 *
 * @var string
 */
	public $useTable = false;

/**
 * Data type
 *
 * @var array
 */
	public $dataType = [
		'id' => 'numeric',
		'key' => 'string',
		'name' => 'string',
		'multidatabase_id' => 'numeric',
		'language_id' => 'numeric',
		'position' => 'numeric',
		'rank' => 'numeric',
		'col_no' => 'numeric',
		'type' => 'string',
		'selections' => 'json',
		'is_require' => 'boolean',
		'is_title' => 'boolean',
		'is_searchable' => 'boolean',
		'is_sortable' => 'boolean',
		'is_file_dl_require_auth' => 'boolean',
		'is_visible_file_dl_counter' => 'boolean',
		'is_visible_field_name' => 'boolean',
		'is_visible_list' => 'boolean',
		'is_visible_detail' => 'boolean',
		'created' => 'datetime',
		'created_user' => 'numeric',
		'modified' => 'datetime',
		'modified_user' => 'numeric',
	];

/**
 * 選択肢をJSON化する（保存データのため）
 *
 * @param array $metadata メタデータ配列
 * @return string
 */
	public function cnvMetaSelToJson($metadata) {
		if (!empty($metadata['selections']) && is_array($metadata['selections'])) {
			$selectionsJson = json_encode($metadata['selections']);
			return $selectionsJson;
		}
		return '';
	}

/**
 * Bool型のデータをIntに変換する（保存データのため）
 *
 * @param array $metadata メタデータ配列
 * @return array
 */
	public function cnvMetaBoolToInt($metadata) {
		// メタデータの変換 (on => 1, keyが存在しない => 0)
		foreach ([
			'is_require',
			'is_title',
			'is_searchable',
			'is_sortable',
			'is_file_dl_require_auth',
			'is_visible_file_dl_counter',
			'is_visible_field_name',
			'is_visible_list',
			'is_visible_detail'
		] as $metaKey) {
			$tmp = 0;
			if (isset($metadata[$metaKey])) {
				if (
					$metadata[$metaKey] === 'on' ||
					$metadata[$metaKey] === 1 ||
					$metadata[$metaKey] === true
				) {
					$tmp = 1;
				}
			}
			$metadata[$metaKey] = $tmp;
		}

		return $metadata;
	}

/**
 * Normalize edit metadatas type for JSON
 * メタデータの型を調整する（JSONのため）
 *
 * @param array $metadatas メタデータ配列
 * @return array|bool
 */
	public function normalizeEditMetadatasType($metadatas = []) {
		if (empty($metadatas)) {
			return false;
		}

		foreach ($metadatas as $key => $metadata) {
			$result[$key] = $this->__normalizeEditMetadataValue($metadata, $key);

			if ($this->dataType[$key] === 'json') {
				$result[$key] = $this->__normalizeEditMetadataJson($metadata);
			}
		}
		return $result;
	}

/**
 * Normalize edit metadata value
 * メタデータの型を調整する(JSON)
 *
 * @param string $metadata メタデータ値
 * @param string $key メタデータキー
 * @return array
 */
	private function __normalizeEditMetadataJson($metadata) {
		if (! empty($metadata)) {
			if (is_array($metadata)) {
				return $metadata;
			}
			return json_decode($metadata, true);
		}
		return [];
	}

/**
 * Normalize edit metadata value
 * メタデータの型を調整する(JSON以外)
 *
 * @param string|int|bool $metadata メタデータ値
 * @param string $key メタデータキー
 * @return string|int|bool
 */
	private function __normalizeEditMetadataValue($metadata, $key) {
		if ($metadata == 'on') {
			$metadata = 1;
		}

		if ($metadata == 'off') {
			$metadata = 0;
		}

		if (in_array($this->dataType[$key], [
			'numeric', 'integer'
		])) {
			return (int)$metadata;
		}

		if (in_array($this->dataType[$key], [
			'string', 'text'
		])) {
			return (string)$metadata;
		}

		if ($this->dataType[$key] === 'boolean') {
			if ($metadata) {
				return 1;
			}
			return 0;
		}

		// 該当しない場合はそのまま返す
		return $metadata;
	}
}
