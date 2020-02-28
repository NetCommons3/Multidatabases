<?php
/**
 * MultidatabasesContents view view_content_group_c1 view element
 * 汎用データベース コンテンツ一覧・詳細表示 1段レイアウト view element
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @author Kazunori Sakamoto <exkazuu@willbooster.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

echo $this->NetCommonsHtml->script('/multidatabases/js/multidatabase_files.js');
?>

<div class="col-xs-12">
	<table class="table table-bordered">
		<?php foreach ($gMetadatas as $key => $metadata): ?>
			<tr>
				<th style="width:<?php echo $headerMaxLength; ?>rem;">
					<?php if ($metadata['is_visible_field_name'] === 1): ?>
						<?php echo $metadata['name']; ?>
					<?php endif; ?>
				</th>

				<?php if ($metadata['type'] == 'link'): ?>
				<td class="break-word">
				<?php else: ?>
				<td>
				<?php endif; ?>
					<?php echo $this->MultidatabaseContentViewElement->renderViewElement($gContents, $metadata); ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
</div>
