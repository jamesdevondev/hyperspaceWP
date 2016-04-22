<?php

class HyperspaceWP
{
    const type_name  = 'home_page_section';
    const nonce_name = 'home_page_section_nonce';
    // style_classes - defined in init below

    private static function get_setting_names() {
        return array('style', 'vertical_image_position', 'horizontal_image_position');
    }

    private static function get_style_names() {
        return array('style1', 'style2', 'style3', 'style1-alt', 'style2-alt', 'style3-alt');
    }

    private static function get_vertical_image_positions() {
        return array('top', 'center', 'bottom');
    }

    private static function get_horizontal_image_positions() {
        return array('center', 'left', 'right');
    }

    public static function create_fully_qualified_setting_name($key_name) {
        return self::type_name . '_' . $key_name;
    }

    private static function get_fully_qualified_setting_names($keys) {
        $fully_qualified_keys = array();
        foreach($keys as $key) {
            array_push($fully_qualified_keys, self::create_fully_qualified_setting_name( $key ) );
        }
        return $fully_qualified_keys;
    }

    private static function get_vertical_image_position($meta) {
        $name = self::create_fully_qualified_setting_name('vertical_image_position');
        $image_vertical_position   = self::get_meta_from_array_if_set(
            $meta, $name,
            self::get_vertical_image_positions()[0]
        );
        return $image_vertical_position;
    }

    private static function get_horizontal_image_position($meta) {
        $name = self::create_fully_qualified_setting_name('horizontal_image_position');
        $image_horizontal_position   = self::get_meta_from_array_if_set(
            $meta, $name,
            self::get_horizontal_image_positions()[0]
        );
        return $image_horizontal_position;
    }

    public static function register_meta_boxes() {
        add_meta_box(
            'home_page_section_settings',
            'Page Section settings',
            'HyperspaceWP::meta_box_content',
            self::type_name,
            'normal',
            'high',
            null//callback_args
        );
    }

    private static function create_html_select( $id, $values, $selected_value ) {
        $options = '';

        foreach( $values as $value ) {
            $selected = '';
            if( $selected_value === $value ) {
                $selected = "selected='selected'";
            }
            $options .= "<option value='{$value}' {$selected}>{$value}</option>";
        }

        return "<select id='{$id}' name='{$id}'>{$options}</select>";

    }

    private static function create_html_select_setting($values, $setting_name, $meta) {
        $type_name         = self::type_name;
        $label_text        = ucfirst( str_replace('_', ' ', __( $setting_name, $type_name ) ) );
        $id                = $type_name . '_' . $setting_name;
        $full_setting_name = self::create_fully_qualified_setting_name($setting_name);
        $selected_value    = self::get_meta_from_array_if_set($meta, $full_setting_name);

        $select = self::create_html_select( $id, $values, $selected_value );
        return
            "<p>
                <label for='{$id}' class='{$full_setting_name}_row_title'>{$label_text}</label>
                {$select}
            </p>";
    }

    public static function meta_box_content($post, $metabox) {
        $type_name = self::type_name;
        $meta      = get_post_meta( $post->ID );
        $html      = wp_nonce_field( basename( __FILE__ ), self::nonce_name, true, false );

        // style selector
        $html .= self::create_html_select_setting(  self::get_style_names(), 'style', $meta);

        // image position
        $html .= self::create_html_select_setting( self::get_vertical_image_positions(),   'vertical_image_position',   $meta);
        $html .= self::create_html_select_setting( self::get_horizontal_image_positions(), 'horizontal_image_position', $meta);

        echo $html;
    }

    public static function save_meta_box_content( $post_id ) {
        if( ! isset( $_POST['post_type'] ) )          return;
        if( ! self::type_name == $_POST['post_type']) return;
        if( wp_is_post_autosave( $post_id ) )         return;
        if( wp_is_post_revision( $post_id ) )         return;
        if( ! wp_verify_nonce( $_POST[ self::nonce_name ], basename( __FILE__ ) ) ) return;

        // iterate over the settings and save as post meta
        foreach( self::get_fully_qualified_setting_names(self::get_setting_names()) as $name ) {
            if( isset( $_POST[ $name ] ) ) {
                // TODO sanitize
                update_post_meta( $post_id, $name, sanitize_text_field( $_POST[ $name ] ) );
            }
        }
    }

    private static function register_home_page_section_post_type()
    {
        $args = array(
            'labels' => array(
                'name' => 'Home page sections',
                'singular_name' => 'Home page section',
                'parent_item_color' => 'PIC',
            ),
            'description' => 'Editable and embeddable page HTML sections',
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_admin_bar' => false,
            'exclude_from_search' => false,
            'show_in_nav_menus' => false,

            'menu_position' => 22,
            'hierarchical' => true,
            'supports' => array(
                'title',
                'editor',
                'author',
                'thumbnail',
                'excerpt',
                'custom-fields',
                'revisions',
                'page-attributes',
            ),
            'rewrite' => array('slug'=>'section'),
            'register_meta_box_cb' => 'HyperspaceWP::register_meta_boxes',
            'has_archive' => true,
            //'query_var' => false,
        );
        flush_rewrite_rules(true);
        register_post_type(self::type_name, $args);
    }

