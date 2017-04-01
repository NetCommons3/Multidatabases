<?php
/**
 * MultidatabaseMetadataSetting Model
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft, Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MultidatabasesAppModel', 'Multidatabases.Model');

/**
 * MultidatabaseMetadataSetting Model
 *
 * @author Tomoyuki OHNO (Ricksoft, Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @package NetCommons\Multidatabases\Model
 */
class MultidatabaseMetadataSetting extends MultidatabasesAppModel {

	public $metadatas = [];

	/**
	 * Use table
	 *
	 * @var mixed False or table name
	 */
	public $useTable = 'multidatabase_metadata_settings';

	/**
	 * Validation rules
	 *
	 * @var array
	 */
	public $validate = [];

	/**
	 * belongsTo associations
	 *
	 * @var array
	 */
	public $belongsTo = [
		'Multidatabase' => [
			'className' => 'Multidatabases.Multidatabase',
			'foreignKey' => 'multidatabase_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
		],
		'Language' => [
			'className' => 'M17n.Language',
			'foreignKey' => 'language_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
		],
	];

/**
 * 自動採番の値の取得と更新
 *
 * @param int $multidatabaseId 汎用DBID
 * @return bool
 */
	public function updateAutoNum($multidatabaseId = 0) {
		if ($multidatabaseId === 0) {
			$multidatabase = $this->multidatabase->getMultidatabase();
			$conditions['multidatabase_id'] = $multidatabase['Multidatabase']['id'];
		} else {
			$conditions['multidatabase_id'] = $multidatabaseId;
		}

		$metadataSetting = $this->find('first',
			[
				'conditions' => $conditions,
				'recursive' => -1,
			]
		);

		if (! $metadataSetting) {
			return '';
		}

		$currentNum = (int)$metadataSetting['MultidatabaseMetadataSetting']['auto_number_sequence'];
		$newNum = $currentNum + 1;
		$metadataSetting['MultidatabaseMetadataSetting']['auto_number_sequence'] = $newNum;

		if (!$this->saveAll($metadataSetting)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
	}

/**
 * タイトルとするメタデータのIDを登録（更新）する
 *
 * @param int $multidatabaseId 汎用DBID
 * @param int $metadataId メタデータID
 * @return bool
 */
	public function updateTitleId($multidatabaseId = 0, $metadataId = 0) {
		$metadataId = (int)$metadataId;
		if ($metadataId === 0) {
			return false;
		}

		if ($multidatabaseId === 0) {
			$multidatabase = $this->multidatabase->getMultidatabase();
			$conditions['multidatabase_id'] = $multidatabase['Multidatabase']['id'];
		} else {
			$conditions['multidatabase_id'] = $multidatabaseId;
		}

		$metadataSetting = $this->find('first',
			[
				'conditions' => $conditions,
				'recursive' => -1,
			]
		);

		$metadataSetting['MultidatabaseMetadataSetting']['multidatabase_metadata_title_id'] = $metadataId;

		if (!$this->saveAll($metadataSetting)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
	}
}
