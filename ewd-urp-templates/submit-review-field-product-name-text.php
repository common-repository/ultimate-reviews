<div class='form-field'>

	<label id='ewd-urp-review-product-name-label' class='ewd-urp-review-label'>
		<?php echo esc_html( $this->get_label( 'label-submit-product' ) ); ?>:
	</label>
	
	<input name='product_name' id='product_name' type='text' class='ewd-urp-product-name-text-input' value='<?php echo ( ! empty( $_POST['product_name'] ) ? esc_attr( sanitize_text_field( $_POST['product_name'] ) ) : '' ); ?>' size='60' <?php echo $this->element_required(); ?> />
	
	<div id='ewd-urp-restrict-product-names-message'></div>

</div>