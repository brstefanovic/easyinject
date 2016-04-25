<?php

/**
 * Class EI_Main
 * Handles all the plugin functionality.
 */
class EI_Main {
	/**
	 * EI_Main constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'meta_box_display' ) );
		add_action( 'save_post', array( $this, 'meta_box_save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'easyinject_scripts'), 10, 1 );
		add_action( 'wp_print_scripts', array( $this, 'inject'));
	}

	/**
	 * Enqueues all the necessary Javascript files for EasyInject
	 * @param $hook
	 */
	public function easyinject_scripts( $hook ) {
		if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
			wp_enqueue_script(  'easyinject-ace-editor', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.3/ace.js' );
			wp_enqueue_script(  'easyinject-ace-editor-theme', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.3/theme-monokai.js' );
			wp_enqueue_script(  'easyinject-ace-editor-js', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.3/mode-javascript.js' );
			wp_enqueue_script(  'easyinject-ace-editor-css', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.3/mode-css.js' );
			wp_enqueue_script(  'easyinject-main', plugins_url( 'js/main.js', __FILE__ ) );
			wp_enqueue_style(  'easyinject-main', plugins_url( 'css/main.css', __FILE__ ) );
		}
	}

	/**
	 * Render saved Javascript / Css code on the specific page
	 */
	public function inject() {
		global $post;
		$post_id = $post->ID;

		if (($Javascript_Code = get_post_meta($post_id, 'easyinject_JScode', true)) != false) {
			echo "<script type='application/javascript'>$Javascript_Code</script>\n";
		}
		if (($CSS_Code = get_post_meta($post_id, 'easyinject_CSScode', true)) != false) {
			echo "<style>$CSS_Code</style>\n";
		}
	}

	/**
	 * Adding custom meta boxes to all Pages/Posts
	 * @param $post_type
	 */
	public function meta_box_display( $post_type ) {
		add_meta_box(
			'ei_meta_box'
			, "Easy Inject"
			, array( $this, 'meta_box_render' )
			, $post_type
			, 'advanced'
			, 'high'
		);
	}

	/**
	 * Triggers when user updates / publishes the Page/Post
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function meta_box_save( $post_id ) {
		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// If this is an autosave, our form has not been submitted,
		//     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}

		/* OK, its safe for us to save the data now. */
		$easyinject_JScode = $_POST['easyinject_JScode'];
		$easyinject_CSScode = $_POST['easyinject_CSScode'];

		update_post_meta($post_id, 'easyinject_JScode', $easyinject_JScode);
		update_post_meta($post_id, 'easyinject_CSScode', $easyinject_CSScode);


		if ( ! wp_is_post_revision( $post_id ) ) {
			remove_action( 'save_post', array( $this, 'meta_box_save' ) );

			add_action( 'save_post', array( $this, 'meta_box_save' ) );
		}

	}

	/**
	 * Renders the meta box created by Easy Inject
	 * @param $post
	 */
	public function meta_box_render( $post ) {
		/**
		 * Retrieve the previously set Javascript / CSS code for this page/post
		 */
		$post_id = $post->ID;

		// Default values
		$Javascript_Code = "/* Place any Javascript code here without the script tag */";
		$CSS_Code = "/* Place any CSS code here without the style tag */";

		// Get the values
		$Javascript_Code = (!$J_Code = get_post_meta($post_id, 'easyinject_JScode', true)) ? $Javascript_Code : $J_Code;
		$CSS_Code = (!$C_Code = get_post_meta($post_id, 'easyinject_CSScode', true)) ? $CSS_Code : $C_Code;

		/**
		 * Display the HTML
		 */
		?>
		<table class="easyinject_table">
			<tbody>
				<tr>
					<td>
						<h4>Javascript Code:</h4>
						<textarea class="no-display" name="easyinject_JScode"/><?php echo $Javascript_Code; ?></textarea>
						<div id="JSeditor"><?php echo $Javascript_Code; ?></div>
					</td>
					<td>
						<h4>CSS Code:</h4>
						<textarea class="no-display" name="easyinject_CSScode"/><?php echo $CSS_Code; ?></textarea>
						<div id="CSSeditor"><?php echo $CSS_Code; ?></div>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}
}

/**
 * Initialize the plugin
 */
new EI_Main();
