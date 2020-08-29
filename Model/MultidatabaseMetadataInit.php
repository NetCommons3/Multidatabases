<?php
/**
 * MultidatabaseMetadataInit Model
 * 汎用データベースメタデータ初期値定義に関するモデル処理
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MultidatabasesAppModel', 'Multidatabases.Model');

/**
 * MultidatabaseMetadataInit Model
 *
 * @author Tomoyuki OHNO (Ricksoft, Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @package NetCommons\Multidatabases\Model
 */
class MultidatabaseMetadataInit extends MultidatabasesAppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = false;

/**
 * Init Metadata Values
 * @var array
 */
	private $__initMetadataValues = [
		// タイトル
		0 => [
			'id' => '',
			'key' => '',
			'position' => 0,
			'rank' => 0,
			'col_no' => 1,
			'type' => 'text',
			'selections' => '',
			'is_require' => 1,
			'is_title' => 1,
			'is_searchable' => 1,
			'is_sortable' => 1,
			'is_file_dl_require_auth' => 0,
			'is_visible_file_dl_counter' => 0,
			'is_visible_field_name' => 1,
			'is_visible_list' => 1,
			'is_visible_detail' => 1,
		],
		// ふりがな
		1 => [
			'id' => '',
			'key' => '',
			'position' => 0,
			'rank' => 1,
			'col_no' => 2,
			'type' => 'text',
			'selections' => '',
			'is_require' => 0,
			'is_title' => 0,
			'is_searchable' => 1,
			'is_sortable' => 0,
			'is_file_dl_require_auth' => 0,
			'is_visible_file_dl_counter' => 0,
			'is_visible_field_name' => 1,
			'is_visible_list' => 0,
			'is_visible_detail' => 1,
		],
		// カテゴリ
		2 => [
			'id' => '',
			'key' => '',
			'position' => 0,
			'rank' => 2,
			'col_no' => 3,
			'type' => 'select',
			'is_require' => 1,
			'is_title' => 0,
			'is_searchable' => 1,
			'is_sortable' => 0,
			'is_file_dl_require_auth' => 0,
			'is_visible_file_dl_counter' => 0,
			'is_visible_field_name' => 1,
			'is_visible_list' => 1,
			'is_visible_detail' => 1,
		],
		// 概要
		3 => [
			'id' => '',
			'key' => '',
			'position' => 0,
			'rank' => 3,
			'col_no' => 80,
			'type' => 'wysiwyg',
			'selections' => '',
			'is_require' => 1,
			'is_title' => 0,
			'is_searchable' => 1,
			'is_sortable' => 0,
			'is_file_dl_require_auth' => 0,
			'is_visible_file_dl_counter' => 0,
			'is_visible_field_name' => 1,
			'is_visible_list' => 1,
			'is_visible_detail' => 1,
		],
		// 連絡先
		4 => [
			'id' => '',
			'key' => '',
			'position' => 1,
			'rank' => 0,
			'col_no' => 81,
			'type' => 'textarea',
			'selections' => '',
			'is_require' => 0,
			'is_title' => 0,
			'is_searchable' => 1,
			'is_sortable' => 1,
			'is_file_dl_require_auth' => 0,
			'is_visible_file_dl_counter' => 0,
			'is_visible_field_name' => 1,
			'is_visible_list' => 0,
			'is_visible_detail' => 1,
		],
		// 担当者
		5 => [
			'id' => '',
			'key' => '',
			'position' => 1,
			'rank' => 1,
			'col_no' => 4,
			'type' => 'text',
			'selections' => '',
			'is_require' => 0,
			'is_title' => 0,
			'is_searchable' => 1,
			'is_sortable' => 0,
			'is_file_dl_require_auth' => 0,
			'is_visible_file_dl_counter' => 0,
			'is_visible_field_name' => 1,
			'is_visible_list' => 0,
			'is_visible_detail' => 1,
		],
		// ホームページ
		6 => [
			'id' => '',
			'key' => '',
			'position' => 1,
			'rank' => 2,
			'col_no' => 5,
			'type' => 'text',
			'selections' => '',
			'is_require' => 0,
			'is_title' => 0,
			'is_searchable' => 1,
			'is_sortable' => 0,
			'is_file_dl_require_auth' => 0,
			'is_visible_file_dl_counter' => 0,
			'is_visible_field_name' => 1,
			'is_visible_list' => 0,
			'is_visible_detail' => 1,
		],
		// 対象
		7 => [
			'id' => '',
			'key' => '',
			'position' => 1,
			'rank' => 3,
			'col_no' => 6,
			'type' => 'select',
			'is_require' => 0,
			'is_title' => 0,
			'is_searchable' => 1,
			'is_sortable' => 0,
			'is_file_dl_require_auth' => 0,
			'is_visible_file_dl_counter' => 0,
			'is_visible_field_name' => 1,
			'is_visible_list' => 0,
			'is_visible_detail' => 1,
		],
		// 資料
		8 => [
			'id' => '',
			'key' => '',
			'position' => 1,
			'rank' => 4,
			'col_no' => 7,
			'type' => 'file',
			'selections' => 0,
			'is_require' => 0,
			'is_title' => 0,
			'is_searchable' => 0,
			'is_sortable' => 0,
			'is_file_dl_require_auth' => 0,
			'is_visible_file_dl_counter' => 1,
			'is_visible_field_name' => 1,
			'is_visible_list' => 0,
			'is_visible_detail' => 1,
		],
		// コメント
		9 => [
			'id' => '',
			'key' => '',
			'position' => 2,
			'rank' => 0,
			'col_no' => 82,
			'type' => 'textarea',
			'selections' => '',
			'is_require' => 0,
			'is_title' => 0,
			'is_searchable' => 1,
			'is_sortable' => 0,
			'is_file_dl_require_auth' => 0,
			'is_visible_file_dl_counter' => 0,
			'is_visible_field_name' => 1,
			'is_visible_list' => 0,
			'is_visible_detail' => 1,
		],
		// 検索キーワード
		10 => [
			'id' => '',
			'key' => '',
			'position' => 2,
			'rank' => 1,
			'col_no' => 8,
			'type' => 'text',
			'selections' => '',
			'is_require' => 0,
			'is_title' => 0,
			'is_searchable' => 1,
			'is_sortable' => 0,
			'is_file_dl_require_auth' => 0,
			'is_visible_file_dl_counter' => 0,
			'is_visible_field_name' => 1,
			'is_visible_list' => 0,
			'is_visible_detail' => 0,
		],
		// 画像
		11 => [
			'id' => '',
			'key' => '',
			'position' => 3,
			'rank' => 0,
			'col_no' => 9,
			'type' => 'image',
			'selections' => '',
			'is_require' => 0,
			'is_title' => 0,
			'is_searchable' => 0,
			'is_sortable' => 0,
			'is_file_dl_require_auth' => 0,
			'is_visible_file_dl_counter' => 0,
			'is_visible_field_name' => 1,
			'is_visible_list' => 0,
			'is_visible_detail' => 1,
		],
	];

