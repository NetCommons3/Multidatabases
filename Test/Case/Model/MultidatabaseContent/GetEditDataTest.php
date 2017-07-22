<?php
/**
 * MultidatabaseContent::getEditData()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft, Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * MultidatabaseContent::getEditData()のテスト
 *
 * @author Tomoyuki OHNO (Ricksoft, Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @package NetCommons\Multidatabases\Test\Case\Model\MultidatabaseMetadataInit
 */
class GetEditDataTest extends CakeTestCase {

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'multidatabases';

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = [
		'plugin.multidatabases.multidatabase',
		'plugin.multidatabases.multidatabase_metadata',
		'plugin.multidatabases.multidatabase_metadata_setting',
		'plugin.multidatabases.multidatabase_content',
		'plugin.multidatabases.multidatabase_frame_setting',
		'plugin.multidatabases.block_setting_for_multidatabase',
		'plugin.workflow.workflow_comment',
	];

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->MultidatabaseContent = ClassRegistry::init('Multidatabases.MultidatabaseContent');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->MultidatabaseContent);
		parent::tearDown();
	}

	public function test() {
		$id = 1;

		$data = $this->MultidatabaseContent->getEditData([
			'MultidatabaseContent.id' => $id,
			'MultidatabaseContent.is_active' => true,
			'MultidatabaseContent.is_latest' => true,
		]);
		var_dump($data);
		exit;
	}


}