    private static function get_post_by_slug( $post_type, $the_slug ) {
        $my_posts = get_posts(array(
            'name' => $the_slug,
            'post_type' => $post_type,
            'numberposts' => 1
        ));

        if ( $my_posts ) {
            return $my_posts[0];
        }

        return null;
    }

    public static function get_home_page_section_posts() {
        return  get_posts(array(
            'post_type' => self::type_name,
            'post_parent'=>'0',
        ));
    }

    /**
     * Singleton pattern function to
     *
     * @return two dimensional array of a homepage section post and its meta array
     */
    public static function get_ordered_home_page_section_groups() {
        static $ordered_home_page_section_groups = null;
        if( $ordered_home_page_section_groups ) {
            return $ordered_home_page_section_groups;
        } else {
            $ordered_home_page_section_groups = array();
            $home_page_section_posts = self::get_home_page_section_posts();
            //$ordered_home_page_section_posts = array();

            foreach( $home_page_section_posts as $section ) {
                $meta = get_post_meta( $section->ID );
                array_push($ordered_home_page_section_groups, array('post'=>$section, 'post_meta'=>$meta));
            }
            $ordered_home_page_section_groups = array_reverse($ordered_home_page_section_groups);
        }
        return $ordered_home_page_section_groups;
    }


    //-------------------------------------------------------------------------
    //--- HTML generators -----------------------------------------------------
    //-------------------------------------------------------------------------

    public static function create_nav_list($section_groups, $same_page_links) {
        $links = '';
        foreach( $section_groups as $section_group ) {
            $link_text = $section_group['post']->post_name;
            if( $same_page_links ) {
                $uri = '#' . $link_text;
            } else {
                $uri = get_home_url() . '#' . $link_text;
            }
            $links .= "<li><a href='{$uri}'>" . $link_text . "</a></li>";
        }
        return "<nav><ul>" . $links . "</ul></nav>";
    }

    private static function apply_learn_more_template($learn_more_url) {
        $learn_more_link = '';

        if($learn_more_url) {
            $learn_more_link =
                "<ul class='actions'>
                    <li><a href='{$learn_more_url}' class='button scrolly'>Learn more</a></li>
                </ul>";
        }
        return $learn_more_link;
    }

    /**
     * This function will return the post excerpt if it exists.
     * If not it returns the post's content.
     *
     * It also applies any shortcodes.
     */
    public static function get_excerpt_or_content( $post ) {
        $html = '';
        if( isset( $post->post_excerpt ) && $post->post_excerpt ) {
            $html = '<p>' . $post->post_excerpt . '</p>';
            if( $post->post_content ) {
                $html .= self::apply_learn_more_template( get_permalink( $post ) );
            }
        } else {
            $html = $post->post_content;
        }

        return do_shortcode( $html );
    }

    public static function get_attachment_url( $post_object, $size='thumbnail' ){
        $img_id = get_post_thumbnail_id( $post_object );
        $thumb = wp_get_attachment_image_src( $img_id, $size );
        $image_url = $thumb['0'];
        return $image_url;
    }

    public static function apply_inner_template( $title_size, $post_title, $content ) {
        $html =
            "<div class='inner'>
                <{$title_size}>{$post_title}</{$title_size}>
                {$content}
            </div>";
        return $html;
    }

    public static function get_home_page_subsection_content( $subsection_object, $subsection_meta ) {
        $processed_content = self::get_excerpt_or_content( $subsection_object );
        $post_title = $subsection_object->post_title;
        $image_url = self::get_attachment_url( $subsection_object, array(438, 438) );
        $image_vertical_position   = self::get_vertical_image_position( $subsection_meta );
        $image_horizontal_position = self::get_horizontal_image_position( $subsection_meta );

        $inner = self::apply_inner_template('h2', $post_title, $processed_content );

        $html =
            "<section>
                 <a href='#' class='image' >
                     <img
                        src='{$image_url}'
                        display='none' alt=''
                        data-position='{$image_vertical_position} {$image_horizontal_position}' />
                 </a>
                 <div class='content'>{$inner}</div>
            </section>";

        return $html;
    }

    public static function apply_main_section_template( $post_name, $title, $title_size, $classes, $content ) {
        //$site_name = get_bloginfo ('name');
        $inner = self::apply_inner_template( $title_size, $title, $content );
        $html =
            "<section id='{$post_name}' class='wrapper fade-up intro fullscreen {$classes}'>
                {$inner}
            </section>";
        return $html;
    }

