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
 * 汎用データベースコンテンツ グループのHTMLを出力する(列)
 *
 * @param array $metadataGroups メタデータグループ配列
 * @param array $contents コンテンツ配列
 * @param int $position 位置（グループ）
 * @param int $colSize 列サイズ（1:1列,2:2列）
 * @param null $viewMode 表示方法
 * @return string HTML
 */
	public function renderGroup(
		$metadataGroups, $contents, $position, $colSize = 1, $viewMode = null
	) {
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
 * 選択肢元データ（JSON）をArrayに変換する
 *
 * @param string $selections 選択肢（JSON）
 * @return array
 */
	public function convertSelectionsToArray($selections) {
		foreach (explode('||', $selections) as $selection) {
			$result[md5($selection)] = $selection;
		}
		return $result;
	}

/**
 * フォーム部品に合った表示出力する
 *
 * @param array $content コンテンツ配列
 * @param array $metadata メタデータ配列
 * @return string HTML
 */
	public function renderViewElement($content, $metadata) {
		$elementType = $metadata['type'];
		$colNo = $metadata['col_no'];

		$result = '';
		switch ($elementType) {
			case 'text':
				$result .= $this->renderViewElementText($content, $colNo);
				break;
			case 'textarea':
				$result .= $this->renderViewElementTextArea($content, $colNo);
				break;
			case 'link':
				$result .= $this->renderViewElementLink($content, $colNo);
				break;
			case 'radio':
				$result .= $this->renderViewElementRadio($content, $colNo);
				break;
			case 'select':
				$result .= $this->renderViewElementSelect($content, $colNo);
				break;
			case 'checkbox':
				$result .= $this->renderViewElementCheckBox($content, $colNo);
				break;
			case 'wysiwyg':
				$result .= $this->renderViewElementWysiwyg($content, $colNo);
				break;
			case 'file':
				$result .= $this->renderViewElementFile($content, $colNo,
					$metadata['is_visible_file_dl_counter']);
				break;
			case 'image':
				$result .= $this->renderViewElementImage($content, $colNo);
				break;
			case 'autonumber':
				$result .= $this->renderViewElementAutoNumber($content);
				break;
			case 'mail':
				$result .= $this->renderViewElementEmail($content, $colNo);
				break;
			case 'date':
				$result .= $this->renderViewElementDate($content, $colNo);
				break;
			case 'created':
				$result .= $this->renderViewElementCreated($content);
				break;
			case 'updated':
				$result .= $this->renderViewElementUpdated($content);
				break;
			case 'hidden':
				$result .= $this->renderViewElementHidden($content, $colNo);
				break;
			default:
				$result .= $this->renderViewElementText($content, $colNo);
				break;
		}
		return $result;
	}

/**
 * 汎用的な値出力方法
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return string HTML
 */
	public function renderViewElementGeneral($content, $colNo) {
		$value = (string)trim(h($content['MultidatabaseContent']['value' . $colNo]));

		if ($value === '') {
			return '';
		}

		if (strstr($value, '||') <> false) {
			return str_replace('||', '<br>', $value);
		}

		return $value;
	}

/**
 * テキストボックスの値を出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return string HTML
 */
	public function renderViewElementText($content, $colNo) {
		return $this->renderViewElementGeneral($content, $colNo);
	}

/**
 * WYSIWYGの値を出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return string HTML
 */
	public function renderViewElementWysiwyg($content, $colNo) {
		return $content['MultidatabaseContent']['value' . $colNo];
	}

/**
 * テキストエリアの値を出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return string HTML
 */
	public function renderViewElementTextArea($content, $colNo) {
		$value = $this->renderViewElementGeneral($content, $colNo);
		return nl2br($value);
	}

/**
 * チェックボックスの値を出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return string HTML
 */
	public function renderViewElementCheckBox($content, $colNo) {
		return $this->renderViewElementGeneral($content, $colNo);
	}

/**
 * ラジオボタンの値を出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return string HTML
 */
	public function renderViewElementRadio($content, $colNo) {
		return $this->renderViewElementGeneral($content, $colNo);
	}

/**
 * セレクトボックスの値を出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return string HTML
 */
	public function renderViewElementSelect($content, $colNo) {
		return $this->renderViewElementGeneral($content, $colNo);
	}

/**
 * 日付の値を出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return string HTML
 */
	public function renderViewElementDate($content, $colNo) {
		$value = $this->renderViewElementGeneral($content, $colNo);
		return date("Y/m/d H:i:s", strtotime($value));
	}

/**
 * ダウンロードファイルが存在するかチェックする
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return bool
 */
	public function getFileInfo($content, $colNo) {
		if (
			empty($content['MultidatabaseContent']['id']) ||
			empty($colNo)
		) {
			return false;
		}

		$UploadFile = ClassRegistry::init('Files.UploadFile');
		$pluginKey = 'multidatabases';
		$file = $UploadFile->getFile(
			$pluginKey,
			$content['MultidatabaseContent']['id'],
			'value' . $colNo . '_attach'
		);

		if (! empty($file)) {
			return $file;
		}

		return false;
	}

/**
 * ファイルアップロードの値を出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @param int $showCounter カウンターを表示するか 1:表示する
 * @return string HTML
 */
	public function renderViewElementFile($content, $colNo, $showCounter = 0) {
		// アップロードされたファイルのリンクを表示＆パスワード入力ダイアログ

		if (! $fileInfo = $this->getFileInfo($content, $colNo)) {
			return '';
		}

		$fileUrl = $this->fileDlUrl($content, $colNo);
		$result = '<span class="glyphicon glyphicon-file text-primary"></span>&nbsp;';
		$result .= '<a href="' . $fileUrl . '">';
		$result .= __d('multidatabases', 'Download');
		$result .= '</a>';

		if ((int)$showCounter === 1) {
			$result .= '&nbsp;<span class="badge">';
			$result .= $fileInfo['UploadFile']['download_count'];
			$result .= '</span>';
		}

		return $result;
	}

/**
 * 画像用のファイルアップロードの値を出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return string HTML
 */
	public function renderViewElementImage($content, $colNo) {
		// アップロードされた画像を表示

		if (! $this->getFileInfo($content, $colNo)) {
			return '';
		}

		$fileUrl = $this->fileDlUrl($content, $colNo);
		$result = '<img src="' . $fileUrl . '" alt="" style="width:100%">';
		return $result;
	}

/**
 * 隠し属性の値を出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return string HTML
 */
	public function renderViewElementHidden($content, $colNo) {
		return $this->renderViewElementGeneral($content, $colNo);
	}

/**
 * 作成日時の値を出力する
 *
 * @param array $content コンテンツ配列
 * @return string HTML
 */
	public function renderViewElementCreated($content) {
		return date("Y/m/d H:i:s", strtotime($content['MultidatabaseContent']['modified']));
	}

/**
 * 更新日時の値を出力する
 *
 * @param array $content コンテンツ配列
 * @return string HTML
 */
	public function renderViewElementUpdated($content) {
		return date("Y/m/d H:i:s", strtotime($content['MultidatabaseContent']['created']));
	}

/**
 *リンクの値を出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return string HTML
 */
	public function renderViewElementLink($content, $colNo) {
		$value = $this->renderViewElementGeneral($content, $colNo);
		$result = '<a href="' . $value . '">' . $value . '</a>';
		return $result;
	}

/**
 * メールアドレスの値を出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return string HTML
 */
	public function renderViewElementEmail($content, $colNo) {
		$value = $this->renderViewElementGeneral($content, $colNo);
		$result = '<a href="mailto:' . $value . '">' . $value . '</a>';
		return $result;
	}

/**
 * 自動採番の値を出力する
 *
 * @param array $content コンテンツ配列
 * @return string HTML
 */
	public function renderViewElementAutoNumber($content) {
		// 自動採番のフィールドを作成してここに表示させる
	}

/**
 * ファイルダウンロードURLを出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return string HTML
 */
	public function fileDlUrl($content, $colNo) {
		return $this->NetCommonsHtml->url(
			$this->fileDlArray($content, $colNo)
		);
	}

/**
 * ファイルダウンロードURL出力用の配列を返す
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return array
 */
	public function fileDlArray($content, $colNo) {
		return [
			'controller' => 'multidatabase_contents',
			'action' => 'download',
			$content['MultidatabaseContent']['multidatabase_key'],
			$content['MultidatabaseContent']['id'],
			'?' => ['col_no' => $colNo]
		];
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
		$url = $named;
		$result = '';

		foreach ($metadatas as $metadata) {
			$colNo = 0;
			$name = '';
			$selections = [];

			if (
				$metadata['type'] === 'select' ||
				$metadata['type'] === 'checkbox'
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

		$result = '';
		if ($viewType === 'search') {
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
		} else {
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
}
