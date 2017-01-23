<?php
/**
 * Class RelaxCommentFiltersTest
 *
 * @package Required
 */

namespace Required;

use WP_UnitTestCase;

class RelaxCommentFiltersTest extends WP_UnitTestCase {

	protected static $post_id;
	protected static $subscriber_user_id;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$post_id = self::factory()->post->create();
		self::$subscriber_user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
	}

	public function test_filter_is_added() {
		global $wp_filter;

		$this->assertTrue( has_filter( 'pre_comment_content' ) );
		$this->assertInstanceOf( 'WP_Hook', $wp_filter['pre_comment_content'] );
		$this->assertNotEmpty( $wp_filter['pre_comment_content'][10] );
		$callbacks = array_keys( $wp_filter['pre_comment_content'][10] );
		$this->assertContains( 'wp_filter_post_kses', $callbacks );
		$this->assertNotContains( 'wp_filter_kses', $callbacks );
	}

	public function test_unknown_user_can_add_images() {
		wp_set_current_user( 0 );

		$comment_content = 'Hello World <img src="https://example.org/image.png">';

		$comment_data = $this->generate_comment_data( $comment_content );

		$comment_id = wp_new_comment( wp_slash( $comment_data ), true );
		$this->assertNotWPError( $comment_id );

		$comment = get_comment( $comment_id );
		$this->assertSame( $comment_content, $comment->comment_content );
	}

	public function test_unknown_user_cannot_add_script_and_style_tags() {
		wp_set_current_user( 0 );

		$comment_content = "Hello World \n <script>alert(1);</script> \n <style>a { color: transparent; }</style>";
		$comment_content_expected = "Hello World \n alert(1); \n a { color: transparent; }";

		$comment_data = $this->generate_comment_data( $comment_content );

		$comment_id = wp_new_comment( wp_slash( $comment_data ), true );
		$this->assertNotWPError( $comment_id );

		$comment = get_comment( $comment_id );
		$this->assertSame( $comment_content_expected, $comment->comment_content );
	}

	public function test_unknown_user_cannot_add_images_with_bad_protocols() {
		wp_set_current_user( 0 );

		$comment_content = 'Hello World <img src="javascript:alert(1)">';
		$comment_content_expected = 'Hello World <img src="alert(1)">';

		$comment_data = $this->generate_comment_data( $comment_content );

		$comment_id = wp_new_comment( wp_slash( $comment_data ), true );
		$this->assertNotWPError( $comment_id );

		$comment = get_comment( $comment_id );
		$this->assertSame( $comment_content_expected, $comment->comment_content );
	}

	public function test_subscriber_can_add_images() {
		wp_set_current_user( self::$subscriber_user_id );

		$comment_content = 'Hello World <img src="https://example.org/image.png">';

		$comment_data = $this->generate_comment_data( $comment_content, self::$subscriber_user_id );

		$comment_id = wp_new_comment( wp_slash( $comment_data ), true );
		$this->assertNotWPError( $comment_id );

		$comment = get_comment( $comment_id );
		$this->assertSame( $comment_content, $comment->comment_content );
	}

	public function test_subscriber_can_use_style_attribute() {
		wp_set_current_user( self::$subscriber_user_id );

		$comment_content = '<p style="color:red">Hello World</p>';

		$comment_data = $this->generate_comment_data( $comment_content, self::$subscriber_user_id );

		$comment_id = wp_new_comment( wp_slash( $comment_data ), true );
		$this->assertNotWPError( $comment_id );

		$comment = get_comment( $comment_id );
		$this->assertSame( $comment_content, $comment->comment_content );
	}

	public function test_subscriber_cannot_add_on_attributes() {
		wp_set_current_user( self::$subscriber_user_id );

		$comment_content = 'Hello World <img src="https://example.org/image.png" onerror="alert(1);">';
		$comment_content_expected = 'Hello World <img src="https://example.org/image.png">';

		$comment_data = $this->generate_comment_data( $comment_content, self::$subscriber_user_id );

		$comment_id = wp_new_comment( wp_slash( $comment_data ), true );
		$this->assertNotWPError( $comment_id );

		$comment = get_comment( $comment_id );
		$this->assertSame( $comment_content_expected, $comment->comment_content );
	}

	private function generate_comment_data( $comment_content, $user_id = 0 ) {
		if ( $user_id ) {
			$user = get_user_by( 'id', self::$subscriber_user_id );
		} else {
			$user = (object) [
				'ID'           => 0,
				'display_name' => 'Max',
				'user_email'   => 'max@example.org',
				'user_url'     => 'https://example.org',
			];
		}

		return $comment_data = [
			'comment_post_ID'      => self::$post_id,
			'comment_content'      => $comment_content,
			'comment_type'         => 'comment',
			'comment_author'       => $user->display_name,
			'comment_author_email' => $user->user_email,
			'comment_author_url'   => $user->user_url,
			'user_ID'              => $user->ID,
		];
	}
}
