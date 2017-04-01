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
		'Files.Attachment',
	];

/**
 * @var array 絞り込みフィルタ保持値
 */
	protected $_filter = [
		'status' => 0,
	];

/**
 * Before validate
 *
 * @param array $options オプション
 * @return bool
 */
	public function beforeValidate($options = []) {
		$this->validate = $this->makeValidation();

		return parent::beforeValidate($options);
	}

/**
 * 検索結果を出力するための条件設定を行う
 *
 * @param array $query クエリ(GETより取得)
 * @return bool|array
 */
	public function getSearchConds($query = []) {
		$this->loadModels([
			'Multidatabase' => 'Multidatabases.Multidatabase',
		]);

		if (empty($query)) {
			//throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			return false;
		}

		$conditions = [];

		// 開始日時、終了日時の条件設定
		if (
			!empty($query['start_dt']['value']) &&
			!empty($query['end_dt']['value'])
		) {
			$conditions['MultidatabaseContent.created between ? and ?'] = [
				$query['start_dt']['value'],
				$query['end_dt']['value']
			];
		} else {
			if (!empty($query['start_dt']['value'])) {
				$conditions['MultidatabaseContent.created <='] = $query['start_dt']['value'];
			}
			if (!empty($query['end_dt']['value'])) {
				$conditions['MultidatabaseContent.created >='] = $query['end_dt']['value'];
			}
		}

		// ステータス条件設定
		if (!empty($query['status']['value'])) {
			switch ($query['status']['value']) {
				case 'pub':
					$conditions['status'] = 1;
					break;
				case 'unpub':
					$conditions['or'] = [
						['status' => 2],
						['status' => 3]
					];
					break;
			}
		}

		// チェックボックスの値を取得して条件設定
		foreach ($query as $val) {
			switch($val['type']) {
				case 'checkbox':
				case 'radio':
				case 'select':
					$selVal[$val['field']] = $val['value'];
					break;
			}
		}
		$conditions += $this->getCondSelect($selVal);

		// キーワード検索条件設定
		$conditions += $this->getCondKeywords($query);

		$result = [
			'conditions' => $conditions,
			'order' => $this->getCondSortOrder()
		];

		return $result;
	}

/**
 * 編集用のデータを取得する
 *
 * @param array $conditions データ取得条件
 * @return array|bool
 */
	public function getEditData($conditions = []) {
		$this->loadModels([
			'Multidatabase' => 'Multidatabases.Multidatabase',
			'MultidatabaseMetadata' => 'Multidatabases.MultidatabaseMetadata',
		]);

		if (empty($conditions)) {
			return false;
		}

		$options = [
			'conditions' => $conditions,
			'recursive' => 0,
		];

		$content = $this->find('first', $options);

		if (!$content) {
			return false;
		}

		$metadatas = $this->MultidatabaseMetadata->getEditMetadatas();

		if (!$metadatas) {
			return false;
		}

		foreach ($metadatas as $metadata) {
			if (
				isset($content['MultidatabaseContent']['value' . $metadata['col_no']]) &&
				$content['MultidatabaseContent']['value' . $metadata['col_no']] <> ''
			) {
				$tmpValue = $content['MultidatabaseContent']['value' . $metadata['col_no']];
				switch ($metadata['type']) {
					case 'radio':
					case 'select':
						$content['MultidatabaseContent']['value' . $metadata['col_no']]
							= md5($tmpValue);
						break;
					case 'checkbox' :
						$tmpValArr = explode('||', $tmpValue);
						$tmpValRes = [];
						foreach ($tmpValArr as $val) {
							$tmpValRes[] = md5($val);
						}
						$content['MultidatabaseContent']['value' . $metadata['col_no']]
							= $tmpValRes;
						break;
					default:
						break;
				}
			}
		}

		return $content;
	}

/**
 * Get contents
 * 複数のコンテンツを取得
 *
 * @param array $conditions 条件
 * @return array|bool
 */
	public function getMultidatabaseContents($conditions = []) {
		$this->loadModels([
			'Multidatabase' => 'Multidatabases.Multidatabase',
		]);

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
			'recursive' => 0,
			'conditions' => $conditions
		]);

		return $result;
	}

