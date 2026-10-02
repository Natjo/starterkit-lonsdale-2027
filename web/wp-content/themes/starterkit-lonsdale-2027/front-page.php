<?php
get_header();
common("header-nav-complex");
?>

<main>
    <?php hero('homepage'); ?>

    <?php get_template_part('template-parts/utils/dispatch-strates'); ?>
</main>


<?php
common('prefooter');
get_footer();
