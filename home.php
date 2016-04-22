<?php
/*
Template Name: WP Hyperspace Home page
*/

get_header('homepage');

$section_groups = array_slice(HyperspaceWP::get_ordered_home_page_section_groups(), 1);

foreach($section_groups as $section_group) {
    echo HyperspaceWP::get_home_page_section_html( $section_group['post'], $section_group['post_meta'] );
}

get_footer('homepage');