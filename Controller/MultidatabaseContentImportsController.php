<?php
/**
 * MultidatabaseContentImportsController Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft, Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MultidatabasesAppController', 'Multidatabases.Controller');

/**
 * MultidatabaseContentImportsController Controller
 *
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @package NetCommons\Multidatabases\Controller
 */
class MultidatabaseContentImportsController extends MultidatabasesAppController {

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
				'edit,download_import_format' => 'block_permission_editable',
			],
		],
		'Files.Download',
		'Files.FileUpload',
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
 * edit (Import)
 *
 * @return void
 */
	public function edit() {
		// CSVインポート
		if ($this->request->is('post')) {
			$this->__import();
		}
	}

/**
 * CSV Import
 *
 * @return void
 */
	private function __import() {
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

		$dat = $this->__getImportContents();

		if (! $dat) {
			$this->NetCommons->setFlashNotification(
				__d('multidatabases', 'Invalid data.'), ['class' => 'danger']
			);
		}

		$result = $this->__saveImportContents($dat);
		if ($result['result']) {
			$this->NetCommons->setFlashNotification(
				__d('multidatabases', 'Successfully saved.'), ['class' => 'success']
			);
		} else {
			$this->NetCommons->setFlashNotification(
				$result['errmsg'], ['class' => 'danger']
			);
		}
	}

/**
 * Check Import Contents Format
 *
 * @param array $csvHeader CSVヘッダ配列
 * @return mixed
 */
	private function __chkImportContentsFormat($csvHeader = []) {
		foreach ($this->_metadata as $key => $val) {
			if (
				! isset($csvHeader[$key]) ||
				$csvHeader[$key] !== $val['name']
			) {
				return false;
			}
		}

		return true;
	}

/**
 * Load CSV and Get Import Contents
 *
 * @return array|bool
 */
	private function __getImportContents() {
		// ファイル有無チェック
		if (empty($_FILES['data']['name']['import_csv'])) {
			return false;
		}

		App::uses('CsvFileReader', 'Files.Utility');
		$file = $this->FileUpload->getTemporaryUploadFile('import_csv');
		$reader = new CsvFileReader($file);

		$cnt = 0;
		foreach ($reader as $rowVals) {
			if ($cnt == 0) {
				$dat['header'] = $rowVals;
				$cnt++;
				continue;
			}
			$dat['data'][] = $rowVals;
		}

		if (! $this->__chkImportContentsFormat($dat['header'])) {
			$this->NetCommons->setFlashNotification(
				__d('multidatabases', 'Incorrect file format.'), ['class' => 'danger']
			);
			return false;
		}

		return $dat;
	}

/**
 * Save Import Contents
 *
 * @param array $importDat インポートデータ配列
 * @return array|bool
 * @throws InternalErrorException
 */
	private function __saveImportContents($importDat) {
		// 共通データ
		$commonDat['MultidatabaseContent']['key'] = '';
		$commonDat['MultidatabaseContent']['multidatabase_id'] =
			$this->_multidatabase['Multidatabase']['id'];
		$commonDat['MultidatabaseContent']['multidatabase_key'] =
			$this->_setting['MultidatabaseSetting']['multidatabase_key'];
		$commonDat['MultidatabaseContent']['block_id'] = Current::read('Block.id');
		$commonDat['MultidatabaseContent']['language_id'] = Current::read('Language.id');
		// WorkflowBehavior.phpで厳密型比較しているため、stringでないとactiveとみなされない
		// https://github.com/NetCommons3/Workflow/blob/d4081290421b1f8e8b8659dc44fb36345a57af1f/Model/Behavior/WorkflowBehavior.php#L129
		//$commonDat['MultidatabaseContent']['status'] = 1;
		$commonDat['MultidatabaseContent']['status'] = '1';

		$commonDat['Frame']['id'] = Current::read('Frame.id');
		$commonDat['Block']['id'] = Current::read('Block.id');
		$commonDat['Block']['key'] = Current::read('Block.key');
		$commonDat['Multidatabase']['id'] = $this->_multidatabase['Multidatabase']['id'];
		$commonDat['Multidatabase']['key'] = $this->_multidatabase['Multidatabase']['key'];

		$commonDat['WorkflowComment']['comment'] = '';
		//$commonDat['_NetCommonsTime']['user_timezone'] = NetCommonsTime::getUserTimezone();
		$commonDat['_NetCommonsTime']['convert_fields'] = '';

		if (empty($importDat['data'])) {
			return [
				'result' => false,
				'errmsg' => __d('multidatabases', 'Invalid data.'), ['class' => 'danger']
			];
		}

		foreach ($importDat['data'] as $content) {
			$tmp = $commonDat;
			foreach ($this->_metadata as $key => $metadata) {
				if (
					isset($importDat['header'][$key]) &&
					isset($content[$key]) &&
					$importDat['header'][$key] === $metadata['name']
				) {
					$tmp['MultidatabaseContent']['value' . $metadata['col_no']] = $content[$key];
				} else {
					return [
						'result' => false,
						'errmsg' => __d('multidatabases', 'Invalid data.'), ['class' => 'danger']
					];
				}
			}
			$result[] = $tmp;
		}

		foreach ($result as $val) {
			if (! $this->MultidatabaseContent->saveContentForImport($val)) {
				return [
					'result' => false,
					'errmsg' => __d('multidatabases', 'Invalid data.'), ['class' => 'danger']
				];
			}
		}

		return [
			'result' => true,
			'errmsg' => ''
		];
	}

/**
 * Download CSV Format
 *
 * @return bool
 * @throws InternalErrorException
 */
	public function download_import_format() {
		$this->_prepare();

		set_time_limit(1800);

		App::uses('CsvFileWriter', 'Files.Utility');
		$csvWriter = new CsvFileWriter();

		foreach ($this->_metadata as $metadata) {
			$header[] = $metadata['name'];
		}
		$csvWriter->add($header);
		$csvWriter->close();

		if (! $csvWriter) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		return $csvWriter->Download('export_multidatabases_format.csv');
	}

}
