<?php
/**
 * MultidatabaseContentEditHelper Helper
 * 汎用データベースコンテンツ編集ヘルパー
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppHelper', 'View/Helper');

/**
 * MultidatabaseContentEditHelper Helper
 *
 * @author Tomoyuki OHNO (Ricksoft, Co., LTD.) <ohno.tomoyuki@ricksoft.jp>
 * @package NetCommons\Multidatabase\View\Helper
 *
 */
class MultidatabaseContentEditHelper extends AppHelper {

/**
 * 使用するHelpers
 *
 * @var array
 */
	public $helpers = [
		'NetCommons.Button',
		'NetCommons.NetCommonsHtml',
		'NetCommons.NetCommonsForm',
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
 * 汎用データベースメタデータレイアウト グループのHTMLを出力する(列)
 *
 * @param array $metadataGroups メタデータグループ
 * @param int $position グループNo
 * @return string HTML
 */
	public function renderGroup($metadataGroups, $position) {
		$element = 'MultidatabaseContents/edit/edit_content_group';

		switch ($position) {
			case 0:
			case 1:
			case 2:
			case 3:
				return $this->_View->Element(
					$element,
					[
						'gMetadatas' => $metadataGroups[$position],
						'gPos' => $position,
					]
				);
			default:
				return '';
		}
	}

/**
 * 汎用データベースメタデータレイアウト アイテムのHTMLを出力する
 *
 * @param array $metadatas 特定グループのメタデータ
 * @return string HTML
 */
	public function renderGroupItems($metadatas) {
		return $this->_View->Element(
			'MultidatabaseContents/edit/edit_content_group_items',
			[
				'metadatas' => $metadatas,
			]
		);
	}

/**
 * メタデータ選択肢の値を配列に変換する
 *
 * @param array $metaSelections メタデータの選択肢の値
 * @return array
 */
	public function cnvMetaSelToArr($metaSelections) {
		$result = [];
		foreach ($metaSelections as $metaSelection) {
			$result[md5($metaSelection)] = $metaSelection;
		}
		return $result;
	}

/**
 * フォーム部品を出力する
 *
 * @param array $metadata メタデータ
 * @param array $options オプション
 * @return string HTML
 */
	public function renderFormElement($metadata, $options = []) {
		if (!empty($label)) {
			$options['label'] = $label;
		}

		$name = 'MultidatabaseContent.value' . $metadata['col_no'];
		$options['id'] = $name;
		$options['label'] = $metadata['name'];
		if ((int)$metadata['is_require'] === 1) {
			$options['required'] = true;
		}
		$elementType = $metadata['type'];

		$result = '';
		switch ($elementType) {
			case 'text':
				$result .= $this->renderFormElementText($name, $options);
				break;
			case 'textarea':
				$result .= $this->renderFormElementTextArea($name, $options);
				break;
			case 'link':
				$result .= $this->renderFormElementText($name, $options);
				break;
			case 'radio':
				$options['options'] = $this->cnvSelToArr($metadata['selections']);
				$result .= $this->renderFormElementRadio($name, $options);
				break;
			case 'select':
				$options['options'] = $this->cnvMetaSelToArr($metadata['selections']);
				$result .= $this->renderFormElementSelect($name, $options);
				break;
			case 'checkbox':
				$options['options'] = $this->cnvMetaSelToArr($metadata['selections']);
				$result .= $this->renderFormElementCheckBox($name, $options);
				break;
			case 'wysiwyg':
				$options['rows'] = 12;
				$result .= $this->renderFormElementWysiwyg($name, $options);
				break;
			case 'file':
				$result .= $this->renderFormElementFile($name, $options);
				break;
			case 'image':
				$result .= $this->renderFormElementImage($name, $options);
				break;
			case 'autonumber':
				$result .= $this->renderFormElementReadOnly($name, $options);
				break;
			case 'mail':
				$result .= $this->renderFormElementText($name, $options);
				break;
			case 'date':
				$result .= $this->renderFormElementDate($name, $options);
				break;
			case 'created':
				$result .= $this->renderFormElementReadOnly($name, $options);
				break;
			case 'updated':
				$result .= $this->renderFormElementReadOnly($name, $options);
				break;
			case 'hidden':
				$result .= $this->renderFormElementHidden($name, $options);
				break;
			default:
				$result .= $this->renderFormElementReadOnly($name, $options);
				break;
		}

		return $result;
	}

/**
 * 値を出力する
 *
 * @param string $name フィールド名(key)
 * @param array $options オプション
 * @return string HTML
 */
	public function renderFormElementReadOnly($name, $options = []) {
		if (isset($options['value'])) {
			return $options['value'];
		}

		return '';
	}

/**
 * テキストボックスを出力する
 *
 * @param string $name フィールド名(key)
 * @param array $options オプション
 * @return string HTML
 */
	public function renderFormElementText($name, $options = []) {
		$options['type'] = 'text';
		return $this->NetCommonsForm->input($name, $options);
	}

/**
 * WYSIWYGを出力する
 *
 * @param string $name フィールド名(key)
 * @param array $options オプション
 * @return string HTML
 */
	public function renderFormElementWysiwyg($name, $options = []) {
		return $this->NetCommonsForm->wysiwyg($name, $options);
	}

/**
 * テキストエリアを出力する
 *
 * @param string $name フィールド名(key)
 * @param array $options オプション
 * @return string HTML
 */
	public function renderFormElementTextArea($name, $options = []) {
		$options['type'] = 'textarea';
		return $this->NetCommonsForm->input($name, $options);
	}

/**
 * チェックボックスを出力する
 *
 * @param string $name フィールド名(key)
 * @param array $options オプション
 * @return string HTML
 */
	public function renderFormElementCheckBox($name, $options = []) {
		$options += [
			'type' => 'select',
			'multiple' => 'checkbox',
			'options' => $options,
			'class' => 'checkbox-inline nc-checkbox',
		];

		return $this->NetCommonsForm->input($name, $options);
	}

/**
 * ラジオボタンを出力する
 *
 * @param string $name フィールド名(key)
 * @param array $options オプション
 * @return string HTML
 */
	public function renderFormElementRadio($name, $options = []) {
		$options['type'] = 'radio';
		$options['inline'] = true;
		return $this->NetCommonsForm->input($name, $options);
	}

/**
 * セレクトボックスを出力する
 *
 * @param string $name フィールド名(key)
 * @param array $options オプション
 * @return string HTML
 */
	public function renderFormElementSelect($name, $options = []) {
		$options['type'] = 'select';
		return $this->NetCommonsForm->input($name, $options);
	}

/**
 * デートピッカー対応のテキストボックスを出力する
 *
 * @param string $name フィールド名(key)
 * @param array $options オプション
 * @return string HTML
 */
	public function renderFormElementDate($name, $options = []) {
		$options['type'] = 'datetime';
		return $this->NetCommonsForm->input($name, $options);
	}

/**
 * ファイルアップロードエレメントを出力する
 *
 * @param string $name フィールド名(key)
 * @param array $options オプション
 * @return string HTML
 */
	public function renderFormElementFile($name, $options = []) {
		$options['remove'] = false;
		$options['filename'] = false;
		return $this->NetCommonsForm->uploadFile($name, $options);
	}

/**
 * 画像用のファイルアップロードエレメントを出力する
 *
 * @param string $name フィールド名(key)
 * @param array $options オプション
 * @return string HTML
 */
	public function renderFormElementImage($name, $options = []) {
		$options['remove'] = false;
		$options['filename'] = false;
		return $this->NetCommonsForm->uploadFile($name, $options);
	}

/**
 * 隠し属性のエレメントを出力する
 *
 * @param string $name フィールド名(key)
 * @param array $options オプション
 * @return string HTML
 */
	public function renderFormElementHidden($name, $options = []) {
		$options['type'] = 'hidden';
		return $this->NetCommonsForm->input($name, $options);
	}
}