    public static function apply_section_template($post_name, $classes, $content) {
        $html = "<section id='{$post_name}' class='wrapper fade-up {$classes}'>" . $content. "</section>";
        return $html;
    }

    public static function apply_intro_section_template( $post_name, $classes, $content ) {
        $site_name = get_bloginfo ('name');
        $html = self::apply_main_section_template( $post_name, $site_name, 'h1', $classes, $content);
        return $html;
    }


    public static function get_home_page_section_html( $section_object, $section_meta ) {
        $post_id = $section_object->ID;
        $children = array_reverse( get_children( array( 'post_parent'=>$post_id, 'post_type'=>self::type_name ) ) );
        $content_html = '';
        $img_url = self::get_attachment_url( $section_object );

        // get content
        if( $children ) {
            foreach ($children as $child) {
                $content_html .= self::get_home_page_subsection_content( $child, get_post_meta( $child->ID ) );
            }
        } elseif ( $img_url ) {
            $content_html = self::get_home_page_subsection_content( $section_object, $section_meta );

        } else {
            $content_html =
                self::apply_inner_template('h2', $section_object->post_title, self::get_excerpt_or_content( $section_object ) );
            ;
        }

        // add classes
        $classes = '';
        $classes = self::get_meta_from_array_if_set($section_meta, self::create_fully_qualified_setting_name('style') );
        if( $children || $img_url ) {
            $classes .= ' spotlights';
        }

        $post_name  = $section_object->post_name;
        $html = self::apply_section_template($post_name, $classes, $content_html);

        return $html;
    }

    /*
     * shortcode function that returns the template directory
     * [wphyperspace_templatedir]
     */
    public static function get_template_dir( $atts ){
        return get_stylesheet_directory_uri();
    }

    public static function init() {
        self::register_home_page_section_post_type();
        add_action( 'add_meta_boxes_' . self::type_name, 'HyperspaceWP::register_meta_boxes' );
        add_action( 'save_post', 'HyperspaceWP::save_meta_box_content' );
        //add_action( 'save_post', )
    }

    public static function after_theme_setup() {
        add_theme_support( 'post-thumbnails' );
        set_post_thumbnail_size( 438, 438, true );
        add_shortcode( 'wphyperspace_templatedir', 'HyperspaceWP::get_template_dir' );
    }

    public static function enqueue_scripts() {
        $template_dir = get_template_directory_uri();
        wp_enqueue_script('html5shiv', $template_dir . '/assets/js/ie/html5shiv.js',        array(), false, false);
        wp_enqueue_script('scrollex',  $template_dir . '/assets/js/jquery.scrollex.min.js', array('jquery'), false, true);
        wp_enqueue_script('scrolly',   $template_dir . '/assets/js/jquery.scrolly.min.js',  array('jquery'), false, true);
        wp_enqueue_script('skel',      $template_dir . '/assets/js/skel.min.js',            array(), false, true);
        wp_enqueue_script('util',      $template_dir . '/assets/js/util.js',                array(), false, true);
        wp_enqueue_script('main',      $template_dir . '/assets/js/main.js',                array('jquery'), false, true);

        wp_register_style('hyperspace-main',  $template_dir . '/assets/css/main.css' );
        if ( is_child_theme() ) {
            wp_register_style( 'parent-style', trailingslashit( get_template_directory_uri() ) . 'style.css', array('hyperspace-main') );
        }
        wp_register_style( 'hyperspaceWP-style', get_stylesheet_uri(), array('hyperspace-main', 'parent-style') );

        wp_enqueue_style('hyperspaceWP-style');
    }

    public static function register_widgets() {
        $args = array(
            'name'          => __( 'Footer widget', self::type_name ), // __( $setting_name, $type_name )
            'id'            => self::type_name . 'footer',
            'description'   => 'Footer content',
            'class'         => self::type_name . 'footer',
            'before_widget' => '',
            'after_widget'  => '',
            'before_title'  => '<h2 class="widgettitle">',
            'after_title'   => '</h2>' );
        register_sidebar($args);
    }

    //-------------------------------------------------------------------------
    //--- utility functions ---------------------------------------------------
    //-------------------------------------------------------------------------

    public static function str_starts_with($haystack, $needle) {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    public static function get_if_set( $array, $key ) {
        if( isset( $array[$key] ) ) {
            return $array[$key];
        } else {
            return false;
        }
    }

    public static function get_meta_from_array_if_set( $array, $key, $default='' ) {
        if( $meta = self::get_if_set( $array, $key ) ) {
            return $meta[0];
        } else {
            return $default;
        }
    }
}
add_action( 'init',               'HyperspaceWP::init' );
add_action( 'widgets_init',       'HyperspaceWP::register_widgets' );
add_action( 'after_setup_theme',  'HyperspaceWP::after_theme_setup' );
add_action( 'wp_enqueue_scripts', 'HyperspaceWP::enqueue_scripts' );



