<?php
if (!defined('APPLICATION_LOADED') || !APPLICATION_LOADED) {
    die('No direct script access.');
}

$this->title = $this->project_name . ' - ' . $this->lang_php['title_activity_stream'];

$stream = $this->getTicketsActivityStream($this->user_id, $this->project_id);
require 'templates/activitystream.php';
?>
<h1><?= $this->lang_php['activity'] ?></h1>
<div class="row" id="home-tickets">
    <div class="col-sm-6">
        <div class="panel panel-info bg-choice right">
            <div class="panel-heading"><?= $this->lang_php['assigned_to_me'] ?></div>
            <div class="panel-body">
                <?php
                if (!empty($stream['mine'])) {
                    activityForeach($stream['mine']);
                    ?>
                    <div id="mine-ajax"></div>
                    <a href="javascript:void(0)" onClick="showMore(<?= $this->user_id ?>, <?= $this->project_id ?>, true)" class="bordered text-center show-more"><?= $this->lang_php['show_more'] ?> <i class="fa fa-chevron-circle-down"></i></a>
                    <?php
                } else {
                    ?>
                    <?= $this->lang_php['no_activity'] ?>
                <?php } ?>
            </div>
        </div>
        <div class="panel panel-info bg-choice left">
            <div class="panel-heading"><?= $this->lang_php['activity_stream'] ?></div>
            <div class="panel-body">
                <?php
                if (!empty($stream['other'])) {
                    activityForeach($stream['other']);
                    ?>
                    <div id="other-ajax"></div>
                    <a href="javascript:void(0)" onClick="showMore(<?= $this->user_id ?>, <?= $this->project_id ?>, false)" class="bordered text-center show-more"><?= $this->lang_php['show_more'] ?> <i class="fa fa-chevron-circle-down"></i></a>
                        <?php
                    } else {
                        ?>
                        <?= $this->lang_php['no_activity'] ?>
                    <?php } ?>
            </div>
        </div>
    </div>
</div>
<script src="<?= base_url('assets/js/ticketsActivity.js') ?>"></script>
