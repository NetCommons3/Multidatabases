<?php
/**
 * MultidatabaseContentSearchCond Model
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft, Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MultidatabasesAppModel', 'Multidatabases.Model');
App::uses('MultidatabaseContentSearchModel', 'MultidatabaseContentSearch.Model');

/**
 * MultidatabaseContentSearchCond Model
 *
 * @author Tomoyuki OHNO (Ricksoft, Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @package NetCommons\Multidatabases\Model
 *
 */
class MultidatabaseContentSearchCond extends MultidatabasesAppModel {

/**
 * Custom database table name
 *
 * @var string
 */
	public $useTable = false;

/**
 * 単一選択、複数選択に該当する値を出力する
 *
 * @param array $query クエリ(GETより取得)
 * @return array
 */
	public function getCondSelVal($query) {
		$selVal = [];
		foreach ($query as $val) {
			switch($val['type']) {
				case 'checkbox':
				case 'radio':
				case 'select':
					$selVal[$val['field']] = $val['value'];
					break;
			}
		}

		return $selVal;
	}

/**
 * 開始日時、終了日時の条件出力
 *
 * @param array $query クエリ(GETより取得)
 * @return array
 */
	public function getCondStartEndDt($query) {
		$conditions = [];

		//サーバタイムゾーンの日時に変換
		$date = new DateTime($query['start_dt']['value']);
		$createdStart = (new NetCommonsTime())->toServerDatetime($date->format('Y-m-d H:i:s'));
		$date = new DateTime($query['end_dt']['value']);
		$date->modify('+59 second');
		$createdEnd = (new NetCommonsTime())->toServerDatetime($date->format('Y-m-d H:i:s'));

		if (
			!empty($query['start_dt']['value']) &&
			!empty($query['end_dt']['value'])
		) {
			$conditions['MultidatabaseContent.created between ? and ?'] = [
				$createdStart,
				$createdEnd
			];
		} else {
			if (!empty($query['start_dt']['value'])) {
				$conditions['MultidatabaseContent.created >='] = $createdStart;
			}
			if (!empty($query['end_dt']['value'])) {
				$conditions['MultidatabaseContent.created <='] = $createdEnd;
			}
		}

		return $conditions;
	}

/**
 * ステータス条件の出力
 *
 * @param array $query クエリ(GETより取得)
 * @return array
 */
	public function getCondStatus($query) {
		$conditions = [];
		if (!empty($query['status']['value'])) {
			switch ($query['status']['value']) {
				case 'pub':
					$conditions['MultidatabaseContent.status'] = 1;
					break;
				case 'unpub':
					$conditions['or'] = [
						['MultidatabaseContent.status' => 2],
						['MultidatabaseContent.status' => 3]
					];
					break;
			}
		}

		return $conditions;
	}

/**
 * キーワード検索条件の出力
 *
 * @param array $query クエリ(GETより取得)
 * @return array
 */
	public function getCondKeywords($query = []) {
		if (empty($query)) {
			return [];
		}

		// キーワード検索時の検索の種類を設定
		$condType = 'and';

		if (isset($query['type']['value'])) {
			$condType = $query['type']['value'];
		}

		// キーワードの値を取得して条件設定
		$keywords = $this->__getKwValNormalize($query, $condType);

		// キーワード検索条件の取得
		$result = $this->__makeKwCond($keywords, $condType);

		return $result;
	}

/**
 * 条件に該当するフィールドを出力する
 *
 * @param array $metadata メタデータ配列
 * @return null|string
 */
	public function getCondValKey($metadata) {
		switch ($metadata['type']) {
			case 'select':
			case 'checkbox':
				return 'value' . $metadata['col_no'];
		}
		return null;
	}

/**
 * 複数選択肢の条件設定を生成
 *
 * @param array $selections 選択肢
 * @param array $values 値
 * @param string $valueKey フィールド
 * @return array
 */
	public function getCondSelCheck($selections, $values, $valueKey) {
		foreach ($selections as $selection) {
			if (md5($selection) === $values[$valueKey]) {
				$result['or'] = [
					['MultidatabaseContent.' . $valueKey => "{$selection}"],
					['MultidatabaseContent.' . $valueKey . ' like' => "%{$selection}||%"],
					['MultidatabaseContent.' . $valueKey . ' like' => "%||{$selection}%"],
				];
				return $result;
			}
		}
		// 一覧表示でプルダウンでセレクトボックス値を選択後、表示されたURLの値を変えると内部エラーになるため、空配列を返す
		return [];
	}

/**
 * 単一選択肢の条件設定を生成
 *
 * @param array $selections 選択肢
 * @param array $values 値
 * @param string $valueKey フィールド
 * @return array
 */
	public function getCondSelSelect($selections, $values, $valueKey) {
		foreach ($selections as $selection) {
			if (md5($selection) === $values[$valueKey]) {
				$result['MultidatabaseContent.' . $valueKey] = "{$selection}";
				return $result;
			}
		}
		// 一覧表示でプルダウンでチェックボックス値を選択後、表示されたURLの値を変えると内部エラーになるため、空配列を返す
		return [];
	}

/**
 * キーワード検索条件の生成
 *
 * @param array $keywords キーワード文字列
 * @param string $condType 検索条件(and, or ,phrase)
 * @return array
 */
	private function __makeKwCond($keywords, $condType) {
		$this->loadModels([
			'MultidatabaseContentSearch' => 'Multidatabases.MultidatabaseContentSearch',
		]);

		// キーワード文字列を配列に変換
		$arrKeywords = $this->__kwValToArr($keywords);

		// 検索対象のメタデータを取得
		$searchMetadatas = $this->MultidatabaseContentSearch->getSearchMetadatas();

		$result = [];
		if (!empty($arrKeywords)) {
			foreach ($searchMetadatas as $metaField) {
				$tmpConds = [];
				if (
					$condType === 'phrase' ||
					count($arrKeywords) === 1
				) {
					$tmpConds = [
						$metaField . ' like' => '%' . $keywords . '%'
					];
				} else {
					foreach ($arrKeywords as $keyword) {
						$tmpConds[$condType][] = [
							$metaField . ' like' => '%' . $keyword . '%'
						];
					}
				}
				$result['or'][] = $tmpConds;
			}
		}

		return $result;
	}

/**
 * キーワード文字列のノーマライズ
 *
 * @param array $query クエリ(GETより取得)
 * @param string $condType 検索条件(and, or ,phrase)
 * @return array
 */
	private function __getKwValNormalize($query, $condType) {
		$keywords = '';
		if (isset($query['keywords']['value'])) {
			$keywords = trim($query['keywords']['value']);
			if ($condType !== 'phrase') {
				$keywords = str_replace('　', ' ', $keywords);
			}
		}

		return $keywords;
	}

/**
 * キーワード文字列を配列に変換
 *
 * @param string $keywords キーワード文字列
 * @return array
 */
	private function __kwValToArr($keywords) {
		$arrKeywords = [];
		if (!empty($keywords)) {
			$arrKeywords = explode(' ', $keywords);
		}

		return $arrKeywords;
	}
}
