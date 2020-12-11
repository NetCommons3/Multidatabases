<?php
/**
 * MultidatabaseBlocks Controller
 * 汎用データベース ブロック設定コントローラー
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MultidatabasesAppController', 'Multidatabases.Controller');

/**
 * MultidatabaseBlocks Controller
 *
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @package NetCommons\Multidatabases\Controller
 */
class MultidatabaseBlocksController extends MultidatabasesAppController {

/**
 * layout
 *
 * @var array
 */
	public $layout = "NetCommons.setting";

/**
 * use models
 *
 * @var array
 */
	public $uses = [
		'Multidatabases.MultidatabaseFrameSetting',
		'Multidatabases.MultidatabaseMetadata',
		'Multidatabases.MultidatabaseMetadataEdit',
		'Multidatabases.MultidatabaseMetadataInit',
		'DataTypes.DataTypeChoice',
		'Blocks.Block',
	];

/**
 * use components
 *
 * @var array
 */
	public $components = [
		'NetCommons.Permission' => [
			'allow' => [
				'index,add,edit,delete' => 'block_editable',
			],
		],
		'Paginator',
	];

/**
 * use helpers
 *
 * @var array
 */
	public $helpers = [
		'Blocks.BlockForm',
		'Blocks.BlockIndex',
		'Blocks.BlockTabs' => [
			'mainTabs' => [
				'block_index' => ['url' => ['controller' => 'multidatabase_blocks']],
				'frame_settings' => ['url' => ['controller' => 'multidatabase_frame_settings']],
			],
			'blockTabs' => [
				'block_settings' => ['url' => ['controller' => 'multidatabase_blocks']],
				'mail_settings' => ['url' => ['controller' => 'multidatabase_mail_settings']],
				'role_permissions' => ['url' => ['controller' => 'multidatabase_block_role_permissions']],
				'content_imports' => [
					'url' => ['controller' => 'multidatabase_content_imports'],
					'label' => ['multidatabases', 'Import contents']
				],
				'content_exports' => [
					'url' => ['controller' => 'multidatabase_content_exports'],
					'label' => ['multidatabases', 'Export contents']
				],
			],
		],
		'Likes.Like',
	];

/**
 * beforeFilter
 *
 * @return void
 */
	public function beforeFilter() {
		parent::beforeFilter();
	}

/**
 * Block index
 * ブロック一覧
 *
 * @return void
 */
	public function index() {
		$this->Paginator->settings = [
			'Multidatabase' => $this->Multidatabase->getBlockIndexSettings(),
		];

		$multidatabases = $this->Paginator->paginate('Multidatabase');

		if (!$multidatabases) {
			$this->view = 'Blocks.Blocks/not_found';

			return;
		}

		$this->set('multidatabases', $multidatabases);
		$this->request->data['Frame'] = Current::read('Frame');
	}

/**
 * Add Block
 * ブロックの追加
 *
 * @return void
 */
	public function add() {
		$this->view = 'edit';

		if ($this->request->is('put') || $this->request->is('post')) {

			// バリデーション
			$result = $this->Multidatabase->doValidate($this->data);

			if (! $result['has_err']) {
				$this->Multidatabase->saveMultidatabase($this->data);
				return $this->redirect(NetCommonsUrl::backToIndexUrl('default_setting_action'));
			}

			$this->NetCommons->handleValidationError($result['errors']);
			$multidatabases = $result['data'];
		} else {
			$multidatabases = $this->Multidatabase->createMultidatabase();
			$multidatabases['MultidatabaseMetadata'] = $this->MultidatabaseMetadataInit->getInitMetadatas();
		}

		$this->request->data = $multidatabases;
		$this->request->data += $this->MultidatabaseFrameSetting->getMultidatabaseFrameSetting(true);

		$this->set('metadatas', $multidatabases['MultidatabaseMetadata']);
		$this->set('metadataDefault', $this->MultidatabaseMetadataEdit->getEmptyMetadata());
		$this->request->data['Frame'] = Current::read('Frame');
	}

/**
 * Edit Block
 * ブロックの編集
 *
 * @return void
 */
	public function edit() {
		//画面表示とPOSTされた時のバリデーション用にDBの値を利用したいので、
		//両方で値を取得する
		$multidatabases = $this->Multidatabase->getMultidatabase();
		$multidatabases['MultidatabaseMetadata'] = $this->MultidatabaseMetadata->getEditMetadatas(
			$multidatabases['Multidatabase']['id']
		);
		if ($this->request->is('put') || $this->request->is('post')) {
			// バリデーション
			$result = $this->Multidatabase->doValidate($this->data, $multidatabases);

			if (! $result['has_err']) {
				$this->Multidatabase->saveMultidatabase($this->data);
				return $this->redirect(NetCommonsUrl::backToIndexUrl('default_setting_action'));
			}

			$this->NetCommons->handleValidationError($result['errors']);
			$multidatabases = $result['data'];
		} else {
			//$multidatabases = $this->Multidatabase->getMultidatabase();

			if (!$multidatabases) {
				return $this->throwBadRequest();
			}

			// $multidatabases['MultidatabaseMetadata'] = $this->MultidatabaseMetadata->getEditMetadatas(
			// 	$multidatabases['Multidatabase']['id']
			// );

			if (!$multidatabases['MultidatabaseMetadata']) {
				return $this->throwBadRequest($multidatabases['MultidatabaseMetadata']);
			}

		}

		$this->request->data = $multidatabases;
		$this->request->data = Hash::merge(
			$this->request->data,
			$this->MultidatabaseFrameSetting->getMultidatabaseFrameSetting(true)
		);

		$this->set('metadataDefault', $this->MultidatabaseMetadataEdit->getEmptyMetadata());
		$this->set('metadatas', $multidatabases['MultidatabaseMetadata']);
		$this->request->data['Frame'] = Current::read('Frame');
	}

/**
 * Delete Block
 * ブロックの削除
 *
 * @return void
 */
	public function delete() {
		if ($this->request->is('delete')) {
			if ($this->Multidatabase->deleteMultidatabase($this->data)) {
				return $this->redirect(NetCommonsUrl::backToIndexUrl('default_setting_action'));
			}
		}

		return $this->throwBadRequest();
	}
}
