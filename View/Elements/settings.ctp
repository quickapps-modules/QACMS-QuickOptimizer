<?php echo $this->Html->useTag('fieldsetstart', __d('quick_optimizer', 'Compression Options')); ?>
    <?php echo $this->Form->input('Module.settings.compress_css', array('type' => 'checkbox', 'label' => __d('quick_optimizer', 'Compress Style Sheets'))); ?>
    <?php echo $this->Form->input('Module.settings.compress_js', array('type' => 'checkbox', 'label' => __d('quick_optimizer', 'Compress Javascript'))); ?>
    <?php echo $this->Form->input('Module.settings.enable_gzip', array('type' => 'checkbox', 'label' => __d('quick_optimizer', 'Enable GZIP'))); ?>

	<?php
		echo $this->Form->input('Module.settings.cache_duration',
			array(
				'type' => 'select',
				'label' => __d('quick_optimizer', 'Cache compressed files for'),
				'options' => array(
					'+1 hour' => __d('quick_optimizer', '1 hour'),
					'+6 hours' => __d('quick_optimizer', '6 hours'),
					'+12 hours' => __d('quick_optimizer', '12 hours'),
					'+1 day' => __d('quick_optimizer', '1 day'),
					'+4 days' => __d('quick_optimizer', '4 days'),
					'+7 days' => __d('quick_optimizer', '7 days'),
					'+1 month' => __d('quick_optimizer', '1 month'),
					'+6 months' => __d('quick_optimizer', '6 months'),
					'+1 year' => __d('quick_optimizer', '1 year')
				)
			)
		);
	?>
<?php echo $this->Html->useTag('fieldsetend'); ?>