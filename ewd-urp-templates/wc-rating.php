<?php
/**
 * Single Product Rating
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/rating.php.
 *
 *
 * @see 	    http://docs.woothemes.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     2.3.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $ewd_urp_controller;
global $product;

if ( $product->is_type( 'variation' ) ) { $post_data = get_post( $product->get_parent_id() ); }
else { $post_data = get_post( $product->get_id() ); }

$rating_count = $ewd_urp_controller->woocommerce->get_review_count( $post_data->post_title );
$average      = $ewd_urp_controller->woocommerce->get_aggregate_score( $post_data->post_title );

if ( $rating_count > 0 ) : ?>

	<div class="woocommerce-product-rating">
		<div class="star-rating" title="<?php printf( __( 'Rated %s out of %s', 'woocommerce' ), $average, $ewd_urp_controller->settings->get_setting( 'maximum-score' ) ); ?>">
			<span style="width:<?php echo ( ( $average / $ewd_urp_controller->settings->get_setting( 'maximum-score' ) ) * 100 ); ?>%">
				<strong class="rating"><?php echo esc_html( $average ); ?></strong>
				<?php printf( __( 'out of %s', 'woocommerce' ), $ewd_urp_controller->settings->get_setting( 'maximum-score' ) ); ?>
				<?php printf( _n( 'based on %s customer rating', 'based on %s customer ratings', $rating_count, 'woocommerce' ), $rating_count ); ?>
			</span>
		</div>
		<?php if ( comments_open() ) : ?><a href="#tab-reviews" class="woocommerce-review-link" rel="nofollow">(<?php printf( _n( '%s customer review', '%s customer reviews', $rating_count, 'woocommerce' ), $rating_count ); ?>)</a><?php endif ?>
	</div>

<?php endif; ?>