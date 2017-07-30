<?php
/**
 * MultidatabaseContentExportsController Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft, Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MultidatabasesAppController', 'Multidatabases.Controller');

/**
 * MultidatabaseContentExportsController Controller
 *
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @package NetCommons\Multidatabases\Controller
 */
class MultidatabaseContentExportsController extends MultidatabasesAppController {

/**
 * layout
 *
 * @var array
 */
	public $layout = 'NetCommons.setting';

/**
 * use models
 *
 * @var array
 */
	public $uses = [
		'Multidatabases.Multidatabase',
		'Multidatabases.MultidatabaseContent',
	];

/**
 * use components
 *
 * @var array
 */
	public $components = [
		'NetCommons.Permission' => [
			//アクセスの権限
			'allow' => [
				'edit' => 'block_permission_editable',
			],
		],
		'Files.Download',
	];

/**
 * use helpers
 *
 * @var array
 */
	public $helpers = [
		'Blocks.BlockForm',
		'Blocks.BlockTabs' => [
			'mainTabs' => [
				'block_index' => ['url' => ['controller' => 'multidatabase_blocks']],
				'frame_settings' => ['url' => ['controller' => 'multidatabase_frame_settings']],
			],
			'blockTabs' => [
				'block_settings' => ['url' => ['controller' => 'multidatabase_blocks']],
				'mail_settings' => ['url' => ['controller' => 'multidatabase_mail_settings']],
				'role_permissions' => ['url' => ['controller' => 'multidatabase_block_role_permissions']],
				'content_imports' => [
					'url' => ['controller' => 'multidatabase_content_imports'],
					'label' => ['multidatabases', 'Import contents']
				],
				'content_exports' => [
					'url' => ['controller' => 'multidatabase_content_exports'],
					'label' => ['multidatabases', 'Export contents']
				],
			],
		],
	];

/**
 * edit (Export)
 *
 * @return void
 */
	public function edit() {
		// CSVエクスポート
		if (Hash::check($this->request->query, 'save')) {
			return $this->__export($this->request->query['pass']);
		} else {
		}
	}

/**
 * CSV Export
 *
 * @param string $pass ZIPパスワード
 * @return void
 */
	private function __export($pass = '') {
		$this->_prepare();

		$permissions = $this->Workflow->getBlockRolePermissions(
			[
				'content_creatable',
				'content_publishable',
				'content_comment_creatable',
				'content_comment_publishable',
			]
		);
		$this->set('roles', $permissions['Roles']);

		set_time_limit(1800);

		App::uses('CsvFileWriter', 'Files.Utility');
		$csvWriter = new CsvFileWriter();

		$conditions = [
			'is_active' => true,
			'is_latest' => true
		];

		$contents = $this->MultidatabaseContent->getMultidatabaseContents($conditions);

		foreach ($this->_metadata as $metadata) {
			$metadataTitles['value' . $metadata['col_no']] = $metadata['name'];
		}

		$cnt = 1;
		foreach ($contents as $content) {
			foreach ($metadataTitles as $key => $metadataTitle) {
				if ($cnt == 1) {
					$tmpHeader[$key] = $metadataTitle;
				}
				$tmp[$key] = $content['MultidatabaseContent'][$key];
			}

			if ($cnt == 1) {
				$header = $tmpHeader;
				$csvWriter->add($header);
			}

			$csvWriter->add($tmp);
			$cnt++;
		}
		$csvWriter->close();

		if (! $csvWriter) {
			$this->NetCommons->handleValidationError();
			return;
		}

		return $csvWriter->zipDownload(
			'export_multidatabases.zip', 'export_multidatabases.csv', $pass
		);
	}
}
