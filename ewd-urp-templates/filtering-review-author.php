<?php if ( ! empty( $this->review_authors ) ) { ?>

	<div class='ewd-urp-filtering-review-author-div'>
		<label class='ewd-urp-filtering-label'><?php echo $this->get_label( 'label-filter-review-author' ); ?></label>
		<select class='ewd-filtering-review-author ewd-urp-filtering-select' data-shortcodeid='<?php echo esc_attr( $this->shortcode_id ); ?>'>
			<option value=''><?php echo $this->get_label( 'label-filter-all' ); ?></option>
			<?php foreach ( $this->review_authors as $review_author ) { ?><option value='<?php echo esc_attr( $review_author ); ?>'><?php echo esc_html( $review_author ); ?></option> <?php } ?>
		</select>
	</div>
<?php } ?>