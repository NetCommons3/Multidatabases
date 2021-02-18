<?php
/**
 * MultidatabaseMetadata Model
 * 汎用データベースメタデータ定義に関するモデル処理
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MultidatabasesAppModel', 'Multidatabases.Model');
App::uses('MultidatabasesMetadataInitModel', 'MultidatabaseMetadataInit.Model');
App::uses('MultidatabasesMetadataEditModel', 'MultidatabaseMetadataEdit.Model');
App::uses('MultidatabasesMetadataEditCnvModel', 'MultidatabasesMetadataEditCnv.Model');
App::uses('MultidatabasesMetadataSettingModel', 'MultidatabaseMetadataSetting.Model');
App::uses('CakeSession', 'Model/Datasourse');

/**
 * MultidatabaseMetadata Model
 *
 * @author Tomoyuki OHNO (Ricksoft, Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @package NetCommons\Multidatabases\Model
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class MultidatabaseMetadata extends MultidatabasesAppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'multidatabase_metadatas';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = [];

/**
 * hasOne associations
 *
 * @var array
 */
	public $hasOne = [
		'MultidatabaseMetadataSetting' => [
			'className' => 'Multidatabases.MultidatabaseMetadataSetting',
			'foreignKey' => 'id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		]
	];

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = [
		'Multidatabase' => [
			'className' => 'Multidatabases.Multidatabase',
			'foreignKey' => 'multidatabase_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
		],
		'Language' => [
			'className' => 'M17n.Language',
			'foreignKey' => 'language_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
		],
	];

/**
 * メタデータの属性改ざんチェックに用いる
 * DBに登録されているメタデータ毎の情報が入る
 *
 * @var array
 */
	public $dbMetaData = null;

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
			'MultidatabaseContent' => 'Multidatabases.MultidatabaseContent',
			'MultidatabaseMetadataEdit' => 'Multidatabases.MultidatabaseMetadataEdit',
			'MultidatabaseMetadataEditCnv' => 'Multidatabases.MultidatabaseMetadataEditCnv',
			'MultidatabaseMetadataSetting' => 'Multidatabases.MultidatabaseMetadataSetting',
			'MultidatabaseFrameSetting' => 'Multidatabases.MultidatabaseFrameSetting',
			'MultidatabaseSetting' => 'Multidatabases.MultidatabaseSetting',
		]);
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
		if ($created && $this->data['MultidatabaseMetadata']['type'] === 'autonumber') {
			// MultidatabaseMetadataSetting追加
			$metadataId = $this->getInsertID();
			$metadataSettingVals = [
				'id' => $metadataId,
				'auto_number_sequence' => 0
			];
			if (! $this->MultidatabaseMetadataSetting->save($metadataSettingVals)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}

		parent::afterSave($created, $options);
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
 * Get one metadata
 * メタデータを1件取得する
 *
 * @param int|string|null $key キー
 * @param string $type 出力方法
 * @return array|bool
 */
	public function getMetadata($key = null, $type = 'id') {
		if (empty($key)) {
			return false;
		}

		$multidatabase = $this->Multidatabase->getMultidatabase();

		if (!$multidatabase) {
			return $multidatabase;
		}

		$conditions['multidatabase_id'] = $multidatabase['Multidatabase']['id'];

		if ($type == 'key') {
			$conditions['key'] = $key;
		} else {
			$conditions['id'] = (int)$key;
			if ($conditions['id'] < 1) {
				return false;
			}
		}

		$metadata = $this->find('first',
			[
				'conditions' => $conditions,
				'recursive' => -1,
			]
		);

		return $metadata;
	}

/**
 * カラムNoをKeyとしてメタデータリストを出力する
 *
 * @param int $multidatabaseId 汎用データベースID
 * @return array|bool
 */
	public function getMetadatasColNo($multidatabaseId = 0) {
		if (empty($multidatabaseId)) {
			return false;
		}

		$metadatas = $this->getMetadatas($multidatabaseId);
		$result = [];

		foreach ($metadatas as $metadata) {
			if (!isset( $metadata['MultidatabaseMetadata'])) {
				return false;
			}
			$tmp = $metadata['MultidatabaseMetadata'];

			$result[$tmp['col_no']] = $tmp;

		}
		return $result;
	}

/**
 * Get metadatas
 * DBよりメタデータを取得する
 *
 * @param int $multidatabaseId 汎用データベースID
 * @param array $extraConditions 追加条件
 * @return array|bool
 */
	public function getMetadatas($multidatabaseId = 0, $extraConditions = []) {
		if (empty($multidatabaseId)) {
			if (! $multidatabase = $this->Multidatabase->getMultidatabase()) {
				return false;
			}
			$multidatabaseId = $multidatabase['Multidatabase']['id'];
		}

		$conditions['multidatabase_id'] = $multidatabaseId;

		if (!empty($conditions)) {
			$conditions += $extraConditions;
		}

		$orders = [
			'MultidatabaseMetadata.position ASC',
			'MultidatabaseMetadata.rank ASC',
		];

		$metadatas = $this->find('all', [
			'conditions' => $conditions,
			'order' => $orders,
		]);

		return $metadatas;
	}

/**
 * Get metadata group
 * メタデータグループを取得する
 * $result[グループ][並び順] = $metadataを返す
 *
 * @param int $multidatabaseId 汎用データベースID
 * @return bool|array
 */
	public function getMetadataGroups($multidatabaseId) {
		if (empty($multidatabaseId)) {
			return false;
		}

		$metadatas = $this->getEditMetadatas($multidatabaseId);

		if (!$metadatas) {
			return false;
		}

		foreach ($metadatas as $metadata) {
			$result[$metadata['position']][$metadata['rank']] = $metadata;
		}

		return $result;
	}

/**
 * Merge Group Metadatas
 * グループごとに分かれているメタデータを１つの配列に統合する
 *
 * @param array $data メタデータグループ配列
 * @return array
 */
	public function mergeGroupToMetadatas($data) {
		// 1段目も項目なしがありえるため、0番目からループスタート
		//for ($i = 1; $i <= 3; $i++) {
		for ($i = 0; $i <= 3; $i++) {
			if (!isset($data[$i])) {
				$data[$i] = [];
			}
		}

		$result['MultidatabaseMetadata'] = array_merge($data[0], $data[1], $data[2], $data[3]);
		return $result;
	}

/**
 * Save metadata (New/Update)
 * メタデータを保存する（新規・更新）
 *
 * @param array $data データ配列
 * @return void
 * @throws InternalErrorException
 */
	public function saveMetadatas($data) {
		$metadatas = $this->MultidatabaseMetadataEdit->makeSaveData($data);

		// 削除ID,カラムの確認
		$delMetaIdColNos =
			$this->__getDeleteMetadatas(
				$data['Multidatabase']['id'],
				$metadatas
			);

		// MultidatabaseMetadata削除
		if (! empty($delMetaIdColNos)) {
			$this->__deleteMetadatas(
				$data['Multidatabase']['key'],
				$delMetaIdColNos
			);
		}

		// doValidateMetadatas関数でメタデータの数分、バリデーションを実行していて、
		// 動的にバリデーションルールを変更していたが、
		// saveAllで実行されるバリデーションの時は、doValidateMetadatas関数を経由していないため、
		// 動的にバリデーションルールが変更できない。

		// 上記の理由で、正しいデータが入力されていてもバリデーションエラーが発生してしまうため、
		// ここではバリデーションを実行しない。（saveAll前に一度バリデーションを実施しているため）
		// 保存
		if (! $this->saveAll($metadatas, ['validate' => false])) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
	}

/**
 * Get metadatas for edit
 * 編集用のメタデータを取得する
 *
 * @param int|null $multidatabaseId 汎用データベースID
 * @return array|bool
 */
	public function getEditMetadatas($multidatabaseId = 0) {
		if (empty($multidatabaseId)) {
			if (! $multidatabase = $this->Multidatabase->getMultidatabase()) {
				return false;
			}
			$multidatabaseId = $multidatabase['Multidatabase']['id'];
		}

		$metadatas = $this->getMetadatas($multidatabaseId);
		$result = $this->MultidatabaseMetadataEditCnv->normalizeEditMetadatas($metadatas);

		return $result;
	}

/**
 * メタデータのバリデーションを行う
 *
 * @param array $metadataGroups メタデータグループ配列
 * @param array $dbData DB上のメタデータ
 * @return bool|array
 */
	public function doValidateMetadatas($metadataGroups, $dbData = null) {
		$metadatas = $this->mergeGroupToMetadatas($metadataGroups);

		$result['has_err'] = false;
		$result['errors'] = [];
		$result['data'] = $metadatas['MultidatabaseMetadata'];

		foreach ($metadatas['MultidatabaseMetadata'] as $key => $metadata) {
			$this->dbMetaData = [];
			// メタデータの属性改ざんチェックに使用するため、
			// バリデーション対象のメタデータ（入力値）とDBに登録された同一のメタデータの属性を取得する
			if (!empty($dbData)) {
				foreach ($dbData as $dbKey => $dbItem) {
					if ($dbItem['id'] == $metadata['id']) {
						$this->dbMetaData = $dbItem;
						// 処理軽減のため、unset
						unset($dbData[$dbKey]);
						break;
					}
				}
			}
			$this->set($metadata);

			$result['data'][$key] = $this->MultidatabaseMetadataEditCnv
				->normalizeEditMetadatasType($metadata);
			$result['errors'][$key] = '';

			if (! $this->validates()) {
				$result['has_err'] = true;
				$result['errors'][$key] = $this->ValidationErrors;
				// バリデーションエラーは全てここで取得する
				// エラーフラグとエラーメッセージは、メタデータのフィールド毎に管理する
				foreach ($this->validationErrors as $field => $errors) {
					$result['data'][$key]['err_msg'][$field] = '';
					// このフィールドでエラーが発生した
					$result['data'][$key]['has_err'][$field] = 1;

					// エラーメッセージの結合
					foreach ($errors as $index => $errorMsg) {
						// バリデーションエラーが複数発生することを想定して、foreachでエラーメッセージを連結する
						// 最後のループ以外、改行コードは付けておく
						$result['data'][$key]['err_msg'][$field] .=
							(count($errors) - 1) === $index
							? $errorMsg
							: $errorMsg . "\n";
					}
				}
			}
		}

		return $result;
	}

/**
 * Delete metadata
 * メタデータを削除する
 *
 * @param string $multidatabaseKey 汎用データベースKey
 * @param array $metadataIdColNos メタデータID・カラムNo配列
 * @return void
 * @throws InternalErrorException
 */
	private function __deleteMetadatas($multidatabaseKey = null, $metadataIdColNos = []) {
		if (
			empty($multidatabaseKey) ||
			empty($metadataIdColNos)
		) {
			return false;
		}

		foreach ($metadataIdColNos as $metadataIdColNo) {
			// 当該メタデータを削除する
			if (! $this->delete($metadataIdColNo['metadata_id'], false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			// 当該メタデータに関連するカラムの値をクリアする
			$this->MultidatabaseContent->clrMultidatabaseColVal(
				$multidatabaseKey, $metadataIdColNo['col_no']);
		}
	}

/**
 * Get Metadata columns for delete
 * 削除対象のメタデータIDを取得する
 *
 * @param int|null $multidatabaseId 汎用データベースID
 * @param array $currentMetadatas メタデータ配列
 * @return array
 */
	private function __getDeleteMetadatas($multidatabaseId = null, $currentMetadatas = []) {
		if (
			empty($multidatabaseId) ||
			empty($currentMetadatas) ||
			!$beforeSaveMetadatas = $this->getEditMetadatas($multidatabaseId)
		) {
			return [];
		}

		return $this->MultidatabaseMetadataEdit->diffBeforeMetadatas(
			$beforeSaveMetadatas, $currentMetadatas);
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
						__d('multidatabases', 'Field name')
					),
					'required' => true
				],
			],
		];
		// DBに登録されていて、かつ削除されていないメタデータがあれば、改ざんチェックを行う
		if (!empty($this->dbMetaData)) {
			$result['type'] = [
				'doNotChange' => [
					// DB上のメタデータの属性情報が入る
					'rule' => ['equalTo', $this->dbMetaData['type']],
					'message' => sprintf(
						__d('multidatabases', 'This item cannot be edited.')
					),
					'on' => 'update'
				]
			];
		}
		// ValidateMerge::mergeで、既存のバリデーションルールと
		// ここで定義したバリデーションルールをマージするが、
		// 改ざんチェック用のバリデーションルールの比較対象（$this->dbMetaData['type']）が
		// バリデーションを実行するたびに増加する不具合があったため、
		// 予め、バリデーションルールを削除しておく
		unset($this->validate['type']['doNotChange']['rule']);
		return ValidateMerge::merge($this->validate, $result);
	}
}
