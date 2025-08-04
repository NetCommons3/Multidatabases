<?php

/**
 * MultidatabaseFrameSetting Model
 * 汎用データベースフレーム設定に関するモデル処理
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MultidatabasesAppModel', 'Multidatabases.Model');

/**
 * MultidatabaseFrameSetting Model
 *
 * @author Tomoyuki OHNO (Ricksoft, Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @package NetCommons\Multidatabases\Model
 *
 */
class MultidatabaseFrameSetting extends MultidatabasesAppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = [];

/**
 * Before validate
 *
 * @param array $options オプション
 * @return bool
 */
	public function beforeValidate($options = []) {
		$this->validate = [
			'frame_key' => [
				'notBlank' => [
					'rule' => ['notBlank'],
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
				],
			],
			'content_per_page' => [
				'number' => [
					'rule' => ['notBlank'],
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
				],

			],
			'default_sort_type' => [
				'notBlank' => [
					'rule' => ['notBlank'],
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
				],
			],
		];

		return parent::beforeValidate($options);

		//The Associations below have been created with all possible keys, those that are not needed can be removed
	}

/**
 * Get frame setting
 * フレーム設定を取得する
 *
 * @return array|null
 */
	public function getMultidatabaseFrameSetting() {
		$conditions = [
			'frame_key' => Current::read('Frame.key'),
		];

		$frameSetting = $this->find('first', [
				'recursive' => -1,
				'conditions' => $conditions,
				'callbacks' => false,
			]
		);

		if (!$frameSetting) {
			$frameSetting = $this->create([
				'frame_key' => Current::read('Frame.key'),
			]);
		}

		return $frameSetting;
	}

/**
 * Save frame setting
 * フレーム設定を保存する
 *
 * @param array $data データ
 * @return bool
 * @throws InternalErrorException
 */
	public function saveMultidatabaseFrameSetting($data) {
		$this->loadModels([
			'MultidatabaseFrameSetting' => 'Multidatabases.MultidatabaseFrameSetting',
		]);

		//トランザクションBegin
		$this->begin();

		//バリデーション
		$this->set($data);
		if (!$this->validates()) {
			$this->rollback();
			return false;
		}

		try {
			//登録処理
			if (!$this->save(null, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			//トランザクションCommit
			$this->commit();
		} catch (Exception $ex) {
			//トランザクションRollback
			$this->rollback($ex);
		}
		return true;
	}
}
