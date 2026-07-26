<?php
/*
Template Name: Custom Grip Form for iFrame
*/

get_header();

?>

<style>
header#masthead {
    display: none;
}

.iframe-form-content {
    padding: 25px 0 0;
}

.container, .container-fluid, .container-lg, .container-md, .container-sm, .container-xl, .container-xxl {
    --bs-gutter-x: 0;
    --bs-gutter-y: 0;
    width: 100%;
    padding-right: calc(var(--bs-gutter-x)* .5);
    padding-left: calc(var(--bs-gutter-x)* .5);
    margin-right: auto;
    margin-left: auto;
}

footer#colophon {
    display: none;
}

img {
    max-width: 100%;
    height: auto;
}

@media (max-width: 768px) {
    .iframe-form-content {
        padding: 15px 0 0;
    }
}

</style>

<div class="iframe-form-content">
    <?php while (have_posts()) : the_post(); ?>
        <div class="container">
            <?php
            if ( class_exists( 'TTCG_Intake' ) ) {
                TTCG_Intake::render_form();
            } else {
                the_content();
            }
            ?>
        </div>
        <?php get_template_part('template-parts/content', 'flexible'); ?>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
