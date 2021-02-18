<?php
/**
 * MultidatabaseContentsController Controller
 * 汎用データベース コンテンツ処理関連コントローラー
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @author Kazunori Sakamoto <exkazuu@willbooster.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MultidatabasesAppController', 'Multidatabases.Controller');
App::uses('ZipDownloader', 'Files.Utility');
App::uses('TemporaryFolder', 'Files.Utility');

/**
 * MultidatabaseContentsController Controller
 *
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @author Kazunori Sakamoto <exkazuu@willbooster.com>
 * @package NetCommons\Multidatabases\Controller
 */
class MultidatabaseContentsController extends MultidatabasesAppController {

/**
 * @var array use models
 */
	public $uses = [
		'Multidatabases.MultidatabaseContent',
		'Multidatabases.MultidatabaseContentSearch',
		'Multidatabases.MultidatabaseContentFile',
		'Workflow.WorkflowComment',
		'Categories.Category',
		'NetCommons.NetCommonsTime',
	];

/**
 * @var array helpers
 */
	public $helpers = [
		'NetCommons.BackTo',
		'Workflow.Workflow',
		'Likes.Like',
		'ContentComments.ContentComment' => [
			'viewVarsKey' => [
				'contentKey' => 'multidatabaseContent.MultidatabaseContent.key',
				'contentTitleForMail' => 'multidatabaseContent.MultidatabaseContent.value1',
				'useComment' => 'multidatabaseSetting.use_comment',
				'useCommentApproval' => 'multidatabaseSetting.use_comment_approval',
			],
		],
		'NetCommons.NetCommonsForm',
		'NetCommons.NetCommonsTime',
		'NetCommons.SnsButton',
		'NetCommons.TitleIcon',
		'NetCommons.DisplayNumber',
	];

/**
 * Components
 *
 * @var array
 */
	public $components = [
		'Paginator',
		'NetCommons.Permission' => [
			//アクセスの権限
			'allow' => [
				'add,edit,delete' => 'content_creatable',
				'approve' => 'content_comment_publishable',

			],
		],
		'ContentComments.ContentComments' => [
			'viewVarsKey' => [
				'contentKey' => 'multidatabaseContent.MultidatabaseContent.key',
				'useComment' => 'multidatabaseSetting.use_comment',
			],
			'allow' => ['detail'],
		],
		'Files.Download',
		'AuthorizationKeys.AuthorizationKey' => [
			'operationType' => 'popup',
			'targetAction' => 'download',
			'model' => 'MultidatabaseContent',
		],
	];

/**
 * @var array 絞り込みフィルタ保持値
 */
	protected $_filter = [
		'status' => 0,
	];

/**
 * Get Permission
 * 権限の取得
 *
 * @return array
 */
	protected function _getPermission() {
		$permissionNames = [
			'content_readable',
			'content_creatable',
			'content_editable',
			'content_publishable',
		];
		$permission = [];
		foreach ($permissionNames as $key) {
			$permission[$key] = Current::permission($key);
		}

		return $permission;
	}

/**
 * beforeFilter
 *
 * @return void
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Security->validatePost = false;
		$this->Security->csrfCheck = false;

		if (!Current::read('Block.id')) {
			$this->setAction('emptyRender');

			return false;
		}

		$frameSetting = $this->MultidatabaseFrameSetting->getMultidatabaseFrameSetting(true);
		$this->set('multidatabaseFrameSetting', $frameSetting['MultidatabaseFrameSetting']);

		// ゲストアクセスOKのアクションを設定
		$this->Auth->allow('index', 'detail', 'search', 'get_download_count');

		$this->_prepare();
	}

/**
 * Show Index
 * 汎用データベース コンテンツインデックス
 *
 * @return void
 */
	public function index() {
		$conditions = [];
		// 一覧を表示する
		$this->__list($conditions);
	}

/**
 * Show Content Detail
 * 汎用データベース コンテンツ詳細表示
 *
 * @return void
 */
	public function detail() {
		$key = $this->params['key'];

		$permission = $this->_getPermission();

		$conditions = $this->MultidatabaseContent->getConditions(
			Current::read('Block.id'),
			$permission
		);

		$conditions['MultidatabaseContent.key'] = $key;

		$options = [
			'conditions' => $conditions,
			'recursive' => 0,
		];

		$this->MultidatabaseContent->recursive = 0;
		$this->MultidatabaseContent->Behaviors->load('ContentComments.ContentComment');
		$multidatabaseContent = $this->MultidatabaseContent->find('first', $options);
		$this->MultidatabaseContent->Behaviors->unload('ContentComments.ContentComment');
		$this->MultidatabaseContent->recursive = -1;

		if ($multidatabaseContent) {
			$this->set('multidatabaseContent', $multidatabaseContent);
			$this->set('viewMode', 'detail');
			if ($this->_setting['MultidatabaseSetting']['use_comment']) {
				if ($this->request->is('post')) {
					$contentKey
						= $multidatabaseContent['MultidatabaseContent']['key'];
					$useCommentApproval
						= $this->_setting['MultidatabaseSetting']['use_comment_approval'];
					if (!$this->ContentComments->comment('multidatabases', $contentKey,
						$useCommentApproval)
					) {
						return $this->throwBadRequest();
					}
				}
			}
		} else {
			return $this->throwBadRequest();
		}
	}

/**
 * Add Content
 * 汎用データベース コンテンツ追加
 *
 * @return void
 */
	public function add() {
		$this->set('isEdit', false);

		if ($this->request->is('post')) {
			$url = $this->__save(false);
			if (!$url) {
				$this->NetCommons->handleValidationError($this->MultidatabaseContent->validationErrors);
			} else {
				return $this->redirect($url);
			}
			$multidatabaseContent = $this->request->data['MultidatabaseContent'];
		} else {
			$multidatabaseContent = [];
		}

		$this->set('multidatabaseContent', $multidatabaseContent);

		$this->render('form');
	}

/**
 * Edit Content
 * 汎用データベース コンテンツ編集
 *
 * @return void
 */
	public function edit() {
		$this->set('isEdit', true);
		$key = $this->params['key'];

		$permission = $this->_getPermission();

		$conditions = $this->MultidatabaseContent->getConditions(
			Current::read('Block.id'),
			$permission
		);

		$conditions['MultidatabaseContent.key'] = $key;

		$multidatabaseContent = $this->MultidatabaseContent->getEditData($conditions);

		if (
			! $multidatabaseContent ||
			$this->MultidatabaseContent->canEditWorkflowContent($multidatabaseContent) === false
		) {
			return $this->throwBadRequest();
		}

		if ($this->request->is(['post', 'put'])) {
			$url = $this->__save(true);
			if (!$url) {
				$this->NetCommons->handleValidationError($this->MultidatabaseContent->validationErrors);
			} else {
				return $this->redirect($url);
			}

		} else {
			$this->request->data = $multidatabaseContent;
		}

		$this->set('multidatabaseContent', $multidatabaseContent['MultidatabaseContent']);
		$this->set('isDeletable',
			$this->MultidatabaseContent->canDeleteWorkflowContent($multidatabaseContent)
		);
		$comments = $this->MultidatabaseContent->getCommentsByContentKey(
			$multidatabaseContent['MultidatabaseContent']['key']
		);
		$this->set('comments', $comments);

		$this->render('form');
	}

/**
 * Save Content
 * データを保存する
 *
 * @param bool $isUpdate 更新処理であるか(true:更新,false:新規)
 * @return bool|string
 */
	private function __save($isUpdate) {
		$this->request->data['MultidatabaseContent']['multidatabase_key'] =
			$this->_setting['MultidatabaseSetting']['multidatabase_key'];

		$status = $this->Workflow->parseStatus();
		$this->request->data['MultidatabaseContent']['block_id'] = Current::read('Block.id');
		$this->request->data['MultidatabaseContent']['language_id'] = Current::read('Language.id');
		$this->request->data['MultidatabaseContent']['status'] = $status;

		$data = $this->request->data;

		unset($data['MultidatabaseContent']['id']);

		if ($result = $this->MultidatabaseContent->saveContent($data, $isUpdate)) {
			$url = NetCommonsUrl::actionUrl(
				[
					'controller' => 'multidatabase_contents',
					'action' => 'detail',
					'block_id' => Current::read('Block.id'),
					'frame_id' => Current::read('Frame.id'),
					'key' => $result['MultidatabaseContent']['key'],

				]
			);
			return $url;
		}
		return false;
	}

/**
 * Delete Content
 * 汎用データベース コンテンツ削除
 *
 * @return string
 * @throws InternalErrorException
 */
	public function delete() {
		$this->request->allowMethod('post', 'delete');

		$key = $this->request->data['MultidatabaseContent']['key'];
		$multidatabaseContent = $this->MultidatabaseContent->getWorkflowContents('first', [
			'recursive' => 0,
			'conditions' => [
				'MultidatabaseContent.key' => $key,
			],
		]);

		// 権限チェック
		if ($this->MultidatabaseContent->canDeleteWorkflowContent($multidatabaseContent) === false) {
			return $this->throwBadRequest();
		}

		if ($this->MultidatabaseContent->deleteContentByKey($key) === false) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		return $this->redirect(
			NetCommonsUrl::actionUrl(
				[
					'controller' => 'multidatabase_contents',
					'action' => 'index',
					'frame_id' => Current::read('Frame.id'),
					'block_id' => Current::read('Block.id'),
				]
			)
		);
	}

/**
 * File Download
 * ファイルダウンロード
 *
 * @throws InternalErrorException
 * @return void
 */
	public function download() {
		$key = $this->params['key'];

		$permission = $this->_getPermission();

		$conditions = $this->MultidatabaseContent->getConditions(
			Current::read('Block.id'),
			$permission
		);

		$conditions['MultidatabaseContent.key'] = $key;

		$options = [
			'conditions' => $conditions,
			'recursive' => 0,
		];

		$content = $this->MultidatabaseContent->find('first', $options);

		if (! $content) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$contentId = $content['MultidatabaseContent']['id'];
		$colNo = (int)($this->request->query['col_no'] ?? '0');
		$field = 'value' . $colNo;
		$fieldAttach = $field . '_attach';

		// パスワード認証確認
		$content['AuthorizationKey'] = $this->MultidatabaseContentFile->getAuthKey($contentId, $field);
		if ($content['AuthorizationKey']) {
			$this->AuthorizationKey->guard('popup', 'MultidatabaseContent', $content, $field);
		}

		$options = [
			'field' => $fieldAttach,
			'download' => true,
			//'name' => '',
		];

		return $this->Download->doDownload($contentId, $options);
	}

/**
 * File Download Count
 * ファイルダウンロード数
 *
 * @return void
 */
	public function get_download_count() {
		$query = [
			'fields' => [
				'UploadFile.total_download_count',
			],
			'conditions' => [
				'UploadFile.id' => $this->request->query('upload_file_id'),
			],
			'recursive' => -1
		];
		$UploadFile = ClassRegistry::init('Files.UploadFile');
		$file = $UploadFile->find('first', $query);

		$this->set('_serialize', ['count']);
		$this->set('count', $file);
	}

/**
 * Search
 * 検索
 *
 * @return void
 */
	public function search() {
		// クエリを取得する
		$query = [];
		foreach ([
			'keywords',
			'type',
			'start_dt',
			'end_dt',
			'status',
			'sort'
		] as $key) {
			if (!is_null($this->request->query($key))) {
				$query[$key]['type'] = 'search';
				$query[$key]['field'] = null;
				$query[$key]['value'] = $this->request->query($key);
			}
		}

		foreach ($this->_metadata as $metadata) {
			switch($metadata['type']) {
				case 'checkbox':
				case 'radio':
				case 'select':
					if (!is_null($this->request->query('value' . $metadata['col_no']))) {
						$field = 'value' . $metadata['col_no'];
						$query[$field]['type'] = $metadata['type'];
						$query[$field]['field'] = $field;
						$query[$field]['value'] = $this->request->query($field);
					}
					break;
			}
		}

		$searchConds = $this->MultidatabaseContentSearch->getSearchConds($query);
		$conditions = $this->__listBase($searchConds['conditions']);

		// paginatorへ渡すための条件を取得する
		$this->Paginator->settings = array_merge(
			$this->Paginator->settings,
			[
				'conditions' => $conditions,
				'limit' => $this->_frameSetting['MultidatabaseFrameSetting']['content_per_page'],
				'order' => $searchConds['order'],
			]
		);

		$this->set('cancelUrl', NetCommonsUrl::backToIndexUrl());
		$this->set('multidatabaseContents', $this->Paginator->paginate('MultidatabaseContent'));
		$this->set('viewMode', 'list');
		if (!empty($query)) {
			$this->render('search_results');
		} else {
			$this->render('search');
		}
	}

/**
 * Make sort condition
 * 汎用データベース コンテンツ一覧ソート処理（条件設定）
 *
 * @param string $sortCol ソート対象の
 * @return string Order句の内容
 */
	private function __condSortOrder($sortCol = '') {
		if (empty($sortCol)) {
			$pagerNamed = $this->Paginator->Controller->params->named;
			if (empty($pagerNamed['sort_col'])) {
				$sortCol = $this->_frameSetting['MultidatabaseFrameSetting']['default_sort_type'];
			} else {
				// ソートする項目はDBにあるか
				// ソート順は created_desc, modified_desc等、DB項目名＋_descがありえるため、strstrで比較する
				//if ($this->MultidatabaseContent->hasField($pagerNamed['sort_col'])) {
				if (strstr($pagerNamed['sort_col'], 'value') <> false ||
						strstr($pagerNamed['sort_col'], 'created') <> false ||
						strstr($pagerNamed['sort_col'], 'modified') <> false) {
					$sortCol = $pagerNamed['sort_col'];
				} else {
					// 項目ないと内部エラーになるため空にする
					$sortCol = '';
				}
			}
		}

		return $this->MultidatabaseContentSearch->getCondSortOrder($sortCol);
	}

/**
 * 一覧表示における複数選択、単一選択の絞込条件取得
 *
 * @return mixed
 */
	private function __condSelect() {
		$pagerNamed = $this->Paginator->Controller->params->named;
		return $this->MultidatabaseContentSearch->getCondSelect($pagerNamed);
	}

/**
 * Show Contents List
 * 汎用データべース コンテンツ一覧表示（ベース）
 *
 * @param array $extraConditions 追加条件
 * @return void
 */
	private function __listBase($extraConditions = []) {
		$permission = $this->_getPermission();

		$conditions = $this->MultidatabaseContent->getConditions(
			Current::read('Block.id'),
			$permission
		);

		if ($extraConditions) {
			$conditions = Hash::merge($conditions, $extraConditions);
		}

		return $conditions;
	}

/**
 * Show Contents List
 * 汎用データべース コンテンツ一覧表示（インデックス用）
 *
 * @param array $extraConditions 追加条件
 * @return void
 */
	private function __list($extraConditions = []) {
		$conditions = $this->__listBase($extraConditions);

		$limitSelect = $this->__condSelect();

		if (! empty($limitSelect)) {
			$conditions = Hash::merge($conditions,
				['and' => $limitSelect]
			);
		}

		$this->Paginator->settings = array_merge(
			$this->Paginator->settings,
			[
				'conditions' => $conditions,
				'limit' => $this->_frameSetting['MultidatabaseFrameSetting']['content_per_page'],
				'order' => $this->__condSortOrder()
			]
		);

		$this->MultidatabaseContent->recursive = 0;
		$this->MultidatabaseContent->Behaviors->load('ContentComments.ContentComment');
		$this->set('multidatabaseContents', $this->Paginator->paginate('MultidatabaseContent'));
		$this->MultidatabaseContent->Behaviors->unload('ContentComments.ContentComment');
		$this->MultidatabaseContent->recursive = -1;

		$this->set('viewMode', 'list');
	}
}
