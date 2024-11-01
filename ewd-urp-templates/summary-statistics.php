<div class='ewd-urp-summary-statistics-div' data-shortcodeid='<?php echo esc_attr( $this->shortcode_id ); ?>'>

	<div class='ewd-urp-summary-statistics-header'>

		<div class='ewd-urp-summary-product-name'> <?php _e( 'Summary for ', 'ultimate-reviews' ); ?> <?php echo esc_html( $this->current_product ); ?></div>

		<div class='ewd-urp-summary-average-score'>
			
			<?php echo esc_html( $this->get_label( 'label-summary-average-score' ) ); ?>: <?php echo $this->get_average_score(); ?> (<?php echo is_array( $this->summary_statistics[ $this->current_product ]['scores'] ) ? array_sum( $this->summary_statistics[ $this->current_product ]['scores'] ) : ''; ?> <?php echo esc_html( $this->get_label( 'label-summary-ratings' ) ); ?>) 
		</div>

	</div>

	<?php $this->maybe_print_summary_graphic(); ?>

	<?php $this->maybe_print_score_bars(); ?>

	<?php $this->maybe_print_clear_score_filter(); ?>

</div>