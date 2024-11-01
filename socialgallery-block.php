<?php 
/**
* Plugin Name:          SocialGallery Block
* Plugin URI:
* Description:          SocialGallery Block Plugin is a block plugin that is compatible with all WordPress themes. In plugin block controls are given, that's help to add insta feed in galley layout.
* Version:              1.0.1
* Requires at least:    5.3
* Requires PHP:         5.2
* Tested up to:         6.6.1
* Author:               
* Author URI:           
* License:              GPLv2 or later
* License URI:          https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:          socialgallery-block
* Domain Path:          /language
*/

if( !defined( 'ABSPATH' ) ) {exit(); }
define('SOCIALGALLERY_BLOCK_VERSION', 'self::VERSION');
define('SOCIALGALLERY_BLOCK_PLUGIN_PATH',trailingslashit(plugin_dir_path(__FILE__)));
define('SOCIALGALLERY_BLOCK_PLUGIN_URL',trailingslashit(plugins_url('/',__FILE__)));
define('SOCIALGALLERY_BLOCK_PLUGIN_UPLOAD',trailingslashit( wp_upload_dir()['basedir'] ) );
require_once SOCIALGALLERY_BLOCK_PLUGIN_PATH . '/inc/socialgalleryblock.php';
require_once SOCIALGALLERY_BLOCK_PLUGIN_PATH . '/inc/svgicon.php';

final class SocialGallery_Block{

    /** 
     * Construct Function
     */
    private  function __construct(){
        add_action('plugins_loaded',[$this,'init_plugin']);
    }

    /**
     * Singletone Instance
     */
    public static function init(){
        static $instance=false;
        if(!$instance){
            $instance=new self();
        }
        return $instance;

    }

    /**
     * Plugin Init
     */
    public function init_plugin(){
       $this->enqueue_scripts();
    }

    /**
     * Enqueue Script
     */
    public function enqueue_scripts(){
        add_action('enqueue_block_editor_assets',[$this,'register_block_editor_assets']);
        add_action('enqueue_block_assets',[$this,'register_block_assets']);
        add_action('admin_enqueue_scripts',[$this,'register_admin_scripts']);
        add_action('init',[$this,'register_block']);        
        add_action('init',[$this,'socialgallery_block_load_plugin_textdomain']);
    }

    /**
     * Register Block Editor Assets
     */   
    public function register_block_editor_assets(){
        wp_enqueue_script(
            'socialgallery-block-build',
            SOCIALGALLERY_BLOCK_PLUGIN_URL.'/build/index.js',
            [
                'wp-blocks',
                'wp-editor',
                'wp-i18n',
                'wp-element',
                'wp-components',
                'wp-data',

            ],
            '1.0.0', // Specify a version number
            true // Load in footer
        );
        $socialgalleryblockaccesskey = get_option('socialgallery_block_access_token');
        wp_localize_script( 'socialgallery-block-build', 'socialgalleryblockaccess', array(
            'socialgalleryblockaccesskey' => $socialgalleryblockaccesskey,
        ) );
    }
    public function register_block_assets(){

        wp_enqueue_style(
           'socialgallery-block-editor-css',
           SOCIALGALLERY_BLOCK_PLUGIN_URL.'assets/css/editor.css',
           false,
           'all'
        );

        wp_enqueue_style(
           'socialgallery-block-style',
           SOCIALGALLERY_BLOCK_PLUGIN_URL.'assets/css/style.css',
           [],
           '1.0.0', // Specify a version number
            'all'
        );

        wp_enqueue_style('socialgallery-block-fontawesome', SOCIALGALLERY_BLOCK_PLUGIN_URL.'assets/css/all.min.css', null, 'all' );
       
    }
    
