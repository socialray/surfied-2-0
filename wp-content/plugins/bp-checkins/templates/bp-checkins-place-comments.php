<?php if ( comments_open() && is_user_logged_in() ) : ?>
	
	<?php if( bp_checkins_places_was_posted_in_group() && !bp_checkins_places_can_publish_in_group() ):?>
		
		<p class="must-register-in-groupe"><?php printf( __('You must be a member of %s to check in this place or post a comment.','bp-checkins' ), bp_get_checkins_places_group_permalink());?></p>
		
	<?php else:?>
		
		<?php if( bp_checkins_places_is_live() && 'live' != bp_checkins_places_live_status(0) ):?>
			
			<p class="bpci-live-unavailable"><?php bp_checkins_places_live_status();?></p>
			
		<?php else:?>
	
			<?php
			$title_reply = apply_filters('bp_checkins_comment_form_title', __('<a href="#" class="add-checkin without">Check-in</a><a href="#" class="add-checkin with">Comment &amp; check-in</a>', 'bp-checkins') );
		
			?>
			
			<?php bp_get_checkins_places_live_end_date();?>
	
			<?php comment_form( array('title_reply' => $title_reply, 'comment_notes_after' => '' )) ?>
		
		<?php endif;?>
		
	<?php endif;?>

<?php else:?>
	
	<p class="must-log-in"><?php printf( __( 'You must be <a href="%s">logged in</a> to post a comment.' ), wp_login_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) ?></p>	

<?php endif; ?>

<?php
	if ( post_password_required() ) {
		echo '<h3 class="comments-header">' . __( 'Password Protected', 'bp-checkins' ) . '</h3>';
		echo '<p class="alert password-protected">' . __( 'Enter the password to view comments.', 'bp-checkins' ) . '</p>';
		return;
	}

	if ( is_page() && !have_comments() && !comments_open() && !pings_open() )
		return;

	if ( have_comments() ) :
		$num_comments = 0;
		$num_trackbacks = 0;
		foreach ( (array)$comments as $comment ) {
			if ( 'comment' != get_comment_type() )
				$num_trackbacks++;
			else
				$num_comments++;
		}
?>
	<div id="comments">

		<h3>
			<?php printf( _n( '<span>1</span> response to %2$s', '<span>%1$s</span> responses to %2$s', $num_comments, 'bp-checkins' ), number_format_i18n( $num_comments ), '<em>' . get_the_title() . '</em>' ) ?>
		</h3>

		<?php do_action( 'bp_before_blog_comment_list' ) ?>
		
		<ol class="commentlist">
			<?php wp_list_comments( array( 'callback' => 'bp_checkins_list_comments', 'type' => 'comment', 'reverse_top_level' => 'ASC' ) ) ?>
		</ol><!-- .comment-list -->

		<?php do_action( 'bp_after_blog_comment_list' ) ?>

		<?php if ( get_option( 'page_comments' ) ) : ?>
			<div class="comment-navigation paged-navigation">
				<?php paginate_comments_links() ?>
			</div>
		<?php endif; ?>

	</div><!-- #comments -->
<?php else : ?>

	<?php if ( pings_open() && !comments_open() && ( is_single() || is_page() ) ) : ?>
		<p class="comments-closed pings-open">
			<?php printf( __( 'Comments are closed, but <a href="%1$s" title="Trackback URL for this post">trackbacks</a> and pingbacks are open.', 'bp-checkins' ), trackback_url( '0' ) ) ?>
		</p>
	<?php elseif ( !comments_open() && ( is_single() || is_page() ) ) : ?>
		<p class="comments-closed">
			<?php _e( 'Comments are closed.', 'bp-checkins' ) ?>
		</p>
	<?php endif; ?>

<?php endif; ?>

<?php if ( !empty( $num_trackbacks ) ) : ?>
	<div id="trackbacks">
		<h3><?php printf( _n( '1 trackback', '%d trackbacks', $num_trackbacks, 'bp-checkins' ), number_format_i18n( $num_trackbacks ) ) ?></h3>

		<ul id="trackbacklist">
			<?php foreach ( (array)$comments as $comment ) : ?>

				<?php if ( 'comment' != get_comment_type() ) : ?>
					<li>
						<h5><?php comment_author_link() ?></h5>
						<em>on <?php comment_date() ?></em>
					</li>
 				<?php endif; ?>

			<?php endforeach; ?>
		</ul>

	</div>
<?php endif; ?>