/**
 * キーワード検索条件の出力
 *
 * @param array $query クエリ(GETより取得)
 * @return array
 */
	public function getCondKeywords($query = []) {
		$this->loadModels([
			'MultidatabaseMetadata' => 'Multidatabases.MultidatabaseMetadata',
		]);

		if (empty($query)) {
			return [];
		}

		// 検索対象のメタデータを取得
		$searchMetadatas = $this->MultidatabaseMetadata->getSearchMetadatas();

		// キーワード検索時の検索の種類を設定
		$condType = 'and';

		if (isset($query['type']['value'])) {
			$condType = $query['type']['value'];
		}

		// キーワードの値を取得して条件設定
		$keywords = '';
		if (isset($query['keywords']['value'])) {
			$keywords = trim($query['keywords']['value']);
			if ($condType !== 'phrase') {
				$keywords = str_replace('　', ' ', $keywords);
			}
		}

		$arrKeywords = [];
		if (!empty($keywords)) {
			$arrKeywords = explode(' ', $keywords);
		}

		$result = [];
		if (!empty($arrKeywords)) {
			foreach ($searchMetadatas as $metaField) {
				$tmpConds = [];
				if (
					$condType === 'phrase' ||
					count($arrKeywords) === 1
				) {
					$tmpConds = [
						$metaField . ' like' => '%' . $keywords . '%'
					];
				} else {
					foreach ($arrKeywords as $keyword) {
						$tmpConds[$condType][] = [
							$metaField . ' like' => '%' . $keyword . '%'
						];
					}
				}
				$result['or'][] = $tmpConds;
			}
		}

		return $result;
	}

/**
 * 複数選択、単一選択の絞込条件出力
 *
 * @param array $values 値
 * @return array
 */
	public function getCondSelect($values = []) {
		if (empty($values)) {
			return [];
		}

		$this->loadModels([
			'MultidatabaseMetadata' => 'Multidatabases.MultidatabaseMetadata',
		]);

		$metadatas = $this->MultidatabaseMetadata->getEditMetadatas();
		$result = [];
		$valueKey = null;
		foreach ($metadatas as $metadata) {
			switch ($metadata['type']) {
				case 'select':
				case 'checkbox':
					$valueKey = 'value' . $metadata['col_no'];
					break;
			}

			if (
				! is_null($valueKey) &&
				isset($values[$valueKey]) &&
				$values[$valueKey] !== '0'
			) {
				switch ($metadata['type']) {
					case 'select':
						foreach ($metadata['selections'] as $selection) {
							if (md5($selection) === $values[$valueKey]) {
								$result['MultidatabaseContent.' . $valueKey] = "{$selection}";
								break;
							}
						}
						break;
					case 'checkbox':
						foreach ($metadata['selections'] as $selection) {
							if (md5($selection) === $values[$valueKey]) {
								$result['or'] = [
									['MultidatabaseContent.' . $valueKey => "{$selection}"],
									['MultidatabaseContent.' . $valueKey . ' like' => "%{$selection}||%"],
									['MultidatabaseContent.' . $valueKey . ' like' => "%||{$selection}%"],
								];
								break;
							}
						}
						break;
				}
			}
		}

		return $result;
	}

/**
 * ソート条件出力
 *
 * @param string $sortCol ソートするカラム
 * @return string
 */
	public function getCondSortOrder($sortCol = '') {
		if (empty($sortCol)) {
			$sortCol = null;
		}

		if (
			isset($sortCol) &&
			!is_null($sortCol) &&
			(
				strstr($sortCol, 'value') <> false ||
				in_array($sortCol, ['created', 'modified'])
			)
		) {
			if (strstr($sortCol, '_desc')) {
				$sortCol = str_replace('_desc', '', $sortCol);
				$sortColDir = 'desc';
			} else {
				$sortCol = $sortCol;
				$sortColDir = 'asc';
			}
		} else {
			$sortCol = 'created';
			$sortColDir = 'desc';
		}

		$sortOrder = 'MultidatabaseContent.' . $sortCol . ' ' . $sortColDir;

		return $sortOrder;
	}

