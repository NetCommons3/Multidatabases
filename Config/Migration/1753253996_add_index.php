<?php
/**
 * AddIndex Migration
 * インデックスを追加するためのMigration
 *
 * @author Hiroki Mochizuki <fj2532ir@fujitsu.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * AddIndex Migration
 *
 * @author Hiroki Mochizuki <fj2532ir@fujitsu.com>
 * @package NetCommons\Multidatabases\Config\Migration
 *
 */
class AddIndex extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'add_index';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'create_field' => array(
				'multidatabase_contents' => array(
					'indexes' => array(
						'key' => array('column' => 'key', 'unique' => 0),
						'block_id' => array('column' => 'block_id', 'unique' => 0),
					),
				),
			),
		),
		'down' => array(
			'drop_field' => array(
				'multidatabase_contents' => array('indexes' => array('key', 'block_id')),
			),
		),
	);

/**
 * Before migration callback
 *
 * @param string $direction Direction of migration process (up or down)
 * @return bool Should process continue
 */
	public function before($direction) {
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction Direction of migration process (up or down)
 * @return bool Should process continue
 */
	public function after($direction) {
		return true;
	}
}
