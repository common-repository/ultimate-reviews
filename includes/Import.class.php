<?php

/**
 * Class to handle importing reviews into the plugin
 */

if ( !defined( 'ABSPATH' ) )
	exit;

if (!class_exists('ComposerAutoloaderInit4618f5c41cf5e27cc7908556f031e4d4')) {require_once EWD_URP_PLUGIN_DIR . '/lib/PHPSpreadsheet/vendor/autoload.php';}
use PhpOffice\PhpSpreadsheet\Spreadsheet;
class ewdurpImport {

	public $status;
	public $message;

	public function __construct() {
		add_action( 'admin_menu', array($this, 'register_install_screen' ));

		if ( isset( $_POST['ewdurpImport'] ) ) { add_action( 'admin_init', array($this, 'import_reviews' )); }

		if ( isset( $_POST['ewdurpWooCommerceImport'] ) ) { add_action( 'admin_init', array($this, 'import_woocommerce_reviews' )); }
	}

	public function register_install_screen() {
		add_submenu_page( 
			'edit.php?post_type=urp_review', 
			'Import Menu', 
			'Import', 
			'manage_options', 
			'ewd-urp-import', 
			array($this, 'display_import_screen') 
		);
	}

	public function display_import_screen() {
		global $ewd_urp_controller;

		$import_permission = $ewd_urp_controller->permissions->check_permission( 'import' );
		?>
		<div class='wrap'>
			<h2>Import</h2>
			<?php if ( $import_permission ) { ?> 
				<form method='post' enctype="multipart/form-data">
					<p>
						<label for="ewd_urp_reviews_spreadsheet"><?php _e( 'Spreadsheet Containing Reviews', 'ultimate-reviews' ) ?></label><br />
						<input name="ewd_urp_reviews_spreadsheet" type="file" value=""/>
					</p>
					<input type='submit' name='ewdurpImport' value='Import Reviews' class='button button-primary' />
				</form>
				<form method='post'>
					<p>
						<label for="ewd_urp_reviews_spreadsheet"><?php _e( 'Import reviews from WooCommerce', 'ultimate-reviews' ) ?></label><br />
					</p>
					<input type='submit' name='ewdurpWooCommerceImport' value='Import WooCommerce Reviews' class='button button-primary' />
				</form>
			<?php } else { ?>
				<div class='ewd-urp-premium-locked'>
					<a href="https://www.etoilewebdesign.com/license-payment/?Selected=URP&Quantity=1&utm_source=urp_import" target="_blank">Upgrade</a> to the premium version to use this feature
				</div>
			<?php } ?>
		</div>
	<?php }

