<?php echo $this->Html->useTag('fieldsetstart', __d('quick_optimizer', 'Compression Options')); ?>
    <?php echo $this->Form->input('Module.settings.compress_css', array('type' => 'checkbox', 'label' => __d('quick_optimizer', 'Compress Style Sheets'))); ?>
    <?php echo $this->Form->input('Module.settings.compress_js', array('type' => 'checkbox', 'label' => __d('quick_optimizer', 'Compress Javascript'))); ?>
    <?php echo $this->Form->input('Module.settings.enable_gzip', array('type' => 'checkbox', 'label' => __d('quick_optimizer', 'Enable GZIP'))); ?>
<?php echo $this->Html->useTag('fieldsetend'); ?>