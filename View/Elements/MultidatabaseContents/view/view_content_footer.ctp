<div class="clearfix Multidatabases_content_reaction">
	<div class="pull-left">
		<div class="pull-left">
			<?php if (isset($viewMode) && ($viewMode === 'view')) : ?>
				<span class="multidatabases__content-comment-count">
			<?php echo $this->ContentComment->count($content); ?>
		</span>
			<?php endif ?>
		</div>

		<div class="pull-left">
			<?php echo $this->Like->buttons('MultidatabaseContent', $multidatabaseSetting, $content); ?>
		</div>
	</div>
</div>
