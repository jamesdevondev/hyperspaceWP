<?php
get_header();

$section_groups = HyperspaceWP::get_ordered_home_page_section_groups();

$uri = $_SERVER['REQUEST_URI'] ;
$decoded_uri = urldecode($uri);
$escaped_html_uri = esc_html($decoded_uri);

if($uri !== $decoded_uri ) $uri_is_encoded = true;
?>

<!-- Header -->
<header id="header">
    <a href="/" class="title"><?php echo get_bloginfo ('name');?></a>
    <?php echo HyperspaceWP::create_nav_list($section_groups, false); ?>
</header>

<!-- Wrapper -->
<div id="wrapper">

    <!-- Main -->
    <section id="main" class="wrapper">
        <div class="inner">
            <h1 class="major">404</h1>
            <p>The requested resource
                <code>
                    <?php
                    echo $escaped_html_uri;
                    ?>
                </code>
                does not exist. <br/>Please use one of these links:</p>
            <?php echo HyperspaceWP::create_nav_list($section_groups, false); ?>
        </div>
    </section>

</div>
<?php get_footer(); ?>