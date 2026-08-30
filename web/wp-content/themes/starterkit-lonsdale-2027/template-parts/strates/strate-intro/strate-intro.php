<?php
$args = get_template_args();

?>

<section <?= options("strate strate-intro", $args) ?>>
    <div class="strate-content">

        <?php component('text', [
            'text' => $args['text'],
            'classes' => 'rte strate-text',
        ]) ?>

    </div>
</section>