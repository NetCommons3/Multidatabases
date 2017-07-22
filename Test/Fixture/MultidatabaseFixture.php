<?php
/**
 * MultidatabaseFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * Summary for MultidatabaseFixture
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MultidatabaseFixture extends CakeTestFixture {

/**
 * Records
 *
 * @var array
 */
	public $records = [
		[
			'id' => '1',
			'block_id' => '1',
			'key' => 'multidatabase_1',
			'name' => 'Test Multidatabase 1',
			'created_user' => '1',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '1',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '2',
			'block_id' => '2',
			'key' => 'multidatabase_2',
			'name' => 'Test Multidatabase 2',
			'created_user' => '1',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '1',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '3',
			'block_id' => '3',
			'key' => 'multidatabase_3',
			'name' => 'Test Multidatabase 3',
			'created_user' => '1',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '1',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '4',
			'block_id' => '4',
			'key' => 'multidatabase_4',
			'name' => 'Test Multidatabase 4',
			'created_user' => '1',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '1',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '5',
			'block_id' => '5',
			'key' => 'multidatabase_5',
			'name' => 'Test Multidatabase 5',
			'created_user' => '1',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '1',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '6',
			'block_id' => '6',
			'key' => 'multidatabase_6',
			'name' => 'Test Multidatabase 6',
			'created_user' => '2',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '2',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '7',
			'block_id' => '7',
			'key' => 'multidatabase_7',
			'name' => 'Test Multidatabase 7',
			'created_user' => '2',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '2',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '8',
			'block_id' => '8',
			'key' => 'multidatabase_8',
			'name' => 'Test Multidatabase 8',
			'created_user' => '2',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '2',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '9',
			'block_id' => '9',
			'key' => 'multidatabase_9',
			'name' => 'Test Multidatabase 9',
			'created_user' => '2',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '2',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '10',
			'block_id' => '10',
			'key' => 'multidatabase_10',
			'name' => 'Test Multidatabase 10',
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
