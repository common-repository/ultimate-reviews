<div class='form-field'>
	
	<label id='ewd-urp-review-title' class='ewd-urp-review-label'>
		<?php _e('Review Image', 'ultimate-reviews'); ?>: 
	</label>

	<input type='file' name='post_image' id='post_image' accept='.jpg,.png' <?php echo $this->element_required(); ?> />

	<div id='ewd-urp-image-explanation' class='ewd-urp-review-explanation'>
	
		<label for='explanation'></label>

		<span>
			<?php _e('The image that should be associated with your review', 'ultimate-reviews'); ?>
		</span>

	</div>

</div>