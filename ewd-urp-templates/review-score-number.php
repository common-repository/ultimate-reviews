<div class='ewd-urp-review-score-number'>
		
	<?php echo round( $this->review->score, 1 ) . ( $ewd_urp_controller->settings->get_setting( 'review-style' ) == 'percentage' ? '%' : '/' . $ewd_urp_controller->settings->get_setting( 'maximum-score' ) ); ?>

</div>