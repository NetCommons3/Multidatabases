<?php
/**
 * MultidatabaseContentViewHelper Helper
 * 汎用データベースコンテンツ表示ヘルパー
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppHelper', 'View/Helper');

/**
 * MultidatabaseContentViewHelper Helper
 *
 * @author Tomoyuki OHNO (Ricksoft, Co., LTD.) <ohno.tomoyuki@ricksoft.jp>
 * @package NetCommons\Multidatabase\View\Helper
 *
 */
class MultidatabaseContentViewHelper extends AppHelper {

/**
 * 使用するHelpers
 *
 * @var array
 */
	public $helpers = [
		'NetCommons.Button',
		'NetCommons.NetCommonsHtml',
		'NetCommons.NetCommonsForm',
		'Form',
	];

/**
 * before render
 *
 * @param string $viewFile viewファイル
 * @return void
 * @link http://book.cakephp.org/2.0/ja/views/helpers.html#Helper::beforeRender Helper::beforeRender
 */
	public function beforeRender($viewFile) {
		parent::beforeRender($viewFile);
	}

/**
 * メタデータ項目ヘッダーの最大文字数を取得する
 *
 * @param array $metadatas メタデータ配列
 * @return int
 */
	public function chkItemHeaderMaxLength($metadatas) {
		$result = 8;

		if (empty($metadatas)) {
			return $result;
		}

		foreach ($metadatas as $metadata) {
			$tmp = mb_convert_encoding($metadata['name'], 'EUC-JP', 'UTF-8');
			$tmpLen = strlen(bin2hex($tmp)) / 2;

			if ($result < $tmpLen) {
				$result = $tmpLen;
			}
		}

		return $result;
	}

/**
 * 汎用データベースコンテンツ グループのHTMLを出力する(列)
 *
 * @param array $metadataGroups メタデータグループ配列
 * @param array $contents コンテンツ配列
 * @param int $position 位置（グループ）
 * @param int $colSize 列サイズ（1:1列,2:2列）
 * @param int $headerMaxLength ヘッダー最大文字数
 * @param null $viewMode 表示方法
 * @return string HTML
 */
	public function renderGroup(
		$metadataGroups, $contents, $position, $colSize = 1, $headerMaxLength = 5, $viewMode = null
	) {
		if (! isset($metadataGroups[$position])) {
			return '';
		}

		switch ($colSize) {
			case 2:
				// 2列レイアウト
				$element = 'MultidatabaseContents/view/view_content_group_c2';
				break;
			default:
				// 1列レイアウト
				$element = 'MultidatabaseContents/view/view_content_group_c1';
		}

		$tmp = $metadataGroups[$position];

		$metadatas = [];

		if ($viewMode === 'detail') {
			foreach ($tmp as $metadata) {
				if ($metadata['is_visible_detail'] === 1) {
					$metadatas[] = $metadata;
				}
			}
		} else {
			foreach ($tmp as $metadata) {
				if ($metadata['is_visible_list'] === 1) {
					$metadatas[] = $metadata;
				}
			}
		}

		if (empty($metadatas)) {
			return '';
		}

		return $this->_View->Element(
			$element,
			[
				'gMetadatas' => $metadatas,
				'gContents' => $contents,
				'colSize' => $colSize,
				'headerMaxLength' => $headerMaxLength
			]
		);
	}

/**
 * 汎用データベースコンテンツ コンテンツフッターのHTMLを出力する
 *
 * @param array $content コンテンツ配列
 * @param int $index 順番
 * @return string HTML
 */
	public function renderContentFooter($content, $index) {
		return $this->_View->Element(
			'MultidatabaseContents/view/view_content_footer',
			[
				'content' => $content,
				'index' => $index,
			]
		);
	}

/**
 * 汎用データベースコンテンツ レイアウトHTMLを出力する
 *
 * @param array $content コンテンツ配列
 * @return string $content HTML
 */
	public function renderContentLayout($content) {
		return $this->_View->Element(
			'MultidatabaseContents/view/view_content_layout',
			[
				'content' => $content,
			]
		);
	}

/**
 * 汎用データベースコンテンツ 一覧HTMLを出力する
 *
 * @return string $content HTML
 */
	public function renderContentsList() {
		return $this->_View->Element(
			'MultidatabaseContents/view/view_contents_list',
			[
				'viewMode' => 'list',
			]
		);
	}

/**
 * 汎用データベースコンテンツ 詳細HTMLを出力する
 *
 * @return string $content HTML
 */
	public function renderContentsDetail() {
		return $this->_View->Element(
			'MultidatabaseContents/view/view_content_detail',
			[
				'viewMode' => 'detail',
			]
		);
	}

/**
 * 汎用データベースコンテンツ アイテムのHTMLを出力する
 *
 * @param array $metadatas メタデータ配列
 * @return string HTML
 */
	public function renderGroupItems($metadatas) {
		return $this->_View->Element(
			'MultidatabaseContents/view/view_content_group_items',
			[
				'metadatas' => $metadatas,
			]
		);
	}

/**
 * 単一選択・複数選択のドロップダウンを出力する
 *
 * @param array $metadatas メタデータ配列
 * @param string $viewType 表示形式
 * @return string HTML tags
 */
	public function dropDownToggleSelect($metadatas, $viewType = 'index') {
		$params = $this->_View->Paginator->params;
		$named = $params['named'];
		$named['page'] = 1;
		$url = $named;
		$result = '';

		foreach ($metadatas as $metadata) {
			$colNo = 0;
			$name = '';
			$selections = [];

			if (
				(int)$metadata['is_visible_list'] === 1 && (
					$metadata['type'] === 'select' ||
					$metadata['type'] === 'checkbox'
				)
			) {
				$colNo = $metadata['col_no'];
				$name = $metadata['name'];

				$currentItemKey = 0;
				if (isset($named['value' . $colNo])) {
					$currentItemKey = $named['value' . $colNo];
				}

				$tmp = $metadata['selections'];
				if ($viewType === 'search') {
					$selections[0] = __d('multidatabases', 'All');
				} else {
					$selections[0] = $name;
				}
				foreach ($tmp as $val) {
					$selections[md5($val)] = $val;
				}

				if ($viewType === 'search') {
					$metaKey = 'value' . $metadata['col_no'];

					$options = [
						'type' => 'select',
						'label' => $metadata['name'],
						'options' => $selections
					];

					$result .= '<div>';
					$result .= $this->NetCommonsForm->input($metaKey, $options);
					$result .= '</div>';
				} else {
					$result .= $this->_View->element(
						'MultidatabaseContents/view/view_content_dropdown_select',
						[
							'dropdownCol' => 'value' . $colNo,
							'dropdownItems' => $selections,
							'currentItemKey' => $currentItemKey,
							'url' => $url,
						]
					);
				}
			}
		}
		return $result;
	}

/**
 * ソートのドロップダウンを出力する
 *
 * @param array $metadatas メタデータ配列
 * @param string $viewType 表示形式
 * @return string HTML tags
 */
	public function dropDownToggleSort($metadatas, $viewType = 'index') {
		$params = $this->_View->Paginator->params;
		$named = $params['named'];
		$url = $named;

		$currentItemKey = 0;
		if (isset($named['sort_col'])) {
			$currentItemKey = $named['sort_col'];
		}

		// 一覧表示で並び順パラメータなかったらフレーム設定のデフォルトにする
		if (!$currentItemKey && $viewType === 'index') {
			$currentItemKey = $this->_View->viewVars['multidatabaseFrameSetting']['default_sort_type'];
		}

		// セレクトボックス取得
		$selections = $this->__getSelections($metadatas);

		$result = '';

		switch ($viewType) {
			case 'search':
				$result .= '<div>';
				$result .= '<label for="sort" class="control-label">';
				$result .= __d('multidatabases', 'Sort order');
				$result .= '</label>';
				$metaKey = 'sort';
				$options = [
					'type' => 'select',
					'options' => $selections
				];
				$result .= $this->NetCommonsForm->input($metaKey, $options);
				$result .= '</div>';
				return $result;
			case 'frame_setting':
				$selections[0] = __d('multidatabases', 'Unspecified');
				$options = [
						'type' => 'select',
						'label' => __d('multidatabases', 'Sort order'),
						'options' => $selections
					];
				return $this->NetCommonsForm->input('MultidatabaseFrameSetting.default_sort_type', $options);
			default:
				return $this->_View->element(
					'MultidatabaseContents/view/view_content_dropdown_sort',
					[
						'dropdownItems' => $selections,
						'currentItemKey' => $currentItemKey,
						'url' => $url,
					]
				);
		}
	}

/**
 * セレクトボックス取得
 *
 * @param array $metadatas メタデータ配列
 * @return array セレクトボックス
 */
	private function __getSelections($metadatas) {
		$selections = [];
		$selections[0] = __d('multidatabases', 'Sort');

		foreach ($metadatas as $metadata) {
			$colNo = 0;
			$name = '';
			if (
				(int)$metadata['is_sortable'] === 1 &&
				$metadata['type'] <> 'created' &&
				$metadata['type'] <> 'updated'
			) {
				$colNo = $metadata['col_no'];
				$name = $metadata['name'];
				$selections['value' . $colNo]
					= $name . '(' . __d('multidatabases', 'Ascending') . ')';
				$selections['value' . $colNo . '_desc']
					= $name . '(' . __d('multidatabases', 'Descending') . ')';
			}
		}

		$selections['created']
			= __d('multidatabases', 'Created date') .
			'(' . __d('multidatabases', 'Ascending') . ')';
		$selections['created_desc']
			= __d('multidatabases', 'Created date') .
			'(' . __d('multidatabases', 'Descending') . ')';
		$selections['modified']
			= __d('multidatabases', 'Modified date') .
			'(' . __d('multidatabases', 'Ascending') . ')';
		$selections['modified_desc']
			= __d('multidatabases', 'Modified date') .
			'(' . __d('multidatabases', 'Descending') . ')';

		return $selections;
	}
}
