<?php

/**
 * Class TC_Unit_Tests_Bootstrap.
 *
 * @since 1.0.3
 */
class TC_Unit_Tests_Bootstrap {
	/** @var TC_Unit_Tests_Bootstrap instance */
	protected static $instance = null;

	public function __construct() {
		ini_set( 'display_errors', 'on' );
		error_reporting( E_ALL );


	}

	/**
	 * Get the single class instance.
	 *
	 * @since 2.2
	 * @return TC_Unit_Tests_Bootstrap
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

TC_Unit_Tests_Bootstrap::instance();