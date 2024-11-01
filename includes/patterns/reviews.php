<?php
/**
 * Reviews
 */
return array(
    'title'       =>	__( 'Reviews', 'ultimate-reviews' ),
    'description' =>	_x( 'Adds your reviews.', 'Block pattern description', 'ultimate-reviews' ),
    'categories'  =>	array( 'ewd-urp-block-patterns' ),
    'content'     =>	'<!-- wp:group {"className":"ewd-urp-pattern-reviews"} -->
                        <div class="wp-block-group ewd-urp-pattern-reviews"><!-- wp:ultimate-reviews/ewd-urp-display-reviews-block /--></div>
                        <!-- /wp:group -->',
);
