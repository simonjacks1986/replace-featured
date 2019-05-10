<?php
// Function to use first image as featured image
$ids = [];
// Modify query
$args = array( 'posts_per_page' => -1, 'post_type'=> 'post', 'order' => 'DESC', 'post__in' => array(19432) );
$myposts = get_posts( $args );
foreach ( $myposts as $post ) : setup_postdata( $post ); 
	$image = wp_get_attachment_url( get_post_thumbnail_id($post->ID)); 

	if(empty($image)) {
		$content = get_the_content();
	    preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $content, $image_url);    
	    
	    // Use to remove any exteaneous info in the URL
	    // echo $image_loc = $image_url['src'], 0, strpos($image_url['src'], "?"));
	    echo $image_url['src'];
	    
	    // Gives us access to the download_url() and wp_handle_sideload() functions
		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		// URL to the WordPress logo
		$url = $image_loc;
		$timeout_seconds = 5;

		// Download file to temp dir
		$temp_file = download_url( $url, $timeout_seconds );

		if ( !is_wp_error( $temp_file ) ) {

		    // Array based on $_FILE as seen in PHP file uploads
		    $file = array(
		        'name'     => basename($url), // ex: wp-header-logo.png
		        'type'     => 'image/png',
		        'tmp_name' => $temp_file,
		        'error'    => 0,
		        'size'     => filesize($temp_file),
		    );

		    $overrides = array(
		        'test_form' => false,
		        'test_size' => true,
		    );

		    // Move the temporary file into the uploads directory
		    $results = wp_handle_sideload( $file, $overrides );

		    if ( !empty( $results['error'] ) ) {
		        // Insert any error handling here
		    } else {

		        $filename  = $results['file']; // Full path to the file
		        $local_url = $results['url'];  // URL to the file in the uploads dir
		        $type      = $results['type']; // MIME type of the file
		        echo $filename;
		        echo '<br>';
		        echo $local_url;
		        echo '<br>';
		        echo $type;
		        // Perform any actions here based in the above results
		    }

		    $filename = $results['file'];
			$attachment = array(
				'post_mime_type' => $results['type'],
				'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
				'post_content' => '',
				'post_status' => 'inherit',
				'guid' => $local_url
			 );
			$attach_id = wp_insert_attachment( $attachment, $filename );
			require_once( ABSPATH."wp-admin/includes/image.php" );
			$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			set_post_thumbnail($post->ID, $attach_id );
		}

		array_push($ids, $post->ID);
	}

endforeach; 
wp_reset_postdata(); 
echo "<pre>";
print_r($ids);
echo "</pre>";
?>