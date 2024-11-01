<div class='ewd-urp-filtering-product-name-div'>
	<label class='ewd-urp-filtering-label'><?php echo $this->get_label( 'label-filter-product-name' ); ?>:</label>
	<select class='ewd-filtering-product-name ewd-urp-filtering-select' data-shortcodeid='<?php echo esc_attr( $this->shortcode_id ); ?>'>
		<option value=''><?php echo $this->get_label( 'label-filter-all' ); ?></option>
		<?php foreach ( $this->product_names as $product_name ) { ?><option value='<?php echo esc_attr( $product_name ); ?>'><?php echo esc_html( $product_name ); ?></option> <?php } ?>
	</select>
</div>