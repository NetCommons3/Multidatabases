<?php
/**
 * MultidatabaseMetadataSettingFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * Summary for MultidatabaseMetadataSettingFixture
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MultidatabaseMetadataSettingFixture extends CakeTestFixture {

/**
 * Records
 *
 * @var array
 */
	public $records = [
		[
			'id' => '1',
			'auto_number_sequence' => '0',
			'created_user' => '1',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '1',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '2',
			'auto_number_sequence' => '0',
			'created_user' => '1',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '1',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '3',
			'auto_number_sequence' => '0',
			'created_user' => '1',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '1',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '4',
			'auto_number_sequence' => '0',
			'created_user' => '1',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '1',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '5',
			'auto_number_sequence' => '0',
			'created_user' => '1',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '1',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '6',
			'auto_number_sequence' => '0',
			'created_user' => '2',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '2',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '7',
			'auto_number_sequence' => '0',
			'created_user' => '2',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '2',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '8',
			'auto_number_sequence' => '0',
			'created_user' => '2',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '2',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '9',
			'auto_number_sequence' => '0',
			'created_user' => '2',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '2',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '10',
			'auto_number_sequence' => '0',
			'created_user' => '2',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '2',
			'modified' => '2016/04/01 1:10:20',
		],
	];

/**
 * Initialize the fixture.
 *
 * @return void
 */
	public function init() {
		require_once App::pluginPath('Multidatabases') . 'Config' . DS . 'Schema' . DS . 'schema.php';
		$this->fields = (new MultidatabasesSchema())->tables[Inflector::tableize($this->name)];
		parent::init();
	}
}
