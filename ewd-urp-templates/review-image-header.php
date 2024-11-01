<?php if ( $this->review_image['is_url'] ) { ?>
  
  <img src="<?php echo esc_attr( $this->review_image['image'] ); ?>" class="ewd-urp-review-image" />

<?php }
else {

  echo $this->review_image['image'];
  
}
