<?php global $ewd_urp_controller; ?>

<?php if ( $ewd_urp_controller->settings->get_setting( 'link-to-post' ) ) { ?>
	<a href='<?php echo get_the_permalink( $this->review->ID ); ?>' class='ewd-urp-review-link'>
<?php } ?>

<div class='ewd-urp-review-title' id='ewd-urp-title-<?php echo esc_attr( $this->unique_id ); ?>-<?php echo esc_attr( $this->review->ID); ?>' data-postid='<?php echo esc_attr( $this->unique_id ); ?>-<?php echo esc_attr( $this->ID); ?>' itemprop='name'>
	<?php echo ( $ewd_urp_controller->settings->get_setting( 'display-woocommerce-verified' ) ? '<span class="ewd-urp-verified"></span>' : '' ); ?>
	<?php echo esc_html( $this->review->title ); ?> 
</div>

<?php if ( $ewd_urp_controller->settings->get_setting( 'link-to-post' ) ) { ?>
	</a>
<?php } ?>