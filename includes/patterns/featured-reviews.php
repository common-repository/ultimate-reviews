<?php
/**
 * Featured Reviews
 */
return array(
    'title'       =>	__( 'Featured Reviews', 'ultimate-reviews' ),
    'description' =>	_x( 'Adds a list of featured reviews. Displays just the reviews, without summaries, filtering, comments etc.', 'Block pattern description', 'ultimate-reviews' ),
    'categories'  =>	array( 'ewd-urp-block-patterns' ),
    'content'     =>	'<!-- wp:group {"className":"ewd-urp-pattern-featured-reviews"} -->
                        <div class="wp-block-group ewd-urp-pattern-featured-reviews"><!-- wp:ultimate-reviews/ewd-urp-display-reviews-block /--></div>
                        <!-- /wp:group -->',
);