/**
 * Get initial metadatas
 * 初期データ
 *
 * @return array
 */
	public function getInitMetadatas() {
		$initMetadatas = $this->__initMetadatas();
		$result = [];
		foreach ($initMetadatas as $key => $initMetadata) {
			$result[$key] = $initMetadata;
			$result[$key]['language_id'] = Current::read('Language.id');
		}
		return $result;
	}

/**
 * Init Metadatas
 *
 * @return array
 */
	private function __initMetadatas() {
		$metadataValues = $this->__initMetadataValues;

		$metadataValues[0]['name'] = __d('multidatabases', 'Title');
		$metadataValues[1]['name'] = __d('multidatabases', 'Phonetic');
		$metadataValues[2]['name'] = __d('multidatabases', 'Category');
		$metadataValues[2]['selections'] = [
			__d('multidatabases', 'National language'),
			__d('multidatabases', 'Mathematic'),
			__d('multidatabases', 'Science'),
			__d('multidatabases', 'Society'),
			__d('multidatabases', 'General'),
			__d('multidatabases', 'Music'),
			__d('multidatabases', 'Arts and crafts'),
			__d('multidatabases', 'Sports'),
			__d('multidatabases', 'Home economics'),
			__d('multidatabases', 'Morals'),
			__d('multidatabases', 'English'),
		];
		$metadataValues[3]['name'] = __d('multidatabases', 'Brief');
		$metadataValues[4]['name'] = __d('multidatabases', 'Contact');
		$metadataValues[5]['name'] = __d('multidatabases', 'Contact person name');
		$metadataValues[6]['name'] = __d('multidatabases', 'Home page');
		$metadataValues[7]['name'] = __d('multidatabases', 'Target');
		$metadataValues[7]['selections'] = [
				__d('multidatabases', 'Junior school'),
				__d('multidatabases', 'Junior high school'),
				__d('multidatabases', 'High school'),
			];
		$metadataValues[8]['name'] = __d('multidatabases', 'Reference');
		$metadataValues[9]['name'] = __d('multidatabases', 'Comment');
		$metadataValues[10]['name'] = __d('multidatabases', 'Search keywords');
		$metadataValues[11]['name'] = __d('multidatabases', 'Image');

		return $metadataValues;
	}
}

