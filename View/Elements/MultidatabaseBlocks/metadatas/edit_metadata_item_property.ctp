<?php
/**
 * MultidatabasesBlocks metadatas edit_metadata_item_property view element
 * 汎用データベース ブロック設定 メタデータ編集フォーム アイテム定義 view element
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Tomoyuki OHNO (Ricksoft Co., Ltd.) <ohno.tomoyuki@ricksoft.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>

<input name="data[MultidatabaseMetadata][<?php echo $gPos; ?>][{{$index}}][id]" type="text" class="hidden"
       value="{{g<?php echo $gPos; ?>.id}}">
<input name="data[MultidatabaseMetadata][<?php echo $gPos; ?>][{{$index}}][key]" type="text" class="hidden"
       value="{{g<?php echo $gPos; ?>.key}}">
<input name="data[MultidatabaseMetadata][<?php echo $gPos; ?>][{{$index}}][rank]" type="text" class="hidden"
       value="{{g<?php echo $gPos; ?>.rank}}">
<input name="data[MultidatabaseMetadata][<?php echo $gPos; ?>][{{$index}}][position]" type="text" class="hidden"
       value="{{g<?php echo $gPos; ?>.position}}">
<input name="data[MultidatabaseMetadata][<?php echo $gPos; ?>][{{$index}}][col_no]" type="text" class="hidden"
       value="{{g<?php echo $gPos; ?>.col_no}}">
<?php // 項目名 ?>
<div class="row form-group">
	<div class="col-xs-12">
		<label for="multidatabaseMetadataSettingEditName<?php echo $gPos; ?>-{{$index}}" class="control-label">
			<?php echo __d('multidatabases', 'Field name'); ?>
			<strong class="text-danger h4">*</strong>
		</label>
		<input type="text"
		       class="form-control"
		       id="multidatabaseMetadataSettingEditName<?php echo $gPos; ?>-{{$index}}"
		       name="data[MultidatabaseMetadata][<?php echo $gPos; ?>][{{$index}}][name]"
		       ng-model="metadataGroup<?php echo $gPos; ?>[$index]['name']">
	</div>
</div>
<?php // 属性 ?>
<div class="row form-group" ng-if="<?php echo "g${gPos}.is_title != 1"; ?>">
	<div class="col-xs-12">
		<label>
			<?php echo __d('multidatabases', 'Field type'); ?>
			<strong class="text-danger h4">*</strong>
		</label>
		<select name="data[MultidatabaseMetadata][<?php echo $gPos; ?>][{{$index}}][type]"
		        id="multidatabaseMetadataSettingEditType<?php echo $gPos; ?>-{{$index}}"
		        class="form-control"
		        ng-model="metadataGroup<?php echo $gPos; ?>[$index]['type']">
			<?php foreach ($this->MultidatabaseMetadataSetting->fieldTypeList() as $key => $fieldName): ?>
				<option value="<?php echo $key; ?>">
					<?php echo $fieldName; ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
</div>
<?php // 選択肢 ?>
<div ng-if="<?php echo "g${gPos}.type == 'select' || g${gPos}.type == 'checkbox'"; ?>">
	<?php echo $this->MultidatabaseMetadataSetting->renderGroupItemPropertySelections($gPos); ?>
</div>
<div class="row form-group">
	<div class="col-xs-12">
		<?php // 入力必須項目にする ?>
		<div class="checkbox" ng-if="<?php echo "g${gPos}.is_title != 1"; ?>">
			<label class="control-label" for="MultidatabaseMetadataSettingEditIsRequire<?php echo $gPos; ?>-{{$index}}">
				<input type="checkbox"
				       name="data[MultidatabaseMetadata][<?php echo $gPos; ?>][{{$index}}][is_require]"
				       id="MultidatabaseMetadataSettingEditIsRequire<?php echo $gPos; ?>-{{$index}}"
				       class="MultidatabaseMetadataSettingEditIsRequire"
				       ng-true-value="1"
				       ng-false-value=""
				       ng-model="metadataGroup<?php echo $gPos; ?>[$index]['is_require']">
				<?php echo __d('multidatabases', 'Is require.'); ?>
			</label>
		</div>
		<?php // 一覧画面に表示する項目 ?>
		<div class="checkbox">
			<label class="control-label"
			       for="MultidatabaseMetadataSettingEditIsVisibleList<?php echo $gPos; ?>-{{$index}}">
				<input type="checkbox"
				       id="MultidatabaseMetadataSettingEditIsVisibleList<?php echo $gPos; ?>-{{$index}}"
				       class="MultidatabaseMetadataSettingEditIsVisibleList"
				       name="data[MultidatabaseMetadata][<?php echo $gPos; ?>][{{$index}}][is_visible_list]"
				       ng-true-value="1"
				       ng-false-value=""
				       ng-model="metadataGroup<?php echo $gPos; ?>[$index]['is_visible_list']">
				<?php echo __d('multidatabases', 'Enable to display on list.'); ?>
			</label>
		</div>
		<?php // ソートできる ?>
		<div class="checkbox" style="margin-left:20px;" ng-if="
				g<?php echo $gPos; ?>.type != 'file' &&
				g<?php echo $gPos; ?>.type != 'image' &&
				g<?php echo $gPos; ?>.type != 'textarea' &&
				g<?php echo $gPos; ?>.type != 'wysiwyg' &&
				g<?php echo $gPos; ?>.type != 'checkbox' &&
				g<?php echo $gPos; ?>.is_visible_list == '1'
		">
			<label class="control-label"
			       for="MultidatabaseMetadataSettingEditIsSortable<?php echo $gPos; ?>-{{$index}}">
				<input type="checkbox"
				       name="data[MultidatabaseMetadata][<?php echo $gPos; ?>][{{$index}}][is_sortable]"
				       id="MultidatabaseMetadataSettingEditIsSortable<?php echo $gPos; ?>-{{$index}}"
				       class="MultidatabaseMetadataSettingEditIsSortable"
				       ng-true-value="1"
				       ng-false-value=""
				       ng-model="metadataGroup<?php echo $gPos; ?>[$index]['is_sortable']">
				<?php echo __d('multidatabases', 'Is sortable.'); ?>
			</label>
		</div>
		<?php // 詳細画面に表示する項目 ?>
		<div class="checkbox">
			<label class="control-label"
			       for="MultidatabaseMetadataSettingEditIsVisibleDetail<?php echo $gPos; ?>-{{$index}}">
				<input type="checkbox"
				       name="data[MultidatabaseMetadata][<?php echo $gPos; ?>][{{$index}}][is_visible_detail]"
				       id="MultidatabaseMetadataSettingEditIsVisibleDetail<?php echo $gPos; ?>-{{$index}}"
				       class="MultidatabaseMetadataSettingEditIsVisibleDetail"
				       ng-true-value="1"
				       ng-false-value=""
				       ng-model="metadataGroup<?php echo $gPos; ?>[$index]['is_visible_detail']">
				<?php echo __d('multidatabases', 'Enable to display on detail.'); ?>
			</label>
		</div>
		<?php // 項目名を表示する ?>
		<div class="checkbox">
			<label class="control-label"
			       for="MultidatabaseMetadataSettingEditIsVisibleFieldName<?php echo $gPos; ?>-{{$index}}">
				<input type="checkbox"
				       name="data[MultidatabaseMetadata][<?php echo $gPos; ?>][{{$index}}][is_visible_field_name]"
				       id="MultidatabaseMetadataSettingEditIsVisibleFieldName<?php echo $gPos; ?>-{{$index}}"
				       class="MultidatabaseMetadataSettingEditIsVisibleFieldName"
				       ng-true-value="1"
				       ng-false-value=""
				       ng-model="metadataGroup<?php echo $gPos; ?>[$index]['is_visible_field_name']">
				<?php echo __d('multidatabases', 'Enable to display field name.'); ?>
			</label>
		</div>
		<?php // 検索の対象に含める ?>
		<div class="checkbox" ng-if="
				g<?php echo $gPos; ?>.type != 'file' &&
				g<?php echo $gPos; ?>.type != 'image' &&
				g<?php echo $gPos; ?>.type != 'autonumber' &&
				g<?php echo $gPos; ?>.type != 'date' &&
				g<?php echo $gPos; ?>.type != 'created' &&
				g<?php echo $gPos; ?>.type != 'updated'
		">
			<label class="control-label"
			       for="MultidatabaseMetadataSettingEditIsSearchable<?php echo $gPos; ?>-{{$index}}">
				<input type="checkbox"
				       name="data[MultidatabaseMetadata][<?php echo $gPos; ?>][{{$index}}][is_searchable]"
				       id="MultidatabaseMetadataSettingEditIsSearchable<?php echo $gPos; ?>-{{$index}}"
				       class="MultidatabaseMetadataSettingEditIsSearchable"
				       ng-true-value="1"
				       ng-false-value=""
				       ng-model="metadataGroup<?php echo $gPos; ?>[$index]['is_searchable']">
				<?php echo __d('multidatabases', 'Is searchable.'); ?>
			</label>
		</div>
		<?php // ファイルのダウンロード回数を表示する ?>
		<div class="checkbox" ng-if="g<?php echo $gPos; ?>.type == 'file'">
			<label class="control-label"
			       for="MultidatabaseMetadataSettingEditIsVisibleFileDlCounter<?php echo $gPos; ?>-{{$index}}">
				<input type="checkbox"
				       id="MultidatabaseMetadataSettingEditIsVisibleFileDlCounter<?php echo $gPos; ?>-{{$index}}"
				       class="MultidatabaseMetadataSettingEditIsVisibleFileDlCounter"
				       name="data[MultidatabaseMetadata][<?php echo $gPos; ?>][{{$index}}][is_visible_file_dl_counter]"
				       ng-true-value="1"
				       ng-false-value=""
				       ng-model="metadataGroup<?php echo $gPos; ?>[$index]['is_visible_file_dl_counter']">
				<?php echo __d('multidatabases', 'Enable to display file download counter.'); ?>
			</label>
		</div>
	</div>
</div>

