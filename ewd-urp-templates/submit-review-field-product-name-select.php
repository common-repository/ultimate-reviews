<div class='form-field'>
	
	<label id='ewd-urp-review-product-name-label' class='ewd-urp-review-label'>
		<?php echo esc_html( $this->get_label( 'label-submit-product' ) ); ?>:
	</label>
	
	<select name='product_name' id='product_name'>

		<?php foreach ( $this->get_reviewable_product_names() as $product_name ) { ?>
			<option value='<?php echo esc_attr( $product_name ); ?>'><?php echo esc_html( $product_name ); ?></option>
		<?php } ?>

	</select>

</div>