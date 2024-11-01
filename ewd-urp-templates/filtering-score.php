<?php global $ewd_urp_controller; ?>
<div class='ewd-urp-filtering-product-name-div'>
	<label class='ewd-urp-filtering-label'><?php echo $this->get_label( 'label-filter-review-score' ); ?></label>
	<span class='ewd-urp-score-range'>1 - <?php echo $ewd_urp_controller->settings->get_setting( 'maximum-score' ); ?></span>
	<div class='ewd-urp-review-score-filter' data-shortcodeid='<?php echo esc_attr( $this->shortcode_id ); ?>'></div>
</div>