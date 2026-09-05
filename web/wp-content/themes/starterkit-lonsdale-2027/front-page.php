<?php
get_header();
common("header-nav");
?>

<main>
    <?php hero('homepage'); ?>

    <?php get_template_part('template-parts/utils/dispatch-strates'); ?>

    <?php  strate('snap'); ?>
    <?php strate('anchor_nav'); ?>
    <?php  strate('lottie'); ?>
</main>


<?php
common('prefooter');
get_footer();
