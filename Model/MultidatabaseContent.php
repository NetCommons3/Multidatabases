<?php
/**
 * MultidatabaseContent Model
 * 汎用データベースコンテンツデータに関するモデル処理
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MultidatabasesAppModel', 'Multidatabases.Model');
App::uses('MultidatabaseModel', 'Multidatabase.Model');
App::uses('MultidatabaseMetadataModel', 'MultidatabaseMetadata.Model');
App::uses('MultidatabaseContentEditModel', 'MultidatabaseContentEdit.Model');
App::uses('MultidatabaseContentFileModel', 'MultidatabaseContentFile.Model');
App::uses('MultidatabaseContentSearchModel', 'MultidatabaseContentSearch.Model');
App::uses('TemporaryFolder', 'Files.Utility');

/**
 * MultidatabaseContent Model
 *
 * @author Tomoyuki OHNO (Ricksoft, Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @package NetCommons\Multidatabases\Model
 *
 */
class MultidatabaseContent extends MultidatabasesAppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = [];

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = [
		/*
		'Multidatabase' => array(
			'className' => 'Multidatabases.Multidatabase',
			'foreignKey' => 'multidatabase_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Language' => array(
			'className' => 'M17.Language',
			'foreignKey' => 'language_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		*/
		'Block' => [
			'className' => 'Blocks.Block',
			'foreignKey' => 'block_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'counterCache' => [
				'content_count' => [
					//'MultidatabaseContent.is_origin' => true,
					'MultidatabaseContent.is_latest' => true,
				],
			],
		],
	];

/**
 * Behavior
 *
 * @var array
 */
	public $actsAs = [
		'NetCommons.Trackable',
		'NetCommons.OriginalKey',
		'Workflow.Workflow',
		'Likes.Like',
		'Workflow.WorkflowComment',
		'ContentComments.ContentComment',
		'Files.Attachment'
	];

/**
 * @var array 絞り込みフィルタ保持値
 */
	protected $_filter = [
		'status' => 0,
	];

/**
 * Constructor. Binds the model's database table to the object.
 *
 * @param bool|int|string|array $id Set this ID for this model on startup,
 * can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 * @see Model::__construct()
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);

		$this->loadModels([
			'Multidatabase' => 'Multidatabases.Multidatabase',
			'MultidatabaseMetadata' => 'Multidatabases.MultidatabaseMetadata',
			'MultidatabaseContentEdit' => 'Multidatabases.MultidatabaseContentEdit',
			'MultidatabaseContentEditPr' => 'Multidatabases.MultidatabaseContentEditPr',
			'MultidatabaseContentEditAt' => 'Multidatabases.MultidatabaseContentEditAt',
			'MultidatabaseContentSearch' => 'Multidatabases.MultidatabaseContentSearch',
			'MultidatabaseContentFile' => 'Multidatabases.MultidatabaseContentFile',
		]);
	}

/**
 * Before validate
 *
 * @param array $options オプション
 * @return bool
 */
	public function beforeValidate($options = []) {
		$this->validate = $this->__makeValidation();

		return parent::beforeValidate($options);
	}

/**
 * 編集用のデータを取得する
 *
 * @param array $conditions データ取得条件
 * @return array|bool
 */
	public function getEditData($conditions = []) {
		if (empty($conditions)) {
			return false;
		}

		$options = [
			'conditions' => $conditions,
			'recursive' => 0,
		];

		$content = $this->find('first', $options);
		$metadatas = $this->MultidatabaseMetadata->getEditMetadatas();

		if (!$content || !$metadatas) {
			return false;
		}

		return $this->MultidatabaseContentEdit->makeEditData($content, $metadatas);
	}

