<div class='ewd-urp-reviews-nav' data-shortcodeid='<?php echo $this->shortcode_id; ?>'>
	<input type='hidden' name='current_page' value='<?php echo esc_attr( $this->current_page ); ?>' id='ewd-urp-current-page' data-shortcodeid='<?php echo esc_attr( $this->shortcode_id ); ?>' />
	<input type='hidden' name='max_page' value='<?php echo esc_attr( $this->max_page ); ?>' id='ewd-urp-max-page' data-shortcodeid='<?php echo $this->shortcode_id; ?>' />
	<span class='displaying-num'><?php echo $this->review_count; ?></span> <?php echo $this->get_label( 'label-reviews' ); ?>
	<span class='pagination-links'>
		
		<a class='first-page ewd-urp-page-control <?php echo $this->current_page == 1 ? 'disabled' : ''; ?>' title='Go to the first page' data-controlvalue='first' data-shortcodeid='<?php echo esc_attr( $this->shortcode_id ); ?>'>&#171;</a>
		<a class='prev-page ewd-urp-page-control <?php echo $this->current_page == 1 ? 'disabled' : ''; ?>' title='Go to the previous page' data-controlvalue='back' data-shortcodeid='<?php echo esc_attr( $this->shortcode_id ); ?>'>&#8249;</a>
		
		<span class='paging-input'>
			<span class='current-page'>
				<?php echo esc_html( $this->current_page ); ?>
			</span>
			<?php _e(' of ', 'ultimate-reviews'); ?>
			<span class='total-pages'>
				<?php echo esc_html( $this->max_page ); ?>
			</span>
		</span>
		
		<a class='next-page ewd-urp-page-control <?php echo $this->current_page == $this->max_page ? 'disabled' : ''; ?>' title='Go to the next page'  data-controlvalue='next' data-shortcodeid='<?php echo esc_attr( $this->shortcode_id ); ?>'>&#8250;</a>
		<a class='last-page ewd-urp-page-control <?php echo $this->current_page == $this->max_page ? 'disabled' : ''; ?>' title='Go to the last page'  data-controlvalue='last' data-shortcodeid='<?php echo esc_attr( $this->shortcode_id ); ?>'>&#187;</a>
	</span>
</div>