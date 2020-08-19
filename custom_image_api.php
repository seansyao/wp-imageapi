<?php
/*
    Plugin Name: Custom Image API
    Description: This is a custom image api for photo gallery
    Version:     1.0
    Author:      Sean Yao
    Author URI:  http://www.seanyao.com
*/

if ( function_exists( 'add_filter' ) ) {
    add_action( 'plugins_loaded', array( 'Attachment_Taxonomies', 'get_object' ));
    add_action('admin_menu', 'imgapi_plugin_setup_menu');
    add_action('rest_api_init', 'custom_rest_route');
}
 
function imgapi_plugin_setup_menu(){
    add_menu_page( 'Upload Image Page', 'Upload Image Plugin', 'manage_options', 'imageapi-plugin', 'iapi_init' );
}
 
function iapi_init(){
    iapi_handle_post();
?>
    <h2>Upload image file(s). Image file type allow: jpg, jpeg, png.</h2>
    <form id="file_upload" method="post" action="#" enctype="multipart/form-data">
        <input type="file" name="my_file_upload[]" multiple="multiple">
        <input name="my_file_upload" type="submit" value="Upload" />
    </form>
<?php
}
 
function iapi_handle_post(){
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );
    
        $files = $_FILES["my_file_upload"];
        $allowed_file_types = array('image/jpg', 'image/jpeg', 'image/png');
        foreach ($files['name'] as $key => $value) {
            if ($files['name'][$key] && in_array($files['type'][$key], $allowed_file_types)) {
                $file = array(
                    'name' => $files['name'][$key],
                    'type' => $files['type'][$key],
                    'tmp_name' => $files['tmp_name'][$key],
                    'error' => $files['error'][$key],
                    'size' => $files['size'][$key]
                );
                $_FILES = array("upload_file" => $file);
                $attachment_id = media_handle_upload("upload_file", 0);
    
                if (is_wp_error($attachment_id)) {
                    // There was an error uploading the image.
                    echo "Error adding file";
                } else {
                    // The image was uploaded successfully!
                    wp_set_object_terms($attachment_id, 'Photo App', 'attachment_category', true);
                    echo "File added successfully with ID: " . $attachment_id . "<br>";
                    echo wp_get_attachment_image($attachment_id, array(800, 600)) . "<br>"; //Display the uploaded image with a size you wish. In this case it is 800x600
                }
            }
            else {
                echo "Error adding file";
            }
        }
    }
}

class Attachment_Taxonomies {

    static private $classobj;

    /**
     * Constructor, init the functions inside WP
     *
     * @since   1.0.0
     * @return  void
     */
    public function __construct() {

        // load translation files
        add_action( 'admin_init', array( $this, 'localize_plugin' ) );
        // add taxonmies
        add_action( 'init', array( $this, 'setup_taxonomies' ) );
    }

    /**
     * Handler for the action 'init'. Instantiates this class.
     *
     * @since   1.0.0
     * @access  public
     * @return  $classobj
     */
    public static function get_object() {

        if ( NULL === self::$classobj ) {
            self::$classobj = new self;
        }

        return self::$classobj;
    }

    /**
     * Localize plugin function.
     *
     * @uses    load_plugin_textdomain, plugin_basename
     * @since   2.0.0
     * @return  void
     */
    public function localize_plugin() {

        load_plugin_textdomain(
            'attachment_taxonomies',
            FALSE,
            dirname( plugin_basename( __FILE__ ) ) . '/languages/'
        );
    }

