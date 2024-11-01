<?php foreach ( $this->custom_filters as $field_name => $custom_filter ) { ?>
	
	<?php $unique_filter_values = array_unique( $custom_filter ); ?>
	<?php natcasesort( $unique_filter_values ); ?>
	<?php if ( ! empty( $unique_filter_values ) ) { ?>
	
		<div class='ewd-urp-filtering-custom-filter-div'>
			<label class='ewd-urp-filtering-label'><?php echo esc_html( $field_name ); ?></label>
			<select class='ewd-urp-custom-filter ewd-urp-filtering-select' data-fieldname='<?php echo esc_attr( $field_name ); ?>' data-shortcodeid='<?php echo esc_attr( $this->shortcode_id ); ?>'>
				<option value=''><?php echo $this->get_label( 'label-filter-all' ); ?></option>
				<?php foreach ( $unique_filter_values as $filter_value ) { ?><option value='<?php echo esc_attr( $filter_value ); ?>'><?php echo esc_html( $filter_value ); ?></option> <?php } ?>
			</select>
		</div>
	<?php } ?>
<?php } ?>