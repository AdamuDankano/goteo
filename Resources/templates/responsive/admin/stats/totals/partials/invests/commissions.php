<?php

$target = $this->target ?: 'commissions';
$slot1 = $this->interval ?: 'today';
$slot2 = $slot1 === 'custom' ? '' : 'yesterday';
$slot3 = $slot4 = '';
if(in_array($this->interval, ['week', 'month', 'year'])) {
    $slot2 = 'last_' . $this->interval;
    $slot3 = 'last_' . $this->interval . '_complete';
    if($this->interval !== 'year')
        $slot4 = 'last_year_' . $this->interval;
}
$id = $this->id ?: 'global';
$method = $this->method ?: $this->text('regular-all');
$query = $this->raw('query');
?>


<div class="d3-chart loading discrete-values" data-source="/api/charts/totals/invests/<?= $target ?>/<?= $id ?>/<?= $slot1 ?>?<?= $query ?>" data-interval="40" data-flash-time="15" data-delay="50">

    <h4><?= $this->text("admin-stats-$target-method") ?>: <?= $method ?></h4>

    <ul class="row list-unstyled">
        <li class="col-xs-2 col-xxs-4" data-property="<?= "$target.$id.$slot1" ?>.charged_formatted" data-title="<?= $this->text('admin-' . $target) . ': ' . $this->text('admin-' . $slot1) ?>"></li>
      <?php if($slot2): ?>
        <li class="col-xs-2 col-xxs-4" data-property="<?= "$target.$id.$slot2" ?>.charged_formatted" data-title="<?= $this->text('admin-' . $target) . ': ' . $this->text('admin-' . $slot2) ?>"></li>
        <li class="col-xs-2 col-xxs-4" data-property="<?= "$target.$id.$slot1" ?>.charged_<?= $slot2 ?>_gain_formatted" data-tooltip="<?= "$target.$id.$slot1" ?>.charged_<?= $slot2 ?>_diff_formatted" data-title="<?= $this->text('admin-' . $target) . ': ' .$this->text('admin-diff') ?>"></li>
      <?php endif ?>
      <?php if($slot4): ?>
            <li class="col-xs-2 col-xxs-4" data-property="<?= "$target.$id.$slot4" ?>.charged_formatted" data-title="<?= $this->text('admin-' . $target) . ': ' . $this->text('admin-' . $slot4) ?>"></li>
            <li class="col-xs-2 col-xxs-4" data-property="<?= "$target.$id.$slot1" ?>.charged_<?= $slot4 ?>_gain_formatted" data-tooltip="<?= "$target.$id.$slot1" ?>.charged_<?= $slot4 ?>_diff_formatted" data-title="<?= $this->text('admin-' . $target) . ': ' .$this->text('admin-diff') ?>"></li>
      <?php endif ?>

      <?php if($slot3): ?>
            <li class="col-xs-2 col-xxs-4<?= $slot4 ? '' : ' col-xs-offset-4'?>" data-property="<?= "$target.$id.$slot3" ?>.charged_formatted" data-title="<?= $this->text('admin-' . $target) . ': ' . $this->text('admin-' . $slot3) ?>"></li>
      <?php endif ?>

        <li class="col-xs-2 col-xxs-4" data-property="<?= "$target.$id.$slot1" ?>.lost_formatted" data-title="<?= $this->text('admin-losts') . ': ' . $this->text('admin-' . $slot1) ?>"></li>
      <?php if($slot2): ?>
        <li class="col-xs-2 col-xxs-4" data-property="<?= "$target.$id.$slot2" ?>.lost_formatted" data-title="<?= $this->text('admin-losts') . ': ' . $this->text('admin-' . $slot2) ?>"></li>
        <li class="col-xs-2 col-xxs-4" data-property="<?= "$target.$id.$slot1" ?>.lost_<?= $slot2 ?>_gain_formatted" data-tooltip="<?= "$target.$id.$slot1" ?>.lost_<?= $slot2 ?>_diff_formatted" data-title="<?= $this->text('admin-losts') . ': ' . $this->text('admin-diff') ?>"></li>
      <?php endif ?>
      <?php if($slot4): ?>
            <li class="col-xs-2 col-xxs-4" data-property="<?= "$target.$id.$slot4" ?>.lost_formatted" data-title="<?= $this->text('admin-losts') . ': ' . $this->text('admin-' . $slot4) ?>"></li>
            <li class="col-xs-2 col-xxs-4" data-property="<?= "$target.$id.$slot1" ?>.lost_<?= $slot4 ?>_gain_formatted" data-tooltip="<?= "$target.$id.$slot1" ?>.lost_<?= $slot4 ?>_diff_formatted" data-title="<?= $this->text('admin-user-fee') . ': ' .$this->text('admin-diff') ?>"></li>
      <?php endif ?>

      <?php if($slot3): ?>
            <li class="col-xs-2 col-xxs-4<?= $slot4 ? '' : ' col-xs-offset-4'?>" data-property="<?= "$target.$id.$slot3" ?>.lost_formatted" data-title="<?= $this->text('admin-losts') . ': ' . $this->text('admin-' . $slot3) ?>"></li>
      <?php endif ?>
    </ul>

</div>
