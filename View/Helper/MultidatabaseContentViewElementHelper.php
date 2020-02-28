<?php
/**
 * MultidatabaseContentViewElementHelper Helper
 * 汎用データベースコンテンツ表示エレメントヘルパー
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppHelper', 'View/Helper');
App::uses('NetCommonsTime', 'NetCommons.Utility');

/**
 * MultidatabaseContentViewElementHelper Helper
 *
 * @author Tomoyuki OHNO (Ricksoft, Co., LTD.) <ohno.tomoyuki@ricksoft.jp>
 * @package NetCommons\Multidatabase\View\Helper
 *
 */
class MultidatabaseContentViewElementHelper extends AppHelper {

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
 * フォーム部品に合った表示出力する
 *
 * @param array $content コンテンツ配列
 * @param array $metadata メタデータ配列
 * @param string $elementType 部品タイプ
 * @return string HTML
 */
	public function renderViewElement($content, $metadata, $elementType = null) {
		if (is_null($elementType)) {
			$elementType = $metadata['type'];
		}
		$colNo = $metadata['col_no'];

		if (
			in_array($elementType, ['date', 'created', 'updated'])
		) {
			return $this->__renderViewElementDate($content, $colNo, $elementType);
		}

		switch ($elementType) {
			case 'link':
				return $this->__renderViewElementLink($content, $colNo);
			case 'wysiwyg':
				return $this->__renderViewElementWysiwyg($content, $colNo);
			case 'file':
				return $this->__renderViewElementFile($content, $colNo,
					$metadata['is_visible_file_dl_counter']);
			case 'image':
				return $this->__renderViewElementImage($content, $colNo);
			case 'autonumber':
				return $this->__renderViewElementAutoNumber($content, $colNo);
			case 'mail':
				return $this->__renderViewElementEmail($content, $colNo);
			default:
				// text, textarea, checkbox, select, hiddenはこちらの処理
				return $this->__renderViewElementGeneral($content, $colNo);
		}
	}

/**
 * 汎用的な値出力方法
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return string HTML
 */
	private function __renderViewElementGeneral($content, $colNo) {
		$value = (string)trim(h($content['MultidatabaseContent']['value' . $colNo]));

		if ($value === '') {
			return '';
		}

		if (strstr($value, '||') <> false) {
			return str_replace('||', '<br>', $value);
		}

		$value = nl2br($value);
		return $value;
	}

/**
 * WYSIWYGの値を出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return string HTML
 */
	private function __renderViewElementWysiwyg($content, $colNo) {
		return $content['MultidatabaseContent']['value' . $colNo];
	}

/**
 * 日付の値を出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @param string $type 種別(created, updated, その他)
 * @return string HTML
 */
	private function __renderViewElementDate($content, $colNo, $type = null) {
		$netCommonsTime = new NetCommonsTime();
		switch ($type) {
			case 'created':
				// 作成日時を出力
				return $netCommonsTime->toUserDatetime($content['MultidatabaseContent']['created']);
			case 'updated':
				// 更新日時を出力
				return $netCommonsTime->toUserDatetime($content['MultidatabaseContent']['modified']);
			default:
				$value = $this->__renderViewElementGeneral($content, $colNo);
				//  空 or DateTime型以外は、空セット
				if (empty($value)) {
					return '';
				} elseif (! $netCommonsTime->isDatetime($value)) {
					return '';
				}
				return $netCommonsTime->toUserDatetime($value);
		}
	}

/**
 * ファイルアップロードの値を出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @param int $showCounter カウンターを表示するか 1:表示する
 * @return string HTML
 */
	private function __renderViewElementFile($content, $colNo, $showCounter = 0) {
		// アップロードされたファイルのリンクを表示＆パスワード入力ダイアログ

		if (! $fileInfo = $this->__getFileInfo($content, $colNo)) {
			return '';
		}

		$ContentFile = ClassRegistry::init('Multidatabases.MultidatabaseContentFile');

		if (! $ContentFile->getAuthKey(
			$content['MultidatabaseContent']['id'], 'value' . $colNo)
		) {
			$fileUrl = $this->__fileDlUrl($content, $colNo);
			$result = '<span class="glyphicon glyphicon-file text-primary"></span>&nbsp;';
			$result .= '<a href="' . $fileUrl . '" target="_blank">';
			// 表示が全てダウンロードとなる。ファイル名を表示する
			//$result .= __d('multidatabases', 'Download');
			$result .= $fileInfo['UploadFile']['original_name'];
			$result .= '</a>';
		} else {
			$result = $this->__renderViewElementFileReqAuth($content, $colNo, $fileInfo);
		}
		if ((int)$showCounter === 1) {
			$result .= '&nbsp;<span class="badge">';
			$result .= $fileInfo['UploadFile']['total_download_count'];
			$result .= '</span>';
		}

		return $result;
	}

/**
 * ファイルアップロードの値を出力する（認証が必要な場合）
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @param array $fileInfo ダウンロードファイル
 * @return string HTML
 */
	private function __renderViewElementFileReqAuth($content, $colNo, $fileInfo) {
		// 認証キー必要
		$result = '<span class="glyphicon glyphicon-file text-primary"></span>&nbsp;';
		$result .= $this->NetCommonsHtml->link(
			// 表示が全てダウンロードとなる。ファイル名を表示する
			//__d('multidatabases', 'Download'),
			$fileInfo['UploadFile']['original_name'],
			'#',
			[
				'authorization-keys-popup-link',
				'url' => NetCommonsUrl::actionUrl($this->__fileDlArrayReqAuth($content, $colNo)),
				'frame-id' => Current::read('Frame.id'),
				'popup-title' => __d('authorization_keys', 'Authorization key confirm dialog'),
				'popup-label' => __d('authorization_keys', 'Authorization key'),
				'popup-placeholder' =>
					__d('authorization_keys', 'Please input authorization key'),
			]
		);
		return $result;
	}

/**
 * 画像用のファイルアップロードの値を出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return string HTML
 */
	private function __renderViewElementImage($content, $colNo) {
		// アップロードされた画像を表示

		if (! $this->__getFileInfo($content, $colNo)) {
			return '';
		}

		$fileUrl = $this->__fileDlUrl($content, $colNo);
		$result = '<img src="' . $fileUrl . '" alt="" style="max-width:100%">';
		return $result;
	}

/**
 * リンクの値を出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return string HTML
 */
	private function __renderViewElementLink($content, $colNo) {
		$value = $this->__renderViewElementGeneral($content, $colNo);
		$result = '<a href="' . $value . '" target="_blank">' . $value . '</a>';
		return $result;
	}

/**
 * メールアドレスの値を出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return string HTML
 */
	private function __renderViewElementEmail($content, $colNo) {
		$value = $this->__renderViewElementGeneral($content, $colNo);
		$result = '<a href="mailto:' . $value . '">' . $value . '</a>';
		return $result;
	}

/**
 * 自動採番の値を出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return string HTML
 */
	private function __renderViewElementAutoNumber($content, $colNo) {
		// 自動採番のフィールドを作成してここに表示させる
		return $this->__renderViewElementGeneral($content, $colNo);
	}

/**
 * ファイルダウンロードURL出力用の配列を返す
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return array
 */
	private function __fileDlArray($content, $colNo) {
		return [
			'controller' => 'multidatabase_contents',
			'action' => 'download',
			$content['MultidatabaseContent']['key'],
			$content['MultidatabaseContent']['id'],
			'?' => ['col_no' => $colNo]
		];
	}

/**
 * ファイルダウンロードURL出力用の配列を返す（認証あり）
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return array
 */
	private function __fileDlArrayReqAuth($content, $colNo) {
		return [
			'controller' => 'multidatabase_contents',
			'action' => 'download',
			Current::read('Block.id'),
			$content['MultidatabaseContent']['key'],
			$content['MultidatabaseContent']['id'],
			'?' => [
				'col_no' => $colNo,
				'frame_id' => Current::read('Frame.id')
			]
		];
	}

/**
 * ファイルダウンロードURLを出力する
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return string HTML
 */
	private function __fileDlUrl($content, $colNo) {
		return $this->NetCommonsHtml->url(
			$this->__fileDlArray($content, $colNo)
		);
	}

/**
 * ダウンロードファイルが存在するかチェックする
 *
 * @param array $content コンテンツ配列
 * @param int $colNo カラムNo
 * @return bool|array
 */
	private function __getFileInfo($content, $colNo) {
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
}
