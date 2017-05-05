<?php
/**
 * Adds Tabs Widget.
 */
class HyunmooTabsWidget extends WP_Widget {
/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'tabs', // Base ID
			'Hyunmoo: Tabs Widget', // Name
			array( 'description' => __( 'Popular posts, recent post and comments.', 'hyunmoo' ) ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $post;
		
		extract($args);
		
		$posts = $instance['posts'];
		$comments = $instance['comments'];
		$tags_count = $instance['tags'];
		$show_popular_posts = isset($instance['show_popular_posts']) ? 'true' : 'false';
		$show_recent_posts = isset($instance['show_recent_posts']) ? 'true' : 'false';
		$show_comments = isset($instance['show_comments']) ? 'true' : 'false';
		$show_tags = isset($instance['show_tags']) ? 'true' : 'false';
		$orderby = $instance['orderby'];

		if(!$orderby) {
			$orderby = 'Highest Comments';
		}

		echo $before_widget;
		?>
		<div class="tab-holder">
			<ul id="tabs" class="tabset tabs">
				<?php if($show_popular_posts == 'true'): ?>
				<li><a href="#tab-popular"><?php echo __('Popular', 'Avada'); ?></a></li>
				<?php endif; ?>
				<?php if($show_recent_posts == 'true'): ?>
				<li><a href="#tab-recent"><?php echo __('Recent', 'Avada'); ?></a></li>
				<?php endif; ?>
				<?php if($show_comments == 'true'): ?>
				<li><a href="#tab-comments"><i class="fa fa-comment"></i></a></li>
				<?php endif; ?>
                <div style="clear:both"></div>
			</ul>
			<?php if($show_popular_posts == 'true'): ?>
			<div id="tab-popular" class="tab tab_content" style="display: none;">
				<?php
				if($orderby == 'Highest Comments') {
					$order_string = '&orderby=comment_count';
				} else {
					$order_string = '&meta_key=avada_post_views_count&orderby=meta_value_num';
				}
				$popular_posts = new WP_Query('showposts='.$posts.$order_string.'&order=DESC');
				if($popular_posts->have_posts()): ?>
				<ul class="news-list">
					<?php while($popular_posts->have_posts()): $popular_posts->the_post(); ?>
					<li>
						<?php if(has_post_thumbnail()): ?>
						<div class="image">
							<a href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail('thumbnail'); ?>
							</a>
						</div>
						<?php endif; ?>
						<div class="post-holder">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							<div class="meta">
								<?php the_date(); ?>
							</div>
						</div>
                        <div style="clear:both"></div>
					</li>
					<?php endwhile; ?>
				</ul>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<?php if($show_recent_posts == 'true'): ?>
			<div id="tab-recent" class="tab tab_content" style="display: none;">
				<?php
				$recent_posts = new WP_Query('showposts='.$posts);
				if($recent_posts->have_posts()):
				?>
				<ul class="news-list">
					<?php while($recent_posts->have_posts()): $recent_posts->the_post(); ?>
					<li>
						<?php if(has_post_thumbnail()): ?>
						<div class="image">
							<a href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail('thumbnail'); ?>
							</a>
						</div>
						<?php endif; ?>
						<div class="post-holder">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							<div class="meta">
								<?php the_date(); ?>
							</div>
						</div>
						<div style="clear:both"></div>
					</li>
					<?php endwhile; ?>
				</ul>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<?php if($show_comments == 'true'): ?>
			<div id="tab-comments" class="tab tab_content" style="display: none;">
				<ul class="news-list">
					<?php
					$number = $instance['comments'];
					global $wpdb;
					$recent_comments = "SELECT DISTINCT ID, post_title, post_password, comment_ID, comment_post_ID, comment_author, comment_author_email, comment_date_gmt, comment_approved, comment_type, comment_author_url, SUBSTRING(comment_content,1,110) AS com_excerpt FROM $wpdb->comments LEFT OUTER JOIN $wpdb->posts ON ($wpdb->comments.comment_post_ID = $wpdb->posts.ID) WHERE comment_approved = '1' AND comment_type = '' AND post_password = '' ORDER BY comment_date_gmt DESC LIMIT $number";
					$the_comments = $wpdb->get_results($recent_comments);
					foreach($the_comments as $comment) { ?>
					<li>
						<div class="image">
							<a>
							<?php echo get_avatar($comment, '52'); ?>
							</a>
						</div>
						<div class="post-holder">
							<p><?php echo strip_tags($comment->comment_author); ?> <?php _e('says', 'Avada'); ?>:</p>
							<div class="meta">
								<a class="comment-text-side" href="<?php echo get_permalink($comment->ID); ?>#comment-<?php echo $comment->comment_ID; ?>" title="<?php echo strip_tags($comment->comment_author); ?> on <?php echo $comment->post_title; ?>">
								<?php 
									if(strlen(strip_tags($comment->com_excerpt))>30)
										echo substr(strip_tags($comment->com_excerpt), 0, 30)." ...";
									else
										echo strip_tags($comment->com_excerpt);
								?></a>
							</div>
						</div>
						<div style="clear:both"></div>
					</li>
					<?php } ?>
				</ul>
			</div>
			<?php endif; ?>
		</div>
        <script type="text/javascript">
		jQuery(document).ready(function($){
			$(".tab-holder").tabs();
		});
		</script>
		<?php
		echo $after_widget;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$defaults = array('posts' => 3, 'comments' => '3', 'tags' => 20, 'show_popular_posts' => 'on', 'show_recent_posts' => 'on', 'show_comments' => 'on', 'show_tags' =>  'on', 'orderby' => 'Highest Comments');
		$instance = wp_parse_args((array) $instance, $defaults); ?>
		<p>
			<label for="<?php echo $this->get_field_id('orderby'); ?>">Popular Posts Order By:</label> 
			<select id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>" class="widefat" style="width:100%;">
				<option <?php if ('Highest Comments' == $instance['orderby']) echo 'selected="selected"'; ?>>Highest Comments</option>
				<option <?php if ('Highest Views' == $instance['orderby']) echo 'selected="selected"'; ?>>Highest Views</option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('posts'); ?>">Number of popular posts:</label>
			<input type="text" class="widefat" style="width: 30px;" id="<?php echo $this->get_field_id('posts'); ?>" name="<?php echo $this->get_field_name('posts'); ?>" value="<?php echo $instance['posts']; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('tags'); ?>">Number of recent posts:</label>
			<input type="text" class="widefat" style="width: 30px;" id="<?php echo $this->get_field_id('tags'); ?>" name="<?php echo $this->get_field_name('tags'); ?>" value="<?php echo $instance['tags']; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('comments'); ?>">Number of comments:</label>
			<input type="text" class="widefat" style="width: 30px;" id="<?php echo $this->get_field_id('comments'); ?>" name="<?php echo $this->get_field_name('comments'); ?>" value="<?php echo $instance['comments']; ?>" />
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked($instance['show_popular_posts'], 'on'); ?> id="<?php echo $this->get_field_id('show_popular_posts'); ?>" name="<?php echo $this->get_field_name('show_popular_posts'); ?>" /> 
			<label for="<?php echo $this->get_field_id('show_popular_posts'); ?>">Show popular posts</label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked($instance['show_recent_posts'], 'on'); ?> id="<?php echo $this->get_field_id('show_recent_posts'); ?>" name="<?php echo $this->get_field_name('show_recent_posts'); ?>" /> 
			<label for="<?php echo $this->get_field_id('show_recent_posts'); ?>">Show recent posts</label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked($instance['show_comments'], 'on'); ?> id="<?php echo $this->get_field_id('show_comments'); ?>" name="<?php echo $this->get_field_name('show_comments'); ?>" /> 
			<label for="<?php echo $this->get_field_id('show_comments'); ?>">Show comments</label>
		</p>

	<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$instance['posts'] = $new_instance['posts'];
		$instance['comments'] = $new_instance['comments'];
		$instance['tags'] = $new_instance['tags'];
		$instance['show_popular_posts'] = $new_instance['show_popular_posts'];
		$instance['show_recent_posts'] = $new_instance['show_recent_posts'];
		$instance['show_comments'] = $new_instance['show_comments'];
		$instance['show_tags'] = $new_instance['show_tags'];
		$instance['orderby'] = $new_instance['orderby'];

		return $instance;
	}
}
?>