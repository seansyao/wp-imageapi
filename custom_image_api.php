<?php
/*
    Plugin Name: Custom Image API
    Description: This is a custom image api for photo gallery
    Version:     1.0
    Author:      Sean Yao
    Author URI:  http://www.seanyao.com
*/

add_action('admin_menu', 'imgapi_plugin_setup_menu');
 
function imgapi_plugin_setup_menu(){
    add_menu_page( 'Upload Image Page', 'Upload Image Plugin', 'manage_options', 'imageapi-plugin', 'iapi_init' );
}
 
function iapi_init(){
    iapi_handle_post();
?>
    <h2>Upload image file(s)</h2>
    <!-- Form to handle the upload - The enctype value here is very important
    <form  method="post" enctype="multipart/form-data">
        <input type='file' id='test_upload_pdf' name='test_upload_pdf'></input>
        <?php /*submit_button('Upload')*/ ?>
    </form> -->
    <form id="file_upload" method="post" action="#" enctype="multipart/form-data">
        <input type="file" name="my_file_upload[]" multiple="multiple">
        <input name="my_file_upload" type="submit" value="Upload" />
    </form>
<?php
}
 
function iapi_handle_post(){
    // First check if the file appears on the _FILES array
    /*if(isset($_FILES['test_upload_pdf'])){
        $pdf = $_FILES['test_upload_pdf'];
 
        // Use the wordpress function to upload
        // test_upload_pdf corresponds to the position in the $_FILES array
        // 0 means the content is not associated with any other posts
        $uploaded=media_handle_upload('test_upload_pdf', 0);
        // Error checking using WP functions
        if(is_wp_error($uploaded)){
            echo "Error uploading file: " . $uploaded->get_error_message();
        }else{
            echo "File upload successful!";
            test_convert($uploaded);
        }
 
 
    }*/

    /*
    $filename = sanitize_text_field($_FILES["image"]["name"]);
    $deprecated = null;
    $bits = file_get_contents($_FILES["image"]["tmp_name"]);
    $time = current_time('mysql');

    $upload = wp_upload_bits($filename, $deprecated, $bits, $time);

    global $current_user;
    get_currentuserinfo();
    $upload_dir = wp_upload_dir(); 
    $user_dirname = $upload_dir['basedir'] . '/' . $current_user->user_login;
    if(!file_exists($user_dirname)) wp_mkdir_p($user_dirname);

    $upload = wp_upload_dir();
    $upload_dir = $upload['basedir'] . $directory_path;
    $permissions = 0755;
    $oldmask = umask(0);
    if (!is_dir($upload_dir)) mkdir($upload_dir, $permissions);
    $umask = umask($oldmask);
    $chmod = chmod($upload_dir, $permissions);
    */

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );
    
        $files = $_FILES["my_file_upload"];
        foreach ($files['name'] as $key => $value) {
            if ($files['name'][$key]) {
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
                    echo "File added successfully with ID: " . $attachment_id . "<br>";
                    echo wp_get_attachment_image($attachment_id, array(800, 600)) . "<br>"; //Display the uploaded image with a size you wish. In this case it is 800x600
                }
            }
        }
    }
}
 /*
function test_convert($id){
    // Get the file details
    $file = get_post($id);
    // Account details
    $email="";
    $password="";
 
    // Declare a new SoapClient - This is a class from PHP
    $client = new SoapClient('http://cloud.idrsolutions.com:8080/HTML_Page_Extraction/IDRConversionService?wsdl');
 
    // Get the data of the file as bytes
    $contents=file_get_contents(wp_get_attachment_url($id));
 
    // plugin_dir_path(__FILE__) gets the location of the plugin directory
    // Using preg replace to replace the directory sepeerators with the correct type
    // This is where the output will be written to
    $outputdir = preg_replace("[\\/]", DIRECTORY_SEPARATOR, plugin_dir_path(__FILE__)) . "output".DIRECTORY_SEPARATOR. $file->post_title. DIRECTORY_SEPARATOR;
    echo $outputdir;
    if (!file_exists($outputdir)) {
        mkdir($outputdir, 0777, true);
    }
 
    // Declare stlye parameters here - left blank here
    $style_params = array();
    // Set up array for the conversion
    $conversion_params = array("email" => $email,
                            "password" =>$password,
                            "fileName"=>$file->post_title,
                            "dataByteArray"=>$contents,
                            "conversionType"=>"html5",
                            "conversionParams"=>$style_params,
                            "xmlParamsByteArray"=>null,
                            "isDebugMode"=>false);
 
    try{
        $output = (array)($client->convert($conversion_params));
        // This method is very improtant as it allows us access to the file system
        WP_Filesystem();
        // Write output as zip
        file_put_contents($outputdir.$file->post_title.".zip", $output);
        // Unzip the file
        $result=unzip_file($outputdir.$file->post_title.".zip", $outputdir);
    } catch (Exception $e){
        echo $e->getMessage() . "<br/>";
        return;
    }
}*/
?>