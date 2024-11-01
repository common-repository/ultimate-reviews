<div class='form-field'>

	<label id='ewd-urp-review-category' class='ewd-urp-review-label'>
		<?php _e( 'Review Category', 'ultimate-reviews' ); ?>: 
	</label>

	<select name='review_category' id='review_category' <?php echo $this->element_required(); ?> >
		
		<option></option>

		<?php foreach ( $this->get_review_categories() as $review_category ) { ?>
			
			<option value='<?php echo $review_category->term_id; ?>' <?php echo ( ! empty( $_POST['review_category'] ) and $_POST['review_category'] == $review_category->term_id ? 'selected' : '' ); ?> ><?php echo esc_html( $review_category->name ); ?></option>
		<?php } ?>

	</select>

	<div id='ewd-urp-category-explanation' class='ewd-urp-category-explanation'>
		
		<label for='explanation'></label>

		<span>
			<?php _e( 'The category that the review will be listed under.', 'ultimate-reviews' ); ?>
		</span>

	</div>

</div>