<?php
/**
 * MultidatabaseFrameSettingFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * Summary for MultidatabaseFrameSettingFixture
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MultidatabaseFrameSettingFixture extends CakeTestFixture {

/**
 * Records
 *
 * @var array
 */
	public $records = [
		[
			'id' => '1',
			'frame_key' => 'frame_key_1',
			'content_per_page' => '5',
			'default_sort_type' => '',
			'created_user' => '1',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '1',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '2',
			'frame_key' => 'frame_key_2',
			'content_per_page' => '5',
			'default_sort_type' => '',
			'created_user' => '1',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '1',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '3',
			'frame_key' => 'frame_key_3',
			'content_per_page' => '5',
			'default_sort_type' => '',
			'created_user' => '1',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '1',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '4',
			'frame_key' => 'frame_key_4',
			'content_per_page' => '5',
			'default_sort_type' => '',
			'created_user' => '1',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '1',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '5',
			'frame_key' => 'frame_key_5',
			'content_per_page' => '5',
			'default_sort_type' => '',
			'created_user' => '1',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '1',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '6',
			'frame_key' => 'frame_key_6',
			'content_per_page' => '5',
			'default_sort_type' => '',
			'created_user' => '2',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '2',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '7',
			'frame_key' => 'frame_key_7',
			'content_per_page' => '5',
			'default_sort_type' => '',
			'created_user' => '2',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '2',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '8',
			'frame_key' => 'frame_key_8',
			'content_per_page' => '5',
			'default_sort_type' => '',
			'created_user' => '2',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '2',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '9',
			'frame_key' => 'frame_key_9',
			'content_per_page' => '5',
			'default_sort_type' => '',
			'created_user' => '2',
			'created' => '2016/04/01 1:10:20',
			'modified_user' => '2',
			'modified' => '2016/04/01 1:10:20',
		],
		[
			'id' => '10',
			'frame_key' => 'frame_key_10',
			'content_per_page' => '5',
			'default_sort_type' => '',
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
