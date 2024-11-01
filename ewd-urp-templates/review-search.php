<form action='#' method='post' id='urp-ajax-form' class='pure-form pure-form-aligned'>
    <input type='hidden' name='urp-input' value='Search'>
    <div id='ewd-urp-jquery-ajax-search' class='pure-control-group ui-front' style='position:relative;'>
        <label  id='urp-ajax-search-lbl' class='ewd-urp-field-label ewd-urp-bold'><?php echo esc_html( $this->get_label( 'label-search-reviews' ) ); ?>:</label>
        <input type='text' id='ewd-urp-ajax-text-input' class='ewd-urp-text-input' name='Question ' placeholder='<?php echo esc_attr( $this->get_label( 'label-search-reviews' ) ); ?>...' data-shortcodeid='<?php echo esc_attr( $this->shortcode_id ); ?>'>
    </div>
</form>