/**
 * Make validation rules
 * バリデーションルールの作成
 *
 * @return array|bool
 */
	public function makeValidation() {
		$this->loadModels([
			'MultidatabaseMetadata' => 'Multidatabases.MultidatabaseMetadata',
			'Multidatabase' => 'Multidatabases.Multidatabase',
		]);

		if (!$multidatabase = $this->Multidatabase->getMultidatabase()) {
			return false;
		}

		if (!$multidatabaseMetadatas =
			$this->MultidatabaseMetadata->getEditMetadatas(
				$multidatabase['Multidatabase']['id']
			)
		) {
			return false;
		}

		$result = [];
		foreach ($multidatabaseMetadatas as $metadata) {
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
						$tmp['message'] = sprintf(__d('net_commons', 'Please input %s.'), $metadata['name']);
						break;
				}
				$tmp['required'] = true;
				$result['value' . $metadata['col_no']] = $tmp;
			}
		}

		return Hash::merge($this->validate, $result);
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
			|| empty($currentMetadatas)
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
 * @return bool|array
 * @throws InternalErrorException
 */
	public function saveContent($data) {
		$this->loadModels([
			'Multidatabase' => 'Multidatabases.Multidatabase',
			'MultidatabaseMetadata' => 'Multidatabases.MultidatabaseMetadata',
		]);

		if (! $multidatabase = $this->Multidatabase->getMultidatabase()) {
			return false;

		}
		if (! $metadatas = $this->MultidatabaseMetadata->getMetadatasColNo(
			$multidatabase['Multidatabase']['id'])
		) {
			return false;
		}

		$this->set($data);

		if (!$this->validates()) {
			return false;
		}

		$multidatabaseContent = $data['MultidatabaseContent'];
		foreach ($multidatabaseContent as $key => $val) {
			if (strstr($key, 'value') <> false) {
				$colNo = (int)str_replace('value', '', $key);

				if (isset($metadatas[$colNo])) {
					$selections = [];
					if (isset($metadatas[$colNo]['selections'])) {
						$selections = json_decode($metadatas[$colNo]['selections'], true);
					}

					switch ($metadatas[$colNo]['type']) {
						case 'select':
							$tmp = $data['MultidatabaseContent'][$key];
							foreach ($selections as $metaSel) {
								if (md5($metaSel) === $tmp) {
									$data['MultidatabaseContent'][$key] = $metaSel;
									break;
								}
							}
							break;
						case 'checkbox':
							$tmpArr = $data['MultidatabaseContent'][$key];
							if (empty($tmpArr)) {
								$data['MultidatabaseContent'][$key] = '';
								break;
							}
							$tmpArr = $data['MultidatabaseContent'][$key];
							$tmpRes = [];
							foreach ($selections as $metaSel) {
								if (in_array(md5($metaSel), $tmpArr)) {
									$tmpRes[] = $metaSel;
								}
							}
							if (empty($tmpRes)) {
								$data['MultidatabaseContent'][$key] = '';
							} else {
								$data['MultidatabaseContent'][$key] = implode('||', $tmpRes);
							}
							break;
						/*
						case 'autonumber':
							$currentNum = $multidatabase['Multidatabase']['auto_number_sequence'];
							$myNum = $currentNum + 1;
							$data['MultidatabaseContent'][$key] = $myNum;
							// Multidatabasesを更新
						*/
						case 'file':
						case 'image':
							if (empty($data['MultidatabaseContent'][$key]['name'])) {
								// 未アップロードの場合は既存ファイルを保持する
								$this->uploadSettings($key . '_attach');
							} else {
								$data['MultidatabaseContent'][$key . '_attach'] =
									$data['MultidatabaseContent'][$key];
								$data['MultidatabaseContent'][$key] = $val['name'];
								$attachFields[] = $key .'_attach';
							}
							$data['MultidatabaseContent'][$key] = '';
							break;
						default:
							break;
					}
				}
			}
		}

		if (!empty($attachFields)) {
			$this->Behaviors->load('Files.Attachment',$attachFields);
		}

		$this->begin();
		try {
			//$this->create();

			if (! $searchContents = $this->MultidatabaseMetadata->getSearchMetadatas()) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			$this->Behaviors->load('Topics.Topics', [
				'fields' => [
					'title' => 'MultidatabaseContent.value1',
					'summary' => 'MultidatabaseContent.value1',
					'path' => '/:plugin_key/multidatabase_contents/detail/:block_id/:content_key',
				],
				'search_contents' => $searchContents
			]);

			$this->Behaviors->load('Mails.MailQueue', [
				'embedTags' => [
					'X-SUBJECT' => 'MultidatabaseContent.value1',
					'X-URL' => [
						'controller' => 'multidatabase_contents'
					]
				],
			]);

			if (($savedData = $this->save($data, false)) === false) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			$this->commit();
		} catch (Exception $e) {
			$this->rollback($e);
		}

		return $savedData;
	}

/**
 * Get File download URL
 * ファイルダウンロードURLを出力
 *
 * @return void
 */
	public function getFileURL() {
		$contentKey = $this->request->params['pass'][0];
		$options['field'] = $this->request->params['pass'][1];
		$options['size'] = Hash::get($this->request->params['pass'], 2, 'medium');
		return $this->Download->doDownload($contentKey, $options);
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
}
