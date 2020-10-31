<?php
/*
Plugin Name: Yet Another Lazy Loader
Plugin Name: http://example.com
Description: The plugin to add lazy loading to images, videos and iframes
Version: 1.0.0
Author: Svjatoslav Kachmar
Author Uri: http://example.com
*/

if (!defined('ABSPATH'))
{
    die("Hey, you don't access this file");
}

class Lazy_Loading_Plugin
{
    function __construct()
    {
        add_action('init', array(
            $this,
            'register'
        ));
    }

    function register()
    {
        add_action('wp_enqueue_scripts', array(
            $this,
            'enqueue'
        ));
    }

    function enqueue()
    {
        wp_enqueue_style('plugin_style', plugins_url('/assets/style.css', __FILE__));
    }
}

if (class_exists('Lazy_Loading_Plugin'))
{
    spl_autoload_register( function ( $class_name ) {
        if ( false !== strpos( $class_name, 'LL' ) ) {
        $classes_dir = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR;
        $class_file = $class_name . '.php';
        require_once $classes_dir . $class_file;
        }
    });


    $settings_obj = new LL_Settings();
    $settings_obj->plugin_settings();

    add_filter('the_content', 'add_lazy_loading');
        function add_lazy_loading($content)
        {
            $options = get_option('lazy_loading_options');
            $in_general = $options['Enable'];
            $pics = $options['Enable_Pics'];
            $back_pics = $options['Enable_Background_Pics'];
            $iframes = $options['Enable_Iframes'];
            $videos = $options['Enable_Videos'];

            if ($pics == 'enable' && $in_general == 'enable')
            {
                $lazy_load_pics = new LL_LozadProcessing();
                $lazy_load_pics->processImages($content);
            }
            if ($back_pics == 'enable' && $in_general == 'enable') {
                $lazy_load_back_pics = new LL_LozadProcessing();
                $lazy_load_back_pics->processBackground($content);
            } 
            if ($iframes == 'enable' && $in_general == 'enable') {
                $lazy_load_iframes = new LL_LozadProcessing();
                $lazy_load_iframes->processIframe($content);
            }
            if ($videos == 'enable' && $in_general == 'enable') {
                $lazy_load_videos = new LL_LozadProcessing();
                $lazy_load_videos->processVideo($content);
            }
            return $content;
        }
}

function add_scripts(){
    $options = get_option('lazy_loading_options');
    $in_general = $options['Enable'];
    $pics = $options['Enable_Pics'];
    $back_pics = $options['Enable_Background_Pics'];
    $iframes = $options['Enable_Iframes'];
    $videos = $options['Enable_Videos'];
    
    if (($videos == 'enable' || $iframes == 'enable' || $back_pics == 'enable' || $pics == 'enable') && $in_general == 'enable') {
        wp_register_script( 'lozad_script', plugins_url( 'js/lozad.js', __FILE__ ), array( 'jquery' ), '1.0', true );
        wp_enqueue_script( 'lozad_script' );

        wp_register_script( 'initLozad_script', plugins_url( 'js/initLozad.js', __FILE__ ), array( 'jquery' ), '1.0', true );
        wp_enqueue_script( 'initLozad_script' );
    } 
}
add_action( 'wp_enqueue_scripts', 'add_scripts' );