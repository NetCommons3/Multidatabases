<?php
/**
 * MultidatabasesContents search form view
 * 汎用データベース コンテンツ検索フォーム view
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

echo $this->NetCommonsHtml->css([
	'/multidatabases/css/style.css',
]);
?>

<div class="multidatabase-contents-search form">
	<article>
		<?php echo $this->NetCommonsHtml->blockTitle($multidatabase['Multidatabase']['name']); ?>
		<?php echo $this->NetCommonsForm->create('MultidatabaseContentSearch', [
			'type' => 'get',
		]);?>
		<?php echo $this->NetCommonsForm->hidden('frame_id', ['value' => Current::read('Frame.id')]); ?>
		<div class="panel panel-default">
			<div class="panel-body">
				<?php // キーワード ?>
				<div>
					<?php
						$options = [
							'label' => __d('multidatabases', 'Keywords')
						];
						echo $this->NetCommonsForm->input('keywords', $options);
					?>
				</div>
				<?php // 検索の種類 ?>
				<div>
					<?php
						$options = [
							'type' => 'select',
							'options' => [
								'and' => __d('multidatabases', 'And search'),
								'or' => __d('multidatabases', 'Or search'),
								'phrase' => __d('multidatabases', 'Phrase search')
							],
							'label' => __d('multidatabases', 'Search type')
						];
						echo $this->NetCommonsForm->input('type', $options);
					?>
				</div>
				<?php // 作成日時 ?>
				<div>
					<div class="form-group">
						<label for="create_date_range" class="control-label">
							<?php echo __d('multidatabases', 'Create date'); ?>
						</label>
						<div class="form-inline">
							<div class="input-group">
								<?php echo $this->NetCommonsForm->input('start_dt', array(
									'type' => 'datetime',
									'label' => false,
									'class' => 'form-control',
									'placeholder' => 'yyyy-mm-dd hh:nn',
									'div' => false,
									'error' => false,
									'default' => false,
								)); ?>
								<span class="input-group-addon">
									<span class="glyphicon glyphicon-minus"></span>
								</span>
								<?php echo $this->NetCommonsForm->input('end_dt', array(
									'type' => 'datetime',
									'label' => false,
									'class' => 'form-control',
									'placeholder' => 'yyyy-mm-dd hh:nn',
									'div' => false,
									'error' => false,
									'default' => false,
								)); ?>
							</div>
						</div>
					</div>
				</div>
				<?php // 選択項目 ?>
				<?php echo $this->MultidatabaseContentView->dropDownToggleSelect($multidatabaseMetadata, 'search'); ?>
				<?php // 状態 ?>
				<div>
					<?php
					$options = [
						'type' => 'select',
						'options' => [
							'0' => __d('multidatabases', 'All'),
							'pub' => __d('multidatabases', 'Published'),
							'unpub' => __d('multidatabases', 'Unpublished')
						],
						'label' => __d('multidatabases', 'Status')
					];
					echo $this->NetCommonsForm->input('status', $options);
					?>
				</div>
				<?php // 表示順 ?>
				<?php echo $this->MultidatabaseContentView->dropDownToggleSort($multidatabaseMetadata, 'search'); ?>
			</div>
			<div class="panel-footer text-center">
				<?php echo $this->Button->cancel(__d('net_commons', 'Cancel'), $cancelUrl, []); ?>
				<button class="btn btn-primary" type="submit">
					<span class="glyphicon glyphicon-search" aria-hidden="true"></span>
					<span><?php echo __d('net_commons', 'Search'); ?></span>
				</button>
			</div>
		</div>
		<?php echo $this->NetCommonsForm->end() ?>
	</article>
</div>
