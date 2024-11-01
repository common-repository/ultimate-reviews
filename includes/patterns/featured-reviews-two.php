<?php
/**
 * Featured Reviews - 2 Per Row
 */
return array(
    'title'       =>	__( 'Featured Reviews - 2 Per Row', 'ultimate-reviews' ),
    'description' =>	_x( 'Adds a list of featured reviews, organized 2 per row. Displays just the reviews, without summaries, filtering, comments etc.', 'Block pattern description', 'ultimate-reviews' ),
    'categories'  =>	array( 'ewd-urp-block-patterns' ),
    'content'     =>	'<!-- wp:group {"className":"ewd-urp-pattern-featured-reviews ewd-urp-pattern-featured-two"} -->
                        <div class="wp-block-group ewd-urp-pattern-featured-reviews ewd-urp-pattern-featured-two"><!-- wp:ultimate-reviews/ewd-urp-display-reviews-block /--></div>
                        <!-- /wp:group -->',
);
