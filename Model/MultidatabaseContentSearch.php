<?php
/**
 * MultidatabaseContentSearch Model
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft, Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MultidatabasesAppModel', 'Multidatabases.Model');
App::uses('MultidatabaseContentSearchCondModel', 'MultidatabaseContentSearchCond.Model');
App::uses('MultidatabaseMetadataModel', 'MultidatabaseMetadata.Model');

/**
 * MultidatabaseContentSearch Model
 *
 * @author Tomoyuki OHNO (Ricksoft, Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @package NetCommons\Multidatabases\Model
 *
 */
class MultidatabaseContentSearch extends MultidatabasesAppModel {

/**
 * Custom database table name
 *
 * @var string
 */
	public $useTable = false;

/**
 * 検索対象のメタデータフィールド一覧を取得する
 *
 * @param int $multidatabaseId 汎用データベースID
 * @return array|bool
 * @throws InternalErrorException
 */
	public function getSearchMetadatas($multidatabaseId = 0) {
		$this->loadModels([
			'MultidatabaseMetadata' => 'Multidatabases.MultidatabaseMetadata'
		]);

		// 「検索の対象に含める(is_searchable)」は、全てOFFの場合もあるため、空でも例外にしない
		$metadatas = $this->MultidatabaseMetadata->getMetadatas(
			$multidatabaseId,
			[
				'is_searchable' => 1
			]
		);

		$result = [];
		foreach ($metadatas as $metadata) {
			$result[] = 'value' . $metadata['MultidatabaseMetadata']['col_no'];
		}

		return $result;
	}

/**
 * 検索結果を出力するための条件設定を行う
 *
 * @param array $query クエリ(GETより取得)
 * @return bool|array
 */
	public function getSearchConds($query = []) {
		$this->loadModels([
			'MultidatabaseContentSearchCond' => 'Multidatabases.MultidatabaseContentSearchCond',
		]);

		if (empty($query)) {
			return false;
		}

		$conditions = [];

		// 開始日時、終了日時の条件設定
		$conditions += $this->MultidatabaseContentSearchCond->getCondStartEndDt($query);

		// ステータス条件設定
		$conditions += $this->MultidatabaseContentSearchCond->getCondStatus($query);

		// チェックボックスの値を取得して条件設定
		$conditions += $this->getCondSelect(
			$this->MultidatabaseContentSearchCond->getCondSelVal($query)
		);

		// キーワード検索条件設定
		$conditions += $this->MultidatabaseContentSearchCond->getCondKeywords($query);

		// ソート順設定
		$result = [
			'conditions' => $conditions,
			'order' => $this->getCondSortOrder()
		];

		return $result;
	}

/**
 * 複数選択、単一選択の絞込条件出力
 *
 * @param array $values 値
 * @return array
 */
	public function getCondSelect($values = []) {
		$this->loadModels([
			'MultidatabaseMetadata' => 'Multidatabases.MultidatabaseMetadata',
			'MultidatabaseContentSearchCond' => 'Multidatabases.MultidatabaseContentSearchCond',
		]);

		if (empty($values)) {
			return [];
		}

		$metadatas = $this->MultidatabaseMetadata->getEditMetadatas();
		$result = [];

		$valueKey = null;
		foreach ($metadatas as $metadata) {
			$valueKey = $this->MultidatabaseContentSearchCond->getCondValKey($metadata);

			if (
				! is_null($valueKey) &&
				isset($values[$valueKey]) &&
				$values[$valueKey] !== '0'
			) {
				switch ($metadata['type']) {
					case 'select':
						$result +=
							$this->MultidatabaseContentSearchCond->getCondSelSelect(
								$metadata['selections'], $values, $valueKey
							);
						break;
					case 'checkbox':
						$result[] = $this->MultidatabaseContentSearchCond->getCondSelCheck(
								$metadata['selections'], $values, $valueKey
							);
						break;
				}
			}
		}

		return $result;
	}

/**
 * ソート条件出力
 *
 * @param string $sortCol ソートするカラム
 * @return string
 */
	public function getCondSortOrder($sortCol = '') {
		if (empty($sortCol)) {
			$sortCol = null;
		}

		if (
			isset($sortCol) &&
			!is_null($sortCol) &&
			(
				strstr($sortCol, 'value') <> false ||
				// ソート順は created_desc, modified_descがありえるため、valueと同様にstrstrで比較する
				//in_array($sortCol, ['created', 'modified'])
				strstr($sortCol, 'created') <> false ||
				strstr($sortCol, 'modified') <> false
			)
		) {
			if (strstr($sortCol, '_desc')) {
				$sortCol = str_replace('_desc', '', $sortCol);
				$sortColDir = 'desc';
			} else {
				$sortCol = $sortCol;
				$sortColDir = 'asc';
			}
		} else {
			$sortCol = 'created';
			$sortColDir = 'desc';
		}

		$sortOrder = ['MultidatabaseContent.' . $sortCol => $sortColDir];

		return $sortOrder;
	}
}
