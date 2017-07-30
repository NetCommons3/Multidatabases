<?php
/**
 * MultidatabasesContents detail view
 * 汎用データベース 詳細表示 view
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

echo $this->Html->script(
	'/AuthorizationKeys/js/authorization_keys.js',
	array(
		'plugin' => false,
		'once' => true,
		'inline' => false
	)
);

echo $this->NetCommonsHtml->css([
	'/multidatabases/css/style.css',
	'/likes/css/style.css',
]);
?>

<article class="multidatabase-contents">
	<header class="clearfix">
		<div class="pull-left">
			<?php echo $this->LinkButton->toList(); ?>
		</div>
		<div class="pull-right">
			<?php echo $this->element('MultidatabaseContents/view/view_content_edit_link', ['status' => $multidatabaseContent['MultidatabaseContent']['status']]); ?>
		</div>

	</header>
	<?php echo $this->NetCommonsHtml->blockTitle($multidatabase['Multidatabase']['name']); ?>
	<article>
		<?php echo $this->MultidatabaseContentView->renderContentsDetail(); ?>
	</article>
</article>
