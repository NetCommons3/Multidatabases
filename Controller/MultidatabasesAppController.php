<?php
/**
 * MultidatabasesAppController Controller
 * 汎用データベースAppコントローラー
 * （汎用データべース関連コントローラーの共通処理を定義する）
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppController', 'Controller');

/**
 * MultidatabasesAppController Controller
 *
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @package NetCommons\Multidatabases\Controller
 */
class MultidatabasesAppController extends AppController {

/**
 * 使用するComponent
 *
 * @var array
 */
	public $components = [
		'Pages.PageLayout',
		'Security',
	];

/**
 * @var array use model
 */
	public $uses = [
		'Multidatabases.Multidatabase',
		'Multidatabases.MultidatabaseMetadata',
		'Multidatabases.MultidatabaseSetting',
		'Multidatabases.MultidatabaseFrameSetting',
	];

/**
 * @var array 汎用DBタイトル
 */
	protected $_multidatabaseTitle;

/**
 * @var array フレーム設定
 */
	protected $_frameSetting;

/**
 * @var array 汎用DBブロック設定
 */
	protected $_setting;

/**
 * @var array 汎用DB設定
 */
	protected $_multidatabase;

/**
 * @var array 汎用DB メタデータ設定
 */
	protected $_metadata;

/**
 * prepare
 *
 * @return void
 */
	protected function _prepare() {
		$this->_initMultidatabase(['multidatabaseSetting']);
		$this->_loadFrameSetting();
	}

/**
 * 初期設定
 *
 * @return bool|void
 */
	protected function _initMultidatabase() {
		// 汎用DBを取得
		if (!$multidatabase = $this->Multidatabase->getMultidatabase()) {
			return $this->throwBadRequest();
		}

		// メタデータを取得
		if (!$metadatas = $this->MultidatabaseMetadata->getEditMetadatas(
			$multidatabase['Multidatabase']['id'])
		) {
			return $this->throwBadRequest();
		}

		foreach ($metadatas as $metadata) {
			$metadataGroups[$metadata['position']][$metadata['rank']] = $metadata;
		}

		$this->_multidatabaseTitle = $multidatabase['Multidatabase']['name'];
		$this->set('multidatabase', $multidatabase);
		$this->set('multidatabaseMetadata', $metadatas);
		$this->set('multidatabaseMetadataGroups', $metadataGroups);

		$multidatabaseSetting['MultidatabaseSetting'] = $multidatabase['MultidatabaseSetting'];
		if (!$multidatabaseSetting) {
			$multidatabaseSetting = $this->MultidatabaseSetting->createBlockSetting();
			$multidatabaseSetting['MultidatabaseSetting']['multidatabase_key'] = null;
		} else {
			$multidatabaseSetting['MultidatabaseSetting']['multidatabase_key']
				= $multidatabase['Multidatabase']['key'];
		}

		$this->_multidatabase = $multidatabase;
		$this->_metadata = $metadatas;
		$this->_setting = $multidatabaseSetting;
		$this->set('multidatabaseSetting', $multidatabaseSetting['MultidatabaseSetting']);
		$this->set('userId', (int)$this->Auth->user('id'));
		return true;
	}

/**
 * フレーム設定を取得する
 *
 * @return void
 */
	protected function _loadFrameSetting() {
		$this->_frameSetting = $this->MultidatabaseFrameSetting->getMultidatabaseFrameSetting();
	}
}