    /**
     * Setup Taxonomies
     * Creates 'attachment_tag' and 'attachment_category' taxonomies.
     * Enhance via filter `attachment_taxonomies`
     * 
     * @uses    register_taxonomy, apply_filters
     * @since   1.0.0
     * @return  void
     */
    public function setup_taxonomies() {

        $attachment_taxonomies = array();

        // Tags
        $labels = array(
            'name'              => _x( 'Media Tags', 'taxonomy general name', 'attachment_taxonomies' ),
            'singular_name'     => _x( 'Media Tag', 'taxonomy singular name', 'attachment_taxonomies' ),
            'search_items'      => __( 'Search Media Tags', 'attachment_taxonomies' ),
            'all_items'         => __( 'All Media Tags', 'attachment_taxonomies' ),
            'parent_item'       => __( 'Parent Media Tag', 'attachment_taxonomies' ),
            'parent_item_colon' => __( 'Parent Media Tag:', 'attachment_taxonomies' ),
            'edit_item'         => __( 'Edit Media Tag', 'attachment_taxonomies' ), 
            'update_item'       => __( 'Update Media Tag', 'attachment_taxonomies' ),
            'add_new_item'      => __( 'Add New Media Tag', 'attachment_taxonomies' ),
            'new_item_name'     => __( 'New Media Tag Name', 'attachment_taxonomies' ),
            'menu_name'         => __( 'Media Tags', 'attachment_taxonomies' ),
        );

        $args = array(
            'hierarchical' => FALSE,
            'labels'       => $labels,
            'show_ui'      => TRUE,
            'show_admin_column' => TRUE,
            'query_var'    => TRUE,
            'rewrite'      => TRUE,
        );

        $attachment_taxonomies[] = array(
            'taxonomy'  => 'attachment_tag',
            'post_type' => 'attachment',
            'args'      => $args
        );

        // Categories
        $labels = array(
            'name'              => _x( 'Media Categories', 'taxonomy general name', 'attachment_taxonomies' ),
            'singular_name'     => _x( 'Media Category', 'taxonomy singular name', 'attachment_taxonomies' ),
            'search_items'      => __( 'Search Media Categories', 'attachment_taxonomies' ),
            'all_items'         => __( 'All Media Categories', 'attachment_taxonomies' ),
            'parent_item'       => __( 'Parent Media Category', 'attachment_taxonomies' ),
            'parent_item_colon' => __( 'Parent Media Category:', 'attachment_taxonomies' ),
            'edit_item'         => __( 'Edit Media Category', 'attachment_taxonomies' ), 
            'update_item'       => __( 'Update Media Category', 'attachment_taxonomies' ),
            'add_new_item'      => __( 'Add New Media Category', 'attachment_taxonomies' ),
            'new_item_name'     => __( 'New Media Category Name', 'attachment_taxonomies' ),
            'menu_name'         => __( 'Media Categories', 'attachment_taxonomies' ),
        );

        $args = array(
            'hierarchical' => TRUE,
            'labels'       => $labels,
            'show_ui'      => TRUE,
            'query_var'    => TRUE,
            'rewrite'      => TRUE,
        );

        $attachment_taxonomies[] = array(
            'taxonomy'  => 'attachment_category',
            'post_type' => 'attachment',
            'args'      => $args
        );

        $attachment_taxonomies = apply_filters( 'attachment_taxonomies', $attachment_taxonomies );

        foreach ( $attachment_taxonomies as $attachment_taxonomy ) {
            register_taxonomy(
                $attachment_taxonomy['taxonomy'],
                $attachment_taxonomy['post_type'],
                $attachment_taxonomy['args']
            );
        }

    }

} // end class

function custom_rest_route() {
    register_rest_route('sy/v1', 'photo_app', [
        'methods' => 'GET',
        'callback' => 'sy_photo_app'
    ]);
}

function sy_photo_app() {
    $taxonomy = 'attachment_category';
    $term = 'Photo App';
    $query_images_args = [
        'post_type'      => 'attachment',
        'post_mime_type' => 'image',
        'post_status'    => 'inherit',
        'posts_per_page' => - 1,
        'tax_query' => [
            [
                'taxonomy'  => $taxonomy,
                'field'     => 'slug',
                'terms'     => $term
            ]
        ]
    ];

    $query_images = new WP_Query( $query_images_args );
    $images = array();
    $data = [];
    $data['photo_count'] = count($query_images->posts);
    $data['photo_objects'] = [];
    $i = 0;
    foreach ( $query_images->posts as $image ) {
        $data['photo_objects'][$i]['id'] = $image->ID;
        $data['photo_objects'][$i]['post_date'] = $image->post_date;
        $data['photo_objects'][$i]['post_last_modified_date'] = $image->post_modified;
        $data['photo_objects'][$i]['image_url'] = $image->guid;
        $i++;
    }

    return $data;
}
?>