	public function import_reviews() {
		global $ewd_urp_controller;

		if ( ! current_user_can( 'edit_posts' ) ) { return; }

		$update = $this->handle_spreadsheet_upload();

    	$review_elements = $ewd_urp_controller->settings->get_review_elements();

		if ( $update['message_type'] != 'Success' ) :
			$this->status = false;
			$this->message =  $update['message'];

			add_action( 'admin_notices', array( $this, 'display_notice' ) );

			return;
		endif;

		$excel_url = EWD_URP_PLUGIN_DIR . '/user-sheets/' . $update['filename'];

	    // Build the workbook object out of the uploaded spreadsheet
	    @$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load( $excel_url );
	
	    // Create a worksheet object out of the product sheet in the workbook
	    $sheet = $spreadsheet->getActiveSheet();

	    // Get column names
	    $highest_column = $sheet->getHighestColumn();
	    $highest_column_index = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString( $highest_column );
	    for ( $column = 1; $column <= $highest_column_index; $column++ ) {

	    	if ( trim( $sheet->getCellByColumnAndRow( $column, 1 )->getValue() ) == 'Title' ) { $title_column = $column; }
        	if ( trim( $sheet->getCellByColumnAndRow( $column, 1 )->getValue() ) == 'Author' ) { $author_column = $column; }
        	if ( trim( $sheet->getCellByColumnAndRow( $column, 1 )->getValue() ) == 'Review' ) { $review_column = $column; }
        	if ( trim( $sheet->getCellByColumnAndRow( $column, 1 )->getValue() ) == 'Score' ) { $score_column = $column; }
        	if ( trim( $sheet->getCellByColumnAndRow( $column, 1 )->getValue() ) == 'Email' ) { $email_column = $column; }
        	if ( trim( $sheet->getCellByColumnAndRow( $column, 1 )->getValue() ) == 'Product Name' ) { $name_column = $column; }
        	if ( trim( $sheet->getCellByColumnAndRow( $column, 1 )->getValue() ) == 'Categories' ) { $categories_column = $column; }
	
	        foreach ( $review_elements as $review_element ) {

        	    if ( trim( $sheet->getCellByColumnAndRow( $column, 1 )->getValue() ) == $review_element->name ) { $review_element->column = $column;}
        	}
	    }

	    $title_column = ! empty( $title_column ) ? $title_column : -1;
	    $author_column = ! empty( $author_column ) ? $author_column : -1;
	    $review_column = ! empty( $review_column ) ? $review_column : -1;
	    $score_column = ! empty( $score_column ) ? $score_column : -1;
	    $email_column = ! empty( $email_column ) ? $email_column : -1;
	    $name_column = ! empty( $name_column ) ? $name_column : -1;
	    $categories_column = ! empty( $categories_column ) ? $categories_column : -1;
	
	    // Put the spreadsheet data into a multi-dimensional array to facilitate processing
	    $highest_row = $sheet->getHighestRow();
	    for ( $row = 2; $row <= $highest_row; $row++ ) {
	        for ( $column = 1; $column <= $highest_column_index; $column++ ) {
	            $data[$row][$column] = $sheet->getCellByColumnAndRow( $column, $row )->getValue();
	        }
	    }
	
	    // Create the query to insert the products one at a time into the database and then run it
	    foreach ( $data as $review ) {
	        
	        // Create an array of the values that are being inserted for each review
	     	$post = array();
	     	$review_element_values = array();
	        foreach ( $review as $col_index => $value ) {

	            if ( $col_index == $title_column ) { $post['post_title'] = esc_sql( $value ); }
            	elseif ( $col_index == $review_column ) {$post['post_content'] = esc_sql( $value ); }
            	elseif ( $col_index == $name_column ) { $product_name = esc_sql( $value ); }
            	elseif ( $col_index == $author_column ) { $author = esc_sql( $value ); }
            	elseif ( $col_index == $score_column ) { $score = esc_sql( $value ); }
            	elseif ( $col_index == $email_column ) { $email = esc_sql( $value ); }
            	elseif ( $col_index == $categories_column ) { $post_categories = explode( ',', esc_sql( $value ) ); }
            	else {

            		foreach ( $review_elements as $review_element ) {
            			if ( $col_index == $review_element->column ) { $review_element_values[ $review_element->name ] = esc_sql( $value ); }
            		}
            	}
	        }

	        $post['post_status'] = 'publish';
        	$post['post_type'] = 'urp_review';

        	$post_id = wp_insert_post( $post );

        	if ($post_id != 0) {

        		if ( ! empty( $product_name ) ) { update_post_meta( $post_id, "EWD_URP_Product_Name", $product_name ); }
            	if ( ! empty( $author ) ) { update_post_meta( $post_id, "EWD_URP_Post_Author", $author ); }
            	if ( ! empty( $score ) ) { update_post_meta( $post_id, "EWD_URP_Post_Email", $score ); }
            	if ( ! empty( $email ) ) { update_post_meta( $post_id, "EWD_URP_Email_Confirmed", "Yes" ); }

            	if ( ! empty( $post_categories ) ) {
            	    
            		$category_ids = array();
            	    foreach ( $post_categories as $category ) {
            	        
            	        $term = term_exists( $category, EWD_URP_REVIEW_CATEGORY_TAXONOMY );
            	        if ( ! empty( $term ) ) { $category_ids[] = (int) $term['term_id']; }
            	    }
            	}
            	if ( isset( $category_ids ) and is_array( $category_ids ) ) { wp_set_object_terms( $post_id, $category_ids, EWD_URP_REVIEW_CATEGORY_TAXONOMY ); }
				
				$review_item_count = 0;
            	$total_score = 0;

            	foreach ( $review_elements as $review_element ) {

            		if ( empty( $review_element->column ) ) { continue; }

            		if ( empty( $review_element_values[ $review_element->name ] ) ) { continue; }

            	    if ( $review_element->type == "reviewitem" ) { $total_score += $review_element_values[ $review_element->name ]; $review_item_count++; }
            	    
            	    update_post_meta($post_id, "EWD_URP_" . $review_element->name, $review_element_values[ $review_element->name ]);
            	}

	            if ( $ewd_urp_controller->settings->get_setting( 'indepth-reviews' ) ) {
	                
	                $score = $review_item_count != 0 ? $total_score / $review_item_count : 0;
	            }

	            update_post_meta( $post_id, "EWD_URP_Overall_Score", $score );

			}
	
	        unset( $post );
        	unset( $product_name );
        	unset( $author );
        	unset( $score );
        	unset( $post_categories );
        	unset( $category_ids );
	    }

	    $this->status = true;
		$this->message = __( 'Reviews added successfully.', 'ultimate-reviews' );

		add_action( 'admin_notices', array( $this, 'display_notice' ) );
	}

