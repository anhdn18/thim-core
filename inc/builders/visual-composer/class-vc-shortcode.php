<?php
/**
 * Thim_Builder VC Shortcode
 *
 * @version     1.0.0
 * @author      Thim_Builder
 * @package     Thim_Builder/Classes
 * @category    Classes
 * @author      Thimpress, leehld
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Thim_Builder_VC_Shortcode' ) ) {
	/**
	 * Class Thim_Builder_VC_Shortcode
	 */
	class Thim_Builder_VC_Shortcode {
		/**
		 * @var string
		 */
		protected $base = '';
		/**
		 * @var string
		 */
		protected $icon = '';
		/**
		 * @var string
		 */
		protected $name = '';

		/**
		 * @var string
		 */
		protected $template_name = '';

		/**
		 * @var string
		 */
		protected $desc = '';

		/**
		 * @var string
		 */
		protected $group = '';

		/**
		 * @var string
		 */
		protected $assets_url = '';

		/**
		 * @var string
		 */
		protected $assets_path = '';

		/**
		 * @var null
		 */
		protected $config_class = null;

		/**
		 * @var array
		 */
		protected $params = array();

		/**
		 * @var array
		 */
		protected $styles = array();

		/**
		 * @var array
		 */
		protected $scripts = array();

		/**
		 * Thim_Builder_Abstract_Shortcode constructor.
		 */
		public function __construct() {

			if ( ! class_exists( $this->config_class ) ) {
				return;
			}

			$config_class = new $this->config_class();

			// config
			$this->base          = $config_class::$base;
			$this->name          = $config_class::$name;
 			$this->icon          = $config_class::$icon;
			$this->template_name = $config_class::$template_name;
			$this->desc          = $config_class::$desc;
			$this->group         = $config_class::$group;
			$this->assets_url    = $config_class::$assets_url;
			$this->assets_path   = $config_class::$assets_path;
			$this->styles        = $config_class::$styles;
			$this->scripts       = $config_class::$scripts;

			// setup to vc_map
			$this->setup_shortcode();

			add_shortcode( 'thim-' . $this->base, array( $this, 'shortcode' ) );

			// enqueue scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		}

		/**
		 * Setup to VC_map
		 */
		public function setup_shortcode() {

			/**
			 * @var $config_class Thim_Builder_Abstract_Config
			 */
			$config_class = new $this->config_class();

			// shortcode params
			$this->params = $config_class::$options;

			vc_map(
				array(
					'name'        => $this->name,
					'base'        => 'thim-' . $this->base,
					'category'    => apply_filters( 'thim_shortcode_group_name', esc_html__( 'ThimBuilder Widgets', 'thim-core' ) ),
					'description' => $this->desc,
 					'icon'        => $this->icon ? $this->icon : '',
					'params'      => $this->params
				)
			);
		}

		/**
		 * Handle shortcode attrs, merge input and default attrs
		 *
		 * @param       $atts
		 * @param array $params
		 *
		 * @return array
		 */
		protected function _handle_attrs( $atts, $params = array() ) {
			$default = array();

			if ( ! $params ) {
				$params = $this->params;
			}

			foreach ( $params as $param ) {
				$type = $param['type'];
				$name = $param['param_name'];

				if ( $type == 'param_group' ) {
					if ( isset( $atts[$name] ) ) {
						$default[$name] = $this->_handle_attrs( $atts[$name], $param['params'] );
					}
				} else {
					$default[$name] = ! empty( $param['std'] ) ? $param['std'] : '';
 				}
			}

			$shortcode_atts = shortcode_atts( $default, $atts );
  			return $shortcode_atts;
		}

		/**
		 * @param $atts
		 *
		 * @return string
		 */
		public function shortcode( $atts ) {
			// get params form shortcode atts
			$atts = apply_filters( "thim-builder/vc/$this->base/shorcode_attrs", $this->_handle_attrs( $atts ) );

			// re-handle VC parse variables
			$atts = $this->_parse_atts( $atts );

			return $this->output( $atts );
		}

		/**
		 * Re-handle VC parse atts likes: vc_build_link, vc_param_group_parse_atts, so on
		 *
		 * @param        $atts
		 * @param string $params
		 *
		 * @return mixed
		 */
		protected function _parse_atts( $atts, $params = '' ) {
			if ( ! $params ) {
				$params = $this->params;
			}

			foreach ( $params as $param ) {
				$type = $param['type'];
				$name = $param['param_name'];

				if ( isset( $atts[$name] ) ) {
					switch ( $type ) {
						case 'param_group':
							$atts[$name] = vc_param_group_parse_atts( $atts[$name] );

							// array values of param group
							$values = $atts[$name];
							if ( $values ) {
								foreach ( $values as $key => $value ) {
									$atts[$name][$key] = $this->_parse_atts( $atts[$name][$key], $param['params'] );
								}
							}
							break;

						case 'vc_link':
							$atts[$name] = $atts[$name] ? vc_build_link( $atts[$name] ) : array();
							break;
						default:
							// fix for param group
							if ( isset( $param['group_id'] ) ) {
								$atts[$param['group_id']][$name] =  $atts[$name];
							}
							break;
					}
				}
			}

			return $atts;
		}

		/**
		 * Enqueue scripts
		 */
		public function register_scripts() {
			/**
			 * @var $config_class Thim_Builder_Abstract_Config
			 */
			$config_class = new $this->config_class();

			$config_class::register_scripts();
		}

		/**
		 * @param $atts
		 *
		 * @return false|string
		 */
		public function output( $atts ) {

			/**
			 * @var $config_class Thim_Builder_Abstract_Config
			 */
			$config_class = new $this->config_class();

			$config_class::enqueue_scripts();
			$sp_group = apply_filters( 'thim_support_folder_groups', true );
			if ( $sp_group == false ) {
				$group_folder = '';
				$params       = 'instance';
			} else {
				$group_folder = $this->get_group() . '/';
				$params       = 'params';
			}

			$atts = array_merge(
				$atts, array(
					'base'          => $this->base,
					'group'         => $this->group,
					'template_path' => $group_folder . $this->base . '/tpl/'
				)
			);

			// allow hook before template
			do_action( 'thim-builder/before-element-template', $this->base );

			ob_start();
			$base_file = $this->template_name ? $this->template_name : $this->base;
			echo '<div class="thim-widget-' . $this->base . ' template-' . $base_file. '">';

			$args                 = array();
			$args['before_title'] = '<h3 class="widget-title">';
			$args['after_title']  = '</h3>';

			thim_builder_get_template( $base_file, array( $params => $atts, 'args'     => $args ), $atts['template_path'] );

			echo '</div>';

			$html = ob_get_clean();

			return $html;
		}
	}
}