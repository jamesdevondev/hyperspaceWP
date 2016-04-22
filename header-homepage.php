<?php
  get_header();

  $section_groups = HyperspaceWP::get_ordered_home_page_section_groups();
?>
<section id="sidebar">
    <div class="inner">
        <?php echo HyperspaceWP::create_nav_list($section_groups, true); ?>
    </div>
</section>

<div id="wrapper"><?php // start Wrapper

    $section = $section_groups[0]['post'];
    $content = HyperspaceWP::get_excerpt_or_content( $section );
    echo HyperspaceWP::apply_intro_section_template($section->post_name, 'style1 intro',  $content);

?>



