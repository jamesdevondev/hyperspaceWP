<!DOCTYPE HTML>
<?php

$template_dir = get_template_directory_uri();
$tag_line = get_bloginfo('description');

?>
<html>
<head>
    <title><?php echo $tag_line; ?></title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!--[if lte IE 8]><script src="<?php echo $template_dir; ?>/assets/js/ie/html5shiv.js"></script><![endif]-->
    <?php wp_head(); ?>
<!--    <link rel="stylesheet" href="--><?php //echo $template_dir; ?><!--/assets/css/main.css" />-->
    <!--[if lte IE 9]><link rel="stylesheet" href="<?php echo $template_dir; ?>/assets/css/ie9.css" /><![endif]-->
    <!--[if lte IE 8]><link rel="stylesheet" href="<?php echo $template_dir; ?>/assets/css/ie8.css" /><![endif]-->
</head>
<body <?php body_class(); ?>>