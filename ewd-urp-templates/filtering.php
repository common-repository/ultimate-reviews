<div class='ewd-urp-filtering' data-shortcodeid='<?php echo esc_attr( $this->shortcode_id ); ?>'>
	
	<div class='ewd-urp-filtering-toggle ewd-urp-filtering-toggle-downcaret'>
		<?php echo $this->get_label( 'label-filter-button' ); ?>
	</div>

	<div class='ewd-urp-filtering-controls ewd-urp-hidden'>

		<?php $this->maybe_print_product_name_filtering(); ?>

		<?php $this->maybe_print_review_author_filtering(); ?>

		<?php $this->maybe_print_score_filtering(); ?>

		<?php $this->maybe_print_custom_filtering(); ?>

	</div>
	
</div>