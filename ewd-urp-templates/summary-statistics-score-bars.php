<?php global $ewd_urp_controller; ?>

<?php for ( $i = $ewd_urp_controller->settings->get_setting( 'maximum-score'); $i >= 1; $i-- ) { ?>

	<?php if ( $ewd_urp_controller->settings->get_setting( 'summary-clickable' ) ) { ?>
		
		<div class='<?php echo ( $this->summary_statistics[ $this->current_product ]['scores'][ $i ] ? 'ewd-urp-summary-clickable' : 'ewd-urp-summary-non-clickable' ); ?>' data-reviewscore='<?php echo $i; ?>' data-shortcodeid='<?php echo esc_attr( $this->shortcode_id ); ?>'>
	<?php } ?>

	<div class='ewd-urp-summary-score-value'><?php echo $i; ?></div>
	
	<div class='ewd-urp-standard-summary-graphic-sub-group'>
		<div class='ewd-urp-standard-summary-graphic-full-sub-group'  style='width: <?php echo $this->get_summary_subscorebar_width( $i ); ?>%'></div>
	</div>

	<div class='ewd-urp-summary-score-count'><?php echo $this->summary_statistics[ $this->current_product ]['scores'][ $i ]; ?></div>
	
	<?php if ( $ewd_urp_controller->settings->get_setting( 'summary-clickable' ) ) { ?>
		</div>
	<?php } ?>
	
	<div class='ewd-urp-clear'></div>
<?php } ?>