<?php foreach ( $this->get_indepth_fields() as $indepth_field ) { ?>

	<div class='ewd-urp-category-field'>

		<div class='ewd-urp-category-score'>

			<div class='ewd-urp-category-score-label'>
				<?php echo esc_html( $indepth_field->name ) . ( $indepth_field->type == 'ReviewItem' or $indepth_field->type == '' ? esc_html( $this->get_label( 'label-score' ) ) : '' ); ?>
			</div>

			<div class='ewd-urp-category-score-number'>
				<?php echo esc_html( $this->get_review_field_score( $indepth_field->name ) ); ?>
			</div>

		</div>

		<?php if ( $indepth_field->explanation ) { ?>

			<div class='ewd-urp-category-explanation'>
	
				<div class='ewd-urp-category-explanation-label'>
					<?php echo esc_html( $indepth_field->name ) . ' ' . esc_html( $this->get_label( 'label-explanation' ) ); ?>
				</div>
	
				<div class='ewd-urp-category-explanation-text'>
					<?php echo esc_html( $this->get_review_field_explanation( $indepth_field->name ) ); ?>
				</div>
	
			</div>
		<?php } ?>

	</div>

<?php } ?>