    /**
     * Register Admin Scripts
     */   
    public function register_admin_scripts(){  

        wp_localize_script('socialgallery-block-editor-js','plugin',['pluginpath' => SOCIALGALLERY_BLOCK_PLUGIN_URL,'plugindir' => SOCIALGALLERY_BLOCK_PLUGIN_UPLOAD ]);
        wp_enqueue_script('socialgallery-block-editor-js');            
       
    }


   /**
    * Register Blocks
    */
    public function register_block(){      
        
        register_block_type('socialgallery-block/socialgallery-block',[
            'style'=> 'socialgallery-block-style',
            'editor_style'=>'socialgallery-block-editor-css',
            'render_callback' => 'socialgallery_block_render_callback',
            'attributes' => [
                'customString'=> [ 'type'=>'string'], 
                'customcss'=> [ 'type'=>'string', 'default'=>''],
                'customjs'=> [ 'type'=>'string', 'default'=>''],         
                'posts'=> [ 'type'=>'string'],
                'accountInfo'=> ['type'=>'string'],
                'gallerylayout'=> [ 'type'=>'string', 'default'=>'grid'],
                'uniqueid'=> [ 'type'=>'string'],
                'controlType'=> [ 'type'=>'string', 'default'=>'basic'],
                'hidecaption'=> [ 'type'=>'boolean', 'default'=>true],
                'captionalign'=> [ 'type'=>'string', 'default'=>'center'],
                'captionstyle'=> [ 'type'=>'string', 'default'=>'default'],
                'titleColor'=> [ 'type'=>'string', 'default'=>''],
                'bgColor'=> [ 'type'=>'string', 'default'=>''],            
                'bggradientValue'=> [ 'type'=>'string', 'default'=>''],
                'bgoverlayColor'=> [ 'type'=>'string', 'default'=>''],            
                'bggradientoverlayValue'=> [ 'type'=>'string', 'default'=>''],
                'layout'=> [ 'type'=>'string', 'default'=>'columns'],
                'previewnocolumn'=> [ 'type'=>'string', 'default'=>'Desktop'],
                'nocolumn'=> [ 'type'=>'number', 'default'=>3],
                'nocolumntab'=> [ 'type'=>'number', 'default'=>3],
                'nocolumnmob'=> [ 'type'=>'number', 'default'=>3],
                'previewnocustomwidth'=> [ 'type'=>'string', 'default'=>'Desktop'],
                'customwidth'=> [ 'type'=>'string', 'default'=>'250px'],
                'customwidthtab'=> [ 'type'=>'string', 'default'=>'250px'], 
                'customwidthmob'=> [ 'type'=>'string', 'default'=>'250px'], 
                'previewnocustomheight'=> [ 'type'=>'string', 'default'=>'250px'],
                'customheight'=> [ 'type'=>'string', 'default'=>'250px'], 
                'customheighttab'=> [ 'type'=>'string', 'default'=>'250px'], 
                'customheightmob'=> [ 'type'=>'string', 'default'=>'250px'],
                'noposts'=> [ 'type'=>'number', 'default'=>10],
                'hidedesktop'=> [ 'type'=>'boolean', 'default'=>true],
                'hidetablet'=> [ 'type'=>'boolean', 'default'=>true],
                'hidemobile'=> [ 'type'=>'boolean', 'default'=>true],
                'boxshadow'=> [ 'type'=>'boolean', 'default'=>false],
                'shadowColor'=> [ 'type'=>'string', 'default'=>'#ADACAC'],
                'hshadow'=> [ 'type'=>'number', 'default'=>0],
                'vshadow'=> [ 'type'=>'number', 'default'=>0],
                'blurshadow'=> [ 'type'=>'number', 'default'=>0],
                'previewmargins'=> [ 'type'=>'string', 'default'=>'Desktop'],
                'previewitemgap'=> [ 'type'=>'string', 'default'=>'Desktop'],
                'itemgap'=> [ 'type'=>'string', 'default'=>'20px'],
                'itemgaptab'=> [ 'type'=>'string', 'default'=>'20px'],
                'itemgapmob'=> [ 'type'=>'string', 'default'=>'20px'],
                'margins'=> ['default'=> [ 'top'=> '0px', 'left'=> '0px', 'right'=> '0px', 'bottom'=>'0px'],],
                'marginstab'=> ['default'=> [ 'top'=> '0px', 'left'=> '0px', 'right'=> '0px', 'bottom'=>'0px'],],
                'marginsmob'=> ['default'=> [ 'top'=> '0px', 'left'=> '0px', 'right'=> '0px', 'bottom'=>'0px'],],
                'previewpaddings'=> [ 'type'=>'string', 'default'=>'Desktop'],
                'paddings'=>['type'=>'object', 'default'=> [ 'top'=> '0px', 'left'=> '0px', 'right'=> '0px', 'bottom'=>'0px'],],
                'paddingstab'=>['type'=>'object', 'default'=> [ 'top'=> '0px', 'left'=> '0px', 'right'=> '0px', 'bottom'=>'0px'],],
                'paddingsmob'=>['type'=>'object', 'default'=> [ 'top'=> '0px', 'left'=> '0px', 'right'=> '0px', 'bottom'=>'0px'],],
                'previewitempaddings'=> [ 'type'=>'string', 'default'=>'Desktop'],
                'itempaddings'=>['type'=>'object', 'default'=> [ 'top'=> '0px', 'left'=> '0px', 'right'=> '0px', 'bottom'=>'0px'],],
                'itempaddingstab'=>['type'=>'object', 'default'=> [ 'top'=> '0px', 'left'=> '0px', 'right'=> '0px', 'bottom'=>'0px'],],
                'itempaddingsmob'=>['type'=>'object', 'default'=> [ 'top'=> '0px', 'left'=> '0px', 'right'=> '0px', 'bottom'=>'0px'],],
                'previewcaptionspaddings'=> [ 'type'=>'string', 'default'=>'Desktop'],
                'captionspaddings'=>['type'=>'object', 'default'=> [ 'top'=> '0px', 'left'=> '0px', 'right'=> '0px', 'bottom'=>'0px'],],
                'captionspaddingstab'=>['type'=>'object', 'default'=> [ 'top'=> '0px', 'left'=> '0px', 'right'=> '0px', 'bottom'=>'0px'],],
                'captionspaddingsmob'=>['type'=>'object', 'default'=> [ 'top'=> '0px', 'left'=> '0px', 'right'=> '0px', 'bottom'=>'0px'],],
                'border'=>['type'=>'object', 'default'=> [ 'color'=> '', 'style'=> '', 'width'=> ''],],
                'borderradius'=>['type'=>'object', 'default'=> [ 'top'=> '0px', 'left'=> '0px', 'right'=> '0px', 'bottom'=>'0px'],],
                'fontfamily'=> [ 'type'=>'string', 'default'=>'Open Sans'],
                'previewfontsize'=> [ 'type'=>'string', 'default'=>'Desktop'],
                'titlefontSize'=> [ 'type'=>'string', 'default'=>''],
                'titlefontSizetab'=> [ 'type'=>'string', 'default'=>''],
                'titlefontSizemob'=> [ 'type'=>'string', 'default'=>''],
                'TitleFontWeight'=> [ 'type'=>'string', 'default'=>600],
                'previewlineheight'=> [ 'type'=>'string', 'default'=>'Desktop'],
                'TitleLineHeight'=> [ 'type'=>'number', 'default'=>''],
                'TitleLineHeighttab'=> [ 'type'=>'number', 'default'=>''],
                'TitleLineHeightmob'=> [ 'type'=>'number', 'default'=>''],
                'TitleTransform'=> [ 'type'=>'string', 'default'=>''],
                'TitleDecoration'=> [ 'type'=>'string', 'default'=>''],
                'previewltrspaceing'=> [ 'type'=>'string', 'default'=>'Desktop'], 
                'TitleLetterSpacing'=> [ 'type'=>'number', 'default'=>''],
                'TitleLetterSpacingtab'=> [ 'type'=>'number', 'default'=>''],
                'TitleLetterSpacingmob'=> [ 'type'=>'number', 'default'=>''],    
            ]    
        ]);  
    }      

