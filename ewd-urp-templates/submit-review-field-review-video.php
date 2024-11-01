<div class='form-field'>
	
	<label id='ewd-urp-review-video' class='ewd-urp-review-label'>
		<?php _e( 'Review Video', 'ultimate-reviews' ); ?>: 
	</label>
	
	<input type='URL' name='review_video' id='review_video' value='<?php echo ( ! empty( $_POST['review_video'] ) ? esc_attr( $_POST['review_video'] ) : '' ); ?>' <?php echo $this->element_required(); ?>/>

	<div id='ewd-urp-video-explanation' class='ewd-urp-review-explanation'>
	
		<label for='explanation'></label>

		<span>
			<?php _e( 'A link to a video for this review from an external site (YouTube, Vimeo, etc.).', 'ultimate-reviews' ); ?>
		</span>

	</div>

</div>