	function handle_spreadsheet_upload() {
		  /* Test if there is an error with the uploaded spreadsheet and return that error if there is */
        if ( ! empty( $_FILES['ewd_urp_reviews_spreadsheet']['error'] ) ) {
                
            switch( $_FILES['ewd_urp_reviews_spreadsheet']['error'] ) {

                case '1':
                    $error = __( 'The uploaded file exceeds the upload_max_filesize directive in php.ini', 'ultimate-reviews' );
                    break;
                case '2':
                    $error = __( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form', 'ultimate-reviews' );
                    break;
                case '3':
                    $error = __( 'The uploaded file was only partially uploaded', 'ultimate-reviews' );
                    break;
                case '4':
                    $error = __( 'No file was uploaded.', 'ultimate-reviews' );
                    break;

                case '6':
                    $error = __( 'Missing a temporary folder', 'ultimate-reviews' );
                    break;
                case '7':
                    $error = __( 'Failed to write file to disk', 'ultimate-reviews' );
                    break;
                case '8':
                    $error = __( 'File upload stopped by extension', 'ultimate-reviews' );
                    break;
                case '999':
                    default:
                    $error = __( 'No error code avaiable', 'ultimate-reviews' );
            }
        }
        /* Make sure that the file exists */
        elseif ( empty($_FILES['ewd_urp_reviews_spreadsheet']['tmp_name']) || $_FILES['ewd_urp_reviews_spreadsheet']['tmp_name'] == 'none' ) {
                $error = __( 'No file was uploaded here..', 'ultimate-reviews' );
        }
        /* Move the file and store the URL to pass it onwards*/
        /* Check that it is a .xls or .xlsx file */ 
        if ( ! isset( $error ) && ! isset($_FILES['ewd_urp_reviews_spreadsheet']['name'] ) or ( ! preg_match("/\.(xls.?)$/", $_FILES['ewd_urp_reviews_spreadsheet']['name'] ) and ! preg_match( "/\.(csv.?)$/", $_FILES['ewd_urp_reviews_spreadsheet']['name'] ) ) ) {
            $error = __( 'File must be .csv, .xls or .xlsx', 'ultimate-reviews' );
        }
        else {
            $filename = basename( $_FILES['ewd_urp_reviews_spreadsheet']['name'] );
            $filename = mb_ereg_replace( "([^\w\s\d\-_~,;\[\]\(\).])", '', $filename );
            $filename = mb_ereg_replace ("([\.]{2,})", '', $filename );

            //for security reason, we force to remove all uploaded file
            $target_path = EWD_URP_PLUGIN_DIR . "/user-sheets/";

            if( ! is_dir( $target_path ) and ! mkdir( $target_path, 0755, true)) {
              $error = __('Unable to create directory, please try again!', 'ultimate-reviews' );
            }

            $target_path = $target_path . $filename;

            if ( ! move_uploaded_file($_FILES['ewd_urp_reviews_spreadsheet']['tmp_name'], $target_path ) ) {
                $error = __( 'There was an error uploading the file, please try again!', 'ultimate-reviews' );
            }
            else {
                $excel_file_name = $filename;
            }
        }

        /* Pass the data to the appropriate function in Update_Admin_Databases.php to create the products */
        if ( ! isset( $error ) ) {
                $update = array( "message_type" => "Success", "filename" => $excel_file_name );
        }
        else {
                $update = array( "message_type" => "Error", "message" => $error );
        }

        return $update;
	}

	public function import_woocommerce_reviews() {
		global $ewd_urp_controller;

		$args = array( 
			'post_type' => 'product', 
			'posts_per_page' => -1 
		);

		$query = new WP_Query( $args );
		$wc_products = $query->posts;
	
		foreach ( $wc_products as $wc_product ) {

			$args = array(
				'post_id' => $wc_product->ID, 
				'orderby' => 'comment_ID', 
				'order' => 'ASC'
			);

			$comments = get_comments( $args );

			$conversion_ids = array();
			$comment_data = array();
	
			foreach ( $comments as $comment ) {

				if ( $comment->comment_parent == 0 ) {

					$product_name = $wc_product->post_title;
	
					if ($comment->comment_approved) {

						$status = "publish";
						$email_confirmed = "Yes";
					}
					else {
						$status = "draft";
						$email_confirmed = "No";
					}
	
					$overall_score = get_comment_meta( $comment->comment_ID, "rating", true );
					$overall_score = round( ( $ewd_urp_controller->settings->get_setting( 'maximum-score' ) / 5 ) * $overall_score, 2 );
	
					$post = array(
						'post_type' => 'urp_review',
						'post_status' => $status,
						'post_content' => $comment->comment_content,
						'post_title' => "Review of " . $product_name,
						'post_date' => $comment->comment_date
					);

					$review_id = wp_insert_post($post);
					$conversion_ids[ $comment->comment_ID ] = $review_id;
	
					update_post_meta( $review_id, 'EWD_URP_Review_Karma', 0 );
					update_post_meta( $review_id, 'EWD_URP_Email_Confirmed', $Email_Confirmed );
					update_post_meta( $review_id, 'EWD_URP_Product_Name', $product_name );
					update_post_meta( $review_id, 'EWD_URP_Post_Author', $comment->comment_author );
	
					update_post_meta( $review_id, "EWD_URP_Overall_Score", $Overall_Score );
				}
				else {
					if ( isset( $conversion_ids[ $comment->comment_parent ] ) ) {
						$comment_post_ID = $conversion_ids[ $comment->comment_parent ];
						$parent_id = 0;
					}
					else {
						$comment_post_ID = $comment_data[$comment->comment_parent]['Post_ID'];
						$parent_id = $comment_data[$comment->comment_parent]['Comment_ID'];
					}
	
					$comment_args = array(
						'comment_post_ID' => $comment_post_ID,
						'comment_author' => $comment->comment_author,
						'comment_author_email' => $comment->comment_author_email,
						'comment_author_url' => $comment->comment_author_url,
						'comment_content' => $comment->comment_content,
						'comment_type' => $comment->comment_type,
						'comment_parent' => $parent_id,
						'user_id' => $comment->user_id,
						'comment_author_IP' => $comment->comment_author_IP,
						'comment_agent' => $comment->comment_agent,
						'comment_date' => $comment->comment_date,
						'comment_approved' => $comment->comment_approved
					);
	
					$new_comment_id = wp_insert_comment( $comment_args );
	
					$comment_data[$comment->comment_ID]['Post_ID'] = $comment_post_ID;
					$comment_data[$comment->comment_ID]['Comment_ID'] = $new_comment_id;
				}
			}
		}

		$this->status = true;
		$this->message = __( 'WooCommerce reviews have been succesfully imported.', 'ultimate-reviews' );
	}

	public function display_notice() {

		if ( $this->status ) {

			echo "<div class='updated'><p>" . esc_html( $this->message ) . "</p></div>";
		}
		else {

			echo "<div class='error'><p>" . esc_html( $this->message ) . "</p></div>";
		}
	}

}