    /**
     * Load the localisation file.
     */
    public function socialgallery_block_load_plugin_textdomain() {
        load_plugin_textdomain( 'socialgallery-block', false, dirname( plugin_basename( __FILE__ ) ) . '/language' );
    }

}

/** 
 * Init
 */

function socialgallery_block_run_plugin(){
   return SocialGallery_Block::init();

}
socialgallery_block_run_plugin();

// Step 2: Add a top-level menu
function socialgallery_block_plugin_menu() {
    $svg_icon = 'data:image/svg+xml;base64,' . base64_encode(socialgallery_block_get_svg_icon());
    add_menu_page(
        'SocialGallery Block Plugin Settings',
        'SocialGallery Block',
        'manage_options',
        'socialgallery-block',
        'socialgallery_block_options_page_content',
        $svg_icon, // Icon
        20 // Position
    );
}
add_action('admin_menu', 'socialgallery_block_plugin_menu');

// Step 3: Create options page content
function socialgallery_block_options_page_content() {
    ?>
    <div class="wrap">
        <h2><b>SocialGallery Block</b></h2>
        <form method="post" action="options.php">
            <?php settings_fields('socialgallery_block_options'); ?>
            <?php do_settings_sections('socialgallery-block'); ?>
            <?php submit_button('Save Settings'); ?>
        </form>
    </div>
    <?php
}

