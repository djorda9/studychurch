<?php
/**
 * StudyChurch functions and definitions
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development and
 * http://codex.wordpress.org/Child_Themes), you can override certain functions
 * (those wrapped in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before the parent
 * theme's file, so the child theme functions would be used.
 *
 * @package StudyChurch
 * @since 0.1.0
 */

// Useful global constants
define( 'SC_VERSION', '0.1.0' );
define( 'BP_DEFAULT_COMPONENT', 'profile' );

SC_Setup::get_instance();
class SC_Setup {

	/**
	 * @var
	 */
	protected static $_instance;

	/**
	 * Only make one instance of the SC_Setup
	 *
	 * @return SC_Setup
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof SC_Setup ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Add Hooks and Actions
	 */
	protected function __construct() {
		$this->add_includes();
		$this->add_filters();
		$this->add_actions();
	}

	protected function add_includes() {

		/**
		 * Custom functions that act independently of the theme templates.
		 */
		require get_template_directory() . '/inc/helper_functions.php';

		/**
		 * Customizer additions.
		 */
		require get_template_directory() . '/inc/customizer.php';

		/**
		 * Include custom Foundation functionality
		 */
		require get_template_directory() . '/inc/classes.php';

		/**
		 * Create custom post types
		 */
		require get_template_directory() . '/inc/custom-post-types.php';

		/**
		 * Create custom meta fields
		 */
		require get_template_directory() . '/inc/custom-meta-fields.php';

		/**
		 * Functions for template components
		 */
		require get_template_directory() . '/inc/template-helpers.php';

		/**
		 * Functionality for profile
		 */
		require get_template_directory() . '/inc/profile-helpers.php';

		/**
		 * General BP Filters
		 */
		require get_template_directory() . '/inc/bp-filters.php';

		/**
		 * Handle login and registration requests
		 */
		require get_template_directory() . '/inc/ajax-login.php';
		require get_template_directory() . '/inc/ajax-forms.php';

		/**
		 * Study functions
		 */
		require get_template_directory() . '/inc/study.php';
		require get_template_directory() . '/inc/study/loader.php';

		/**
		 * Initialize Assignments Component
		 */
		require get_template_directory() . '/inc/assignments/loader.php';
	}

		/**
		 * Wire up filters
		 */
	protected function add_filters() {
		add_filter( 'wp_title', array( $this, 'wp_title_for_home' ) );
		add_filter( 'show_admin_bar', array( $this, 'show_admin_bar' ) );
		add_filter( 'bp_get_nav_menu_items', array( $this, 'bp_nav_menu_items' ) );
		add_filter( 'bp_template_include',   array( $this, 'pb_default_template' ) );
	}

	/**
	 * Custom page header for home page
	 *
	 * @param $title
	 *
	 * @return string
	 */
	public function wp_title_for_home( $title ) {
		if( empty( $title ) && ( is_home() || is_front_page() ) ) {
			return get_bloginfo( 'name' ) . ' | ' . get_bloginfo( 'description' );
		}
		return $title;
	}

	/**
	 * Wire up actions
	 */
	protected function add_actions() {
		add_action( 'after_setup_theme',  array( $this, 'setup'              ) );
		add_action( 'widgets_init',       array( $this, 'add_sidebars'       ) );
		add_action( 'widgets_init',       array( $this, 'unregister_widgets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue'            ) );
		add_action( 'wp_head',            array( $this, 'js_globals'         ) );
		add_action( 'template_redirect',  array( $this, 'maybe_force_login'  ), 5 );
	}

