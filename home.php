<?php get_header(); ?>

<div id="content" class="clearfix">
    <div id="contentInner">
        <main>
            <article>
                <?php get_template_part('itiran');?>
                <?php if (function_exists("pagination")) {
                  pagination($wp_query->max_num_pages);
              } ?>
            </article>
        </main>
    </div>
<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>