// Step 4: Add an input field to enter the access token
function socialgallery_block_access_token_field() {
    $value = get_option('socialgallery_block_access_token');
    echo '<textarea name="socialgallery_block_access_token" rows="4" cols="50">' . esc_attr($value) . '</textarea>';
}

// Step 5: Save the access token value
function socialgallery_block_register_settings() {
    register_setting('socialgallery_block_options', 'socialgallery_block_access_token');
    add_settings_section(
        'socialgallery_block_settings_section',
        'Add Instagram Account',
        'socialgallery_block_settings_section_callback',
        'socialgallery-block'
    );
    add_settings_field(
        'socialgallery_block_access_token_field',
        'Access Token',
        'socialgallery_block_access_token_field',
        'socialgallery-block',
        'socialgallery_block_settings_section'
    );
}
add_action('admin_init', 'socialgallery_block_register_settings');

function socialgallery_block_settings_section_callback() {
    echo '<p><a href="'.esc_url("https://youtu.be/uUkl3xZF_pg").'" target="_blank">How to get your access token?</a></p>';
}

/**
 * Add plugin Class
 */
add_filter( 'body_class', function ( $classes ) {
    $classes[] = 'socialgallery-block';
    return $classes;
});

// Register the activation hook
register_activation_hook(__FILE__, 'socialgallery_block_activate');

function socialgallery_block_activate() {
    // Set an option to trigger the redirection
    add_option('socialgallery_block_activation_redirect', true);
}

// Check for the option and redirect if it exists and condition is met
add_action('admin_init', 'socialgallery_block_redirect');

function socialgallery_block_redirect() {
    // Check if the option is set
    if (get_option('socialgallery_block_activation_redirect', false)) {
        // Delete the option to prevent multiple redirects
        delete_option('socialgallery_block_activation_redirect');
        
        // Prevent redirection on multisite or if activating multiple plugins
        if (is_network_admin() ) {
            return;
        }

        // Condition to check before redirecting
        // You can replace this with your custom condition
        if (current_user_can('manage_options')) { // Example condition
            // Redirect to the desired page
            wp_redirect(admin_url('admin.php?page=socialgallery-block'));
            exit;
        }
    }
}