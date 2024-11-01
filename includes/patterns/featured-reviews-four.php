<?php
/**
 * Featured Reviews - 4 Per Row
 */
return array(
    'title'       =>	__( 'Featured Reviews - 4 Per Row', 'ultimate-reviews' ),
    'description' =>	_x( 'Adds a list of featured reviews, organized 4 per row. Displays just the reviews, without summaries, filtering, comments etc.', 'Block pattern description', 'ultimate-reviews' ),
    'categories'  =>	array( 'ewd-urp-block-patterns' ),
    'content'     =>	'<!-- wp:group {"className":"ewd-urp-pattern-featured-reviews ewd-urp-pattern-featured-four"} -->
                        <div class="wp-block-group ewd-urp-pattern-featured-reviews ewd-urp-pattern-featured-four"><!-- wp:ultimate-reviews/ewd-urp-display-reviews-block /--></div>
                        <!-- /wp:group -->',
);