/**
 * カラムの値をクリアする
 *
 * @param string $multidatabaseKey 汎用データベースKey
 * @param int $colNo カラムNo
 * @return bool
 * @throws InternalErrorException
 */
	public function clrMultidatabaseColVal($multidatabaseKey, $colNo) {
		$data = [
			'MultidatabaseContent.value' . $colNo => '""',
		];

		$conditions = [
			'MultidatabaseContent.multidatabase_key' => $multidatabaseKey,
		];

		if (! $this->updateAll($data, $conditions)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		return true;
	}

/**
 * Get contents
 * 複数のコンテンツを取得
 *
 * @param array $conditions 条件
 * @param int $recursive recursive
 * @return array|bool
 */
	public function getMultidatabaseContents($conditions = [], $recursive = 0) {
		if (!$multidatabase = $this->Multidatabase->getMultidatabase()) {
			return false;
		}

		if (empty($conditions)) {
			$conditions = [];
		}

		$conditions += [
			'multidatabase_key' => $multidatabase['Multidatabase']['key']
		];

		$result = $this->find('all', [
			'recursive' => $recursive,
			'conditions' => $conditions
		]);

		return $result;
	}

/**
 * Clear values
 * 削除対象カラムに存在する値をクリアする
 *
 * @param string $multidatabaseKey 汎用データベースKey（プラグインキー）
 * @param array $colNos 列番号
 * @return bool
 * @throws InternalErrorException
 */
	public function clearValues($multidatabaseKey = null, $colNos = []) {
		if (
			is_null($multidatabaseKey)
		) {
			return false;
		}

		$conditions['multidatabase_key'] = $multidatabaseKey;

		$data = [];
		foreach ($colNos as $colNo) {
			$data['value' . $colNo] = '';
		}

		if (!$this->updateAll($data, $conditions)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		return true;
	}

/**
 * Save content
 * コンテンツを保存する
 *
 * @param array $data 保存するコンテンツデータ
 * @param bool $isUpdate 更新処理であるか(true:更新,false:新規)
 * @return bool|array
 * @throws InternalErrorException
 */
	public function saveContent($data, $isUpdate) {
		if (! $metadatas = $this->MultidatabaseContentEditPr->prGetMetadatas()) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$this->set($data);

		if (! $this->validates()) {
			return false;
		}

		$result = $this->MultidatabaseContentEdit->makeSaveData($data, $metadatas, $isUpdate);

		return $this->__saveContent($result);
	}

/**
 * Save content for Import
 * コンテンツを保存する(インポート用)
 *
 * @param array $data 保存するコンテンツデータ
 * @return bool|array
 * @throws InternalErrorException
 */
	public function saveContentForImport($data) {
		$this->Behaviors->unload('Files.Attachment');
		$this->begin();
		try {
			$this->create();
			$savedData = $this->save($data);
			if ($savedData === false) {
				$this->rollback();
				return false;
			}
			$this->commit();
		} catch (Exception $e) {
			$this->rollback($e);
		}

		return true;
	}

/**
 * Get conditions
 * UserIdと権限から参照可能なEntryを取得するCondition配列を返す
 *
 * @param int $blockId ブロックID
 * @param array $permissions 権限
 * @return array condition
 */
	public function getConditions($blockId, $permissions) {
		// contentReadable falseなら何も見えない
		if ($permissions['content_readable'] === false) {
			$conditions = ['MultidatabaseContent.id' => 0];

			return $conditions;
		}

		// デフォルト絞り込み条件
		$conditions = [
			'MultidatabaseContent.block_Id' => $blockId,
		];

		$conditions = $this->getWorkflowConditions($conditions);

		return $conditions;
	}

/**
 * Delete content
 * コンテンツの削除
 *
 * @param string $key コンテンツキー
 * @return bool
 * @throws InternalErrorException
 */
	public function deleteContentByKey($key) {
		$this->begin();

		$result = false;

		try {
			$this->contentKey = $key;

			// コメントの削除
			$this->deleteCommentsByContentKey($key);

			// 添付ファイルの削除
			if (! $this->MultidatabaseContentFile->removeFileByContentKey($key)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			$conditions = [
				'MultidatabaseContent.Key' => $key,
			];

			if ($result = $this->deleteAll($conditions, true, true)) {
				$this->commit();
			} else {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		} catch (Exception $e) {
			$this->rollback($e);
		}

		return $result;
	}

/**
 * データを保存する
 *
 * @param array $data データ配列
 * @return array|bool
 * @throws InternalErrorException
 */
	private function __saveContent($data) {
		$attachFields = $data['attachFields'];
		unset($data['attachFields']);
		$skipAttaches = $data['skipAttaches'];
		unset($data['skipAttaches']);
		$removeAttachFields = $data['removeAttachFields'];
		unset($data['removeAttachFields']);
		$attachPasswords = $data['attachPasswords'];
		unset($data['attachPasswords']);

		// 未アップロードの場合は既存ファイルを保持する
		$this->__filesAttachment($attachFields, $skipAttaches);

		$this->begin();
		$savedData = false;
		try {
			// 「検索の対象に含める(is_searchable)」は、全てOFFの場合もあるため、空でも例外にしない
			$searchContents = $this->MultidatabaseContentSearch->getSearchMetadatas();

			// is_titleのcol_no取得用
			$metadata = $this->MultidatabaseMetadata->findByKeyAndLanguageIdAndIsTitle(
					$data['Multidatabase']['key'],
					Current::read('Language.id'),
					'1'
				);
			if (! $metadata) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			$titleColum = 'MultidatabaseContent.value' . $metadata['MultidatabaseMetadata']['col_no'];

			// 新着情報を登録
			$this->Behaviors->load('Topics.Topics', [
				'fields' => [
					'title' => $titleColum,
					'summary' => $titleColum,
					'path' => '/:plugin_key/multidatabase_contents/detail/:block_id/:content_key',
				],
				'search_contents' => $searchContents
			]);

			// メールキューを登録
			$this->Behaviors->load('Mails.MailQueue', [
				'embedTags' => [
					'X-SUBJECT' => 'MultidatabaseContent.value1',
					'X-URL' => [
						'controller' => 'multidatabase_contents',
						'action' => 'detail',
					]
				],
			]);

			$savedData = $this->save($data, false);
			if ($savedData === false) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			// パスワードを登録
			$this->MultidatabaseContentFile->saveAuthKey(
				$savedData['MultidatabaseContent']['id'],
				$attachPasswords
			);

			$this->commit();

			// ファイルを削除する
			if (! empty($removeAttachFields)) {
				$this->MultidatabaseContentFile->removeAttachFile(
					$removeAttachFields, $data['MultidatabaseContent']['key']);
			}

		} catch (Exception $e) {
			$this->rollback($e);
		}
		return $savedData;
	}

/**
 * 未アップロードの場合は既存ファイルを保持する
 *
 * @param array $attachFields ファイルのフィールド
 * @param array $skipAttaches 未アップロードの場合の既存ファイル
 * @return void
 */
	private function __filesAttachment($attachFields, $skipAttaches) {
		if (! empty($attachFields) || ! empty($skipAttaches)) {
			$this->Behaviors->load('Files.Attachment', $attachFields);

			// 未アップロードの場合は既存ファイルを保持する
			// $skipAttachesは、空でも必ずarray()の想定. 空array()ならforeach抜けてくれる
			foreach ($skipAttaches as $val) {
				$this->uploadSettings($val);
			}

		} else {
			$this->Behaviors->unload('Files.Attachment');
		}
	}

/**
 * Make validation rules
 * バリデーションルールの作成
 *
 * @return array|bool
 */
	private function __makeValidation() {
		if (!$multidatabase = $this->Multidatabase->getMultidatabase()) {
			return false;
		}

		if (!$metadatas =
			$this->MultidatabaseMetadata->getEditMetadatas(
				$multidatabase['Multidatabase']['id']
			)
		) {
			return false;
		}

		$result = [];
		foreach ($metadatas as $metadata) {
			if ($metadata['is_require']) {
				$tmp = [];
				switch ($metadata['type']) {
					case 'checkbox':
						$tmp['rule'] = [
							'multiple',
							[
								'min' => 1,
							],
						];
						break;
					default:
						$tmp['rule'][] = 'notBlank';
						$tmp['message'] = sprintf(
							__d('net_commons', 'Please input %s.'),
							$metadata['name']
						);
						break;
				}
				$tmp['required'] = true;
				$result['value' . $metadata['col_no']] = $tmp;
			}
		}

		return array_merge($this->validate, $result);
	}
}
