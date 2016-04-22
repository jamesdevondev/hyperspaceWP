<?php $template_dir = get_template_directory_uri(); ?>
</div>

<!-- Footer -->
<footer id="footer" class="wrapper style1-alt">
    <div class="inner">
<!--        <ul class="menu">-->
<!--            <li>&copy; Untitled. All rights reserved.</li><li>Design: <a href="http://html5up.net">HTML5 UP</a></li>-->
<!--        </ul>-->
        <ul class="menu"><li>
                <?php dynamic_sidebar(__( 'Footer widget', HyperspaceWP::type_name )); ?>
        <li></ul>
    </div>
</footer>

<!-- Scripts -->
<!--[if lte IE 8]><script src="<?php echo $template_dir; ?>/assets/js/ie/respond.min.js"></script><![endif]-->
<?php wp_footer(); ?>

</body>
</html>