	/**
	 * Theme setup
	 */
	public function setup() {
		add_editor_style();

		$this->add_image_sizes();

		$this->add_menus();

		/**
		 * Make theme available for translation
		 * Translations can be filed in the /languages/ directory
		 * If you're building a theme based on sc, use a find and replace
		 * to change 'sc' to the name of your theme in all the template files
		 */
		load_theme_textdomain( 'sc', get_template_directory() . '/languages' );

		/**
		 * Add default posts and comments RSS feed links to head
		 */
		add_theme_support( 'automatic-feed-links' );

		/**
		 * Enable support for Post Thumbnails on posts and pages
		 *
		 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
		 */
		add_theme_support( 'post-thumbnails' );

		/**
		 * Setup the WordPress core custom background feature.
		 */
		add_theme_support( 'custom-background', apply_filters( 'sc_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );
	}

	/**
	 * Register theme sidebars
	 */
	public function add_sidebars() {

		$defaults = array(
			'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
			'after_widget'  => '</li>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		);

		$sidebars = array(
			array(
				'id'          => 'sidebar',
				'name'        => 'Default Sidebar',
				'description' => 'Default sidebar display',
			),
			array(
				'id'          => 'landing-social',
				'name'        => 'Landing Page Social',
				'description' => 'Social widget for landing page',
			)
		);

		foreach( $sidebars as $sidebar ) {
			register_sidebar( array_merge( $sidebar, $defaults ) );
		}

	}

	/**
	 * Unregister widgets
	 */
	public function unregister_widgets() {}

	/**
	 * Enqueue styles and scripts
	 */
	public function enqueue() {
		$this->enqueue_scripts();
		$this->enqueue_styles();
	}

	/**
	 * Enqueue Styles
	 */
	protected function enqueue_styles() {
		$postfix = ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_style( 'sc', get_template_directory_uri() . "/assets/css/studychurch{$postfix}.css", array(), SC_VERSION );
		wp_enqueue_style( 'google_fonts', 'https://fonts.googleapis.com/css?family=Open+Sans:300italic,600italic,300,600' );
	}

	/**
	 * Enqueue scripts
	 */
	protected function enqueue_scripts() {
		$postfix = ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) ? '' : '.min';

		/**
		 * Libraries and performance scripts
		 */
		wp_enqueue_script( 'datepicker',          get_template_directory_uri() . '/assets/js/lib/foundation-datepicker.min.js',   array(), false, true );
		wp_enqueue_script( 'navigation',          get_template_directory_uri() . '/assets/js/lib/navigation.js',                  array(),           '20120206', true );
		wp_enqueue_script( 'skip-link-focus-fix', get_template_directory_uri() . '/assets/js/lib/skip-link-focus-fix.js',         array(),           '20130115', true );
		wp_enqueue_script( 'foundation',          get_template_directory_uri() . '/assets/js/lib/foundation' . $postfix . '.js', array( 'jquery' ), '01',       true );
		wp_enqueue_script( 'simplePageNav',       get_template_directory_uri() . '/assets/js/lib/jquery.singlePageNav.min.js',    array( 'jquery' ), '01',       true );

		wp_enqueue_style( 'froala-content', get_template_directory_uri() . '/assets/css/froala/froala_content.css' );
		wp_enqueue_style( 'froala-editor', get_template_directory_uri() . '/assets/css/froala/froala_editor.css' );
		wp_enqueue_style( 'froala-style', get_template_directory_uri() . '/assets/css/froala/froala_style.css' );

		wp_enqueue_script( 'froala-editor', get_template_directory_uri() . '/assets/js/lib/froala/froala_editor.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'froala-fullscreen', get_template_directory_uri() . '/assets/js/lib/froala/plugins/fullscreen.min.js', array( 'jquery', 'froala-editor' ) );

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		if ( is_singular() && wp_attachment_is_image() ) {
			wp_enqueue_script( 'sc-keyboard-image-navigation', get_template_directory_uri() . '/js/keyboard-image-navigation.js', array( 'jquery' ), '20120202' );
		}

		wp_enqueue_script( 'sc', get_template_directory_uri() . "/assets/js/studychurch{$postfix}.js", array( 'jquery', 'foundation', 'wp-util', 'wp-backbone', 'wp-api', 'jquery-ui-sortable', 'froala-editor', 'datepicker' ), SC_VERSION, true );
	}

	/**
	 * Is this a development environment?
	 *
	 * @return bool
	 */
	public function is_dev() {
		return ( 'studychurch.dev' == $_SERVER['SERVER_NAME'] );
	}

	/**
	 * Add custom image sizes
	 */
	protected function add_image_sizes() {}

	/**
	 * Register theme menues
	 */
	protected function add_menus() {
		register_nav_menus( array(
			'members'   => 'Main Members Menu',
			'public'    => 'Main Public Menu',
		) );
	}

	public function show_admin_bar() {
		if ( current_user_can( 'administrator' ) ) {
			return true;
		}

		return false;
	}

	public function bp_nav_menu_items( $items ) {
		// Get the top-level menu parts (Friends, Groups, etc) and sort by their position property
		$top_level_menus = (array) buddypress()->bp_nav;
		usort( $top_level_menus, '_bp_nav_menu_sort' );

		// Iterate through the top-level menus
		foreach ( $top_level_menus as $nav ) {

			// Skip items marked as user-specific if you're not on your own profile
			if ( empty( $nav['show_for_displayed_user'] ) && ! bp_core_can_edit_settings()  ) {
				continue;
			}

			if ( 'activity' == $nav['slug'] ) {
				continue;
			}

			// Get the correct menu link. See http://buddypress.trac.wordpress.org/ticket/4624
			$link = trailingslashit( bp_displayed_user_domain() . $nav['link'] );

			// Add this menu
			$menu         = new stdClass;
			$menu->class  = array( 'menu-parent' );
			$menu->css_id = $nav['css_id'];
			$menu->link   = $link;
			$menu->name   = $nav['name'];
			$menu->parent = 0;

			$menus[] = $menu;
		}

		return $menus;
	}

	public function pb_default_template( $template ) {
		if ( get_stylesheet_directory() . '/page.php' != $template ) {
			return $template;
		}

		if ( $new_temp = locate_template( 'templates/full-width.php' ) ) {
			$template = $new_temp;
		}

		return $template;
	}

	/**
	 * Force user login
	 */
	public function maybe_force_login() {
		/** bale if the user is logged in or is on the login page */
		if ( is_user_logged_in() || ! is_buddypress() ) {
			return;
		}

		include( get_template_directory() . '/page-login.php' );
		exit();
	}

	public function js_globals() {
		$key = ( $this->is_dev() ) ? 'ntB-13C-11nroeB-22B-16syB1wqc==' : 'gsuwgH-7fnrzE5ic=='; ?>

		<script>
			jQuery.Editable = jQuery.Editable || {};
			jQuery.Editable.DEFAULTS = jQuery.Editable.DEFAULTS || {};

			jQuery.Editable.DEFAULTS.key = '<?php echo $key; ?>';
		</script>
		<?php
	}
}