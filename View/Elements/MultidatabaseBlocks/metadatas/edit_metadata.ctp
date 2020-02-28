<?php
/**
 * MultidatabasesBlocks edit view element
 * 汎用データベース ブロック設定 編集用フォーム view element
 *
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>

<?php
echo $this->NetCommonsHtml->script([
	'/multidatabases/js/edit_multidatabase_metadatas.js',
]);
?>

<?php echo $this->NetCommonsForm->unlockField('MultidatabaseMetadata'); ?>

<div ng-controller="MultidatabaseMetadata"
	 ng-init="initialize(<?php
		echo h(json_encode([
			'multidatabaseMetadata' => $metadatas,
			'multidatabaseMetadataDefault' => $metadataDefault
		]));
	?>)">
	<div class="panel panel-default">
		<div class="panel-heading clearfix">
			<div class="pull-left">
				<strong><?php echo __d('multidatabases', 'Section 1'); ?></strong>
			</div>
		</div>
		<div class="panel-body">
			<?php echo $this->MultidatabaseMetadataSetting->renderGroup(0, 1); ?>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading clearfix">
			<div class="pull-left">
				<strong><?php echo __d('multidatabases', 'Section 2'); ?></strong>
			</div>
		</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-xs-12 col-sm-6">
					<?php echo $this->MultidatabaseMetadataSetting->renderGroup(1, 2); ?>
				</div>
				<div class="col-xs-12 col-sm-6">
					<?php echo $this->MultidatabaseMetadataSetting->renderGroup(2, 2); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading clearfix">
			<div class="pull-left">
				<strong><?php echo __d('multidatabases', 'Section 3'); ?></strong>
			</div>
		</div>
		<div class="panel-body">
			<?php echo $this->MultidatabaseMetadataSetting->renderGroup(3, 1); ?>
		</div>
	</div>
</div>
