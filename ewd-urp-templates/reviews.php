<div <?php echo ewd_format_classes( $this->classes ); ?> data-shortcodeid='<?php echo $this->shortcode_id; ?>'>

	<?php $this->print_shortcode_args(); ?>

	<?php $this->maybe_print_pagination( 'top' ); ?>

	<?php $this->maybe_print_filtering(); ?>

	<div class='ewd-urp-reviews-container' data-shortcodeid='<?php echo $this->shortcode_id; ?>'>
		
		<?php $this->print_reviews(); ?>

	</div>

	<?php $this->maybe_print_pagination( 'bottom' ); ?>

</div>