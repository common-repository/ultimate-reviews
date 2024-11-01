<?php
/**
 * Featured Reviews - 3 Per Row
 */
return array(
    'title'       =>	__( 'Featured Reviews - 3 Per Row', 'ultimate-reviews' ),
    'description' =>	_x( 'Adds a list of featured reviews, organized 3 per row. Displays just the reviews, without summaries, filtering, comments etc.', 'Block pattern description', 'ultimate-reviews' ),
    'categories'  =>	array( 'ewd-urp-block-patterns' ),
    'content'     =>	'<!-- wp:group {"className":"ewd-urp-pattern-featured-reviews ewd-urp-pattern-featured-three"} -->
                        <div class="wp-block-group ewd-urp-pattern-featured-reviews ewd-urp-pattern-featured-three"><!-- wp:ultimate-reviews/ewd-urp-display-reviews-block /--></div>
                        <!-- /wp:group -->',
);
