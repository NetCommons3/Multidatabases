<?php
/**
 * Multidatabase Model
 * 汎用データベース基本データに関するモデル処理
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MultidatabasesAppModel', 'Multidatabases.Model');
App::uses('MultidatabasesMetadataEditModel', 'MultidatabaseMetadataEdit.Model');

/**
 * Multidatabase Model
 *
 * @author Tomoyuki OHNO (Ricksoft, Co., LTD.) <ohno.tomoyuki@ricksoft.jp>
 * @package NetCommons\Multidatabases\Model
 */
class Multidatabase extends MultidatabasesAppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = [];

/**
 * use behaviors
 *
 * @var array
 */
	public $actsAs = [
		'Blocks.Block' => [
			'name' => 'Multidatabase.name',
			'loadModels' => [
				'Like' => 'Likes.Like',
				'BlockSetting' => 'Blocks.BlockSetting',
			],
		],
		'NetCommons.OriginalKey',
	];

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = [
		'Block' => [
			'className' => 'Blocks.Block',
			'foreignKey' => 'block_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
		],
	];

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = [
		'MultidatabaseContent' => [
			'className' => 'Multidatabases.MultidatabaseContent',
			'foreignKey' => 'multidatabase_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => '',
		],
		'MultidatabaseMetadata' => [
			'className' => 'Multidatabases.MultidatabaseMetadata',
			'foreignKey' => 'multidatabase_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => '',
		],
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
			'MultidatabaseMetadataEdit' => 'Multidatabases.MultidatabaseMetadataEdit',
			'MultidatabaseFrameSetting' => 'Multidatabases.MultidatabaseFrameSetting',
			'MultidatabaseSetting' => 'Multidatabases.MultidatabaseSetting',
			'MultidatabaseMetadata' => 'Multidatabases.MultidatabaseMetadata',
		]);
	}

/**
 * Called during validation operations, before validation. Please note that custom
 * validation rules can be defined in $validate.
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if validate operation should continue, false to abort
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforevalidate
 * @see Model::save()
 */
	public function beforeValidate($options = []) {
		$this->validate = $this->__makeValidation();
		return parent::beforeValidate($options);
	}

