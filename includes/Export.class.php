<?php

/**
 * Class to export reviews created by the plugin
 */

if ( !defined( 'ABSPATH' ) )
	exit;

if (!class_exists('ComposerAutoloaderInit4618f5c41cf5e27cc7908556f031e4d4')) {require_once EWD_URP_PLUGIN_DIR . '/lib/PHPSpreadsheet/vendor/autoload.php';}
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
class ewdurpExport {

	public function __construct() {
		add_action( 'admin_menu', array($this, 'register_install_screen' ));

		if ( isset( $_POST['ewd_urp_export'] ) ) { add_action( 'admin_menu', array($this, 'export_reviews' )); }

	}

	public function register_install_screen() {
		add_submenu_page( 
			'edit.php?post_type=urp_review', 
			'Export Menu', 
			'Export', 
			'manage_options', 
			'ewd-urp-export', 
			array($this, 'display_export_screen') 
		);
	}

	public function display_export_screen() {
		global $ewd_urp_controller;

		$export_permission = $ewd_urp_controller->permissions->check_permission( 'export' );

		?>
		<div class='wrap'>
			<h2>Export</h2>
			<?php if ( $export_permission ) { ?> 
				<form method='post'>
					<input type='submit' name='ewd_urp_export' value='Export to Spreadsheet' class='button button-primary' />
				</form>
			<?php } else { ?>
				<div class='ewd-urp-premium-locked'>
					<a href="https://www.etoilewebdesign.com/license-payment/?Selected=URP&Quantity=1&utm_source=urp_export" target="_blank">Upgrade</a> to the premium version to use this feature
				</div>
			<?php } ?>
		</div>
	<?php }

	public function export_reviews() {
		global $ewd_urp_controller;

		$review_elements = $ewd_urp_controller->settings->get_review_elements();

		// Instantiate a new PHPExcel object
		$spreadsheet = new Spreadsheet();
		// Set the active Excel worksheet to sheet 0
		$spreadsheet->setActiveSheetIndex(0);

		// Print out the regular review field labels
		$spreadsheet->getActiveSheet()->setCellValue( 'A1', 'Title' );
		$spreadsheet->getActiveSheet()->setCellValue( 'B1', 'Author' );
		$spreadsheet->getActiveSheet()->setCellValue( 'C1', 'Review' );
		$spreadsheet->getActiveSheet()->setCellValue( 'D1', 'Score' );
		$spreadsheet->getActiveSheet()->setCellValue( 'E1', 'Email' );
		$spreadsheet->getActiveSheet()->setCellValue( 'F1', 'Product Name' );
		$spreadsheet->getActiveSheet()->setCellValue( 'G1', 'Categories' );

		$column = 'H';
		if ( $ewd_urp_controller->settings->get_setting( 'indepth-reviews' ) ) {

			foreach ( $review_elements as $review_element ) {

				if ( $review_element->type != 'default' ) {

     				$spreadsheet->getActiveSheet()->setCellValue( $column . '1', $review_element->name );
    				$column++;
    			}
			}
		}

		//start while loop to get data
		$row_count = 2;
		$params = array(
			'posts_per_page' => -1,
			'post_type' => 'urp_review'
		);

		$reviews = get_posts( $params );
		foreach ( $reviews as $review ) {
    	 	
    	 	$author = get_post_meta( $review->ID, 'EWD_URP_Post_Author', true );
     		$score = get_post_meta( $review->ID, 'EWD_URP_Overall_Score', true );
     		$email = get_post_meta( $review->ID, 'EWD_URP_Post_Email', true );
     		$product_name = get_post_meta( $review->ID, 'EWD_URP_Product_Name', true );

    	 	$categories = get_the_terms( $review->ID, EWD_URP_REVIEW_CATEGORY_TAXONOMY );
     		$category_string = '';
     		if ( is_array( $categories ) ) {

     			foreach ( $categories  as $category ) {

     				$category_string .= $category->name . ",";
     			}
     			$category_string = substr( $category_string, 0, -1 );
     		}
     		else { $category_string = ""; }

    	 	$spreadsheet->getActiveSheet()->setCellValue( 'A' . $row_count, $review->post_title );
			$spreadsheet->getActiveSheet()->setCellValue( 'B' . $row_count, $author );
			$spreadsheet->getActiveSheet()->setCellValue( 'C' . $row_count, $review->post_content );
			$spreadsheet->getActiveSheet()->setCellValue( 'D' . $row_count, $score );
			$spreadsheet->getActiveSheet()->setCellValue( 'E' . $row_count, $email );
			$spreadsheet->getActiveSheet()->setCellValue( 'F' . $row_count, $product_name );
			$spreadsheet->getActiveSheet()->setCellValue( 'G' . $row_count, $category_string );

			$column = 'H';
			if ( $ewd_urp_controller->settings->get_setting( 'indepth-reviews' ) ) {

				foreach ( $review_elements as $review_element ) {

					if ( $review_element->type != 'default' ) {

    					$spreadsheet->getActiveSheet()->setCellValue( $column . $row_count, get_post_meta( $review->ID, "EWD_URP_" . $review_element->name, true ) );
   						$column++;
   					}
				}
			}
			
    		$row_count++;

    		unset( $category_string );
		}

		// Redirect output to a client’s web browser (Excel5)
		if ( ! isset( $format_type ) == "csv" ) {

			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="reviews_export.csv"');
			header('Cache-Control: max-age=0');
			$objWriter = new Csv($spreadsheet);
			$objWriter->save('php://output');
			die();
		}
		else {

			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="reviews_export.xls"');
			header('Cache-Control: max-age=0');
			$objWriter = new Xls($spreadsheet);
			$objWriter->save('php://output');
			die();
		}
	}
}


