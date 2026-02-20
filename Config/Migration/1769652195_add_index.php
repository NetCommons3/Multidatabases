<?php
/**
 * AddIndex
 */

/**
 * インデックス追加のみ。
 *
 * @package NetCommons\Multidatabases\Config\Migration
 */
class AddIndex1769652195 extends CakeMigration {

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
				'multidatabase_metadatas' => array(
					'indexes' => array(
						'idx1_p_multidatabase_metadatas' => array('column' => array('multidatabase_id', 'is_title'), 'unique' => 0),
						'idx2_p_multidatabase_metadatas' => array('column' => array('multidatabase_id', 'position', '`rank`'), 'unique' => 0),
					),
				),
			),
		),
		'down' => array(
			'drop_field' => array(
				'multidatabase_metadatas' => array('indexes' => array('idx1_p_multidatabase_metadatas', 'idx2_p_multidatabase_metadatas')),
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