/**
 * Called after each successful save operation.
 *
 * @param bool $created True if this save created a new record
 * @param array $options Options passed from Model::save().
 * @return void
 * @throws InternalErrorException
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#aftersave
 * @see Model::save()
 */
	public function afterSave($created, $options = []) {
		// MultidatabaseSetting登録
		if (isset($this->data['MultidatabaseSetting'])) {
			$this->MultidatabaseSetting->set($this->data['MultidatabaseSetting']);
			$this->MultidatabaseSetting->save(null, false);
		}

		// MultidatabaseFrameSetting登録
		if (isset($this->MultidatabaseFrameSetting->data['MultidatabaseFrameSetting']) &&
			! $this->MultidatabaseFrameSetting->data['MultidatabaseFrameSetting']['id']
		) {
			if (! $this->MultidatabaseFrameSetting->save(null, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}

		// MultidatabaseMetadata登録
		if (! isset($this->data['MultidatabaseMetadata'])) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$this->MultidatabaseMetadata->saveMetadatas($this->data);

		parent::afterSave($created, $options);
	}

/**
 * Create Multidatabase data
 * 汎用データベースを作成する
 *
 * @return array
 */
	public function createMultidatabase() {
		$multidatabase = $this->createAll([
			'Multidatabase' => [
				'name' => __d('multidatabases', 'New multidatabase %s', date('YmdHis')),
			],
			'Block' => [
				'room_id' => Current::read('Room.id'),
				'language_id' => Current::read('Language.id'),
			],

		]);

		return ($multidatabase + $this->MultidatabaseSetting->createBlockSetting());
	}

/**
 * Get Multidatabase data
 * 汎用データベースを取得する
 *
 * @return array
 */
	public function getMultidatabase() {
		$multidatabase = $this->find('first', [
			'recursive' => 0,
			'conditions' => $this->getBlockConditionById(),
		]);

		if (!$multidatabase) {
			return $multidatabase;
		}

		return ($multidatabase + $this->MultidatabaseSetting->getMultidatabaseSetting());
	}

/**
 * Save Multidatabases
 * 汎用データベースを保存する
 *
 * @param array $data received post data
 * @return bool True on success, false on validation errors
 * @throws InternalErrorException
 */
	public function saveMultidatabase($data) {
		//トランザクションBegin
		$this->begin();
		try {
			//登録処理
			$result = $this->save($data, false);

			if (!$result) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//トランザクションCommit
			$this->commit();
			return true;
		} catch (Exception $ex) {
			//トランザクションRollback
			$this->rollback($ex);
			return false;
		}
	}

/**
 * Delete Multidatabases
 * 汎用データベースを削除する
 *
 * @param array $data received post data
 * @return mixed On success Model::$data if its not empty or true, false on failure
 * @throws InternalErrorException
 */
	public function deleteMultidatabase($data) {
		//トランザクションBegin
		$this->begin();

		$conditions = [
			$this->alias . '.key' => $data['Multidatabase']['key'],
		];
		$multidatabases = $this->find('list', [
			'recursive' => -1,
			'conditions' => $conditions,
		]);
		$multidatabaseIds = array_keys($multidatabases);

		try {
			if (!$this->deleteAll(
				[$this->alias . '.key' => $data['Multidatabase']['key']], false, false
			)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			$this->MultidatabaseContent->blockKey = $data['Block']['key'];
			$conditions = [$this->MultidatabaseContent->alias . '.multidatabase_id' => $multidatabaseIds];
			if (!$this->MultidatabaseContent->deleteAll($conditions, false, true)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			$conditions = [
				$this->MultidatabaseMetadata->alias . '.multidatabase_id' => $multidatabaseIds
			];
			if (!$this->MultidatabaseMetadata->deleteAll($conditions, false, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//Blockデータ削除
			$this->deleteBlock($data['Block']['key']);

			//トランザクションCommit
			$this->commit();
		} catch (Exception $ex) {
			//トランザクションRollback
			$this->rollback($ex);
		}
		return true;
	}

/**
 * Make validation rules
 * バリデーションルールの作成
 *
 * @return array|bool
 */
	private function __makeValidation() {
		$result = [
			'name' => [
				'notBlank' => [
					'rule' => ['notBlank'],
					'message' => sprintf(
						__d('net_commons', 'Please input %s.'),
						__d('multidatabases', 'Multidatabase name')
					),
					'required' => true
				],
			],
		];
		return ValidateMerge::merge($this->validate, $result);
	}

/**
 * 汎用DB設定のバリデーションを行う
 *
 * @param array $data データ配列
 * @param array $dbData DB上のメタデータ
 * @return bool|array
 */
	public function doValidate($data, $dbData = null) {
		$this->set($data);

		$result['errors']['Multidatabase'] = [];
		$result['has_err'] = false;
		$result['data'] = $data;

		if (! isset($data['MultidatabaseMetadata'])) {
			$result['hasError'] = true;
		} else {
			$metadataGroups = $data['MultidatabaseMetadata'];
			$metadatasResult = $this->MultidatabaseMetadata->doValidateMetadatas(
				$metadataGroups, $dbData['MultidatabaseMetadata']
			);
			$result['data']['MultidatabaseMetadata'] = $metadatasResult['data'];
			$result['errors']['MultidatabaseMetadata'] = $metadatasResult['errors'];

			if ($metadatasResult['has_err']) {
				$result['has_err'] = true;
			}
		}

		if (! $this->validates()) {
			$result['errors']['Multidatabase'] = $this->ValidationErrors;
			$result['has_err'] = true;
		}

		return $result;
	}
}
