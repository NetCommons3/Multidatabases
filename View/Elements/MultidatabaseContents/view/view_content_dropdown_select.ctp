<?php
/**
 * MultidatabasesContents view view_content_edit_link view drop-down element
 * 汎用データベース ドロップダウンメニュー view element
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>

<span class="btn-group">
	<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
		<?php echo $dropdownItems[$currentItemKey]; ?>
		<span class="caret"></span>
	</button>
	<ul class="dropdown-menu" role="menu">
		<?php foreach ($dropdownItems as $itemKey => $label) : ?>
			<li>
				<?php if ($itemKey === 0): ?>
					<?php echo $this->Paginator->link($label, array_merge($url, [$dropdownCol => 0])); ?>
					<li class="divider"></li>
				<?php else: ?>
					<?php echo $this->Paginator->link($label, array_merge($url, [$dropdownCol => $itemKey])); ?>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</span>
