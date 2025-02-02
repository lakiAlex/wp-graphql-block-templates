<?php
/**
 * Plugin Name: WPGraphQL Block Templates
 * Description: Adds WPGraphQL fields to access WordPress site-editor/block templates, returning the block data as JSON.
 * Version: 1.0.0
 * Author: Lazar Momcilovic
 * Author URI: https://github.com/lakiAlex
 * Text Domain: wp-graphql-block-templates
 * Requires at least: 6.3
 * Tested up to: 6.7.1
 * Requires PHP: 7.1
 * License: GPL-3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WPGraphQLBlockTemplates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WPGraphQLBlockTemplates' ) ) {

	/**
	 * Class WPGraphQLBlockTemplates
	 */
	class WPGraphQLBlockTemplates {

		/**
		 * Instance of the class.
		 *
		 * @var WPGraphQLBlockTemplates
		 */
		private static $instance = null;

		/**
		 * Template object.
		 *
		 * @var object
		 */
		public $template;

		/**
		 * Constructor.
		 */
		private function __construct() {
			add_action( 'graphql_register_types', array( $this, 'register_graphql_field' ) );
		}

		/**
		 * Get the instance of the class.
		 *
		 * @return My_Plugin
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Get the fields based on the template list.
		 *
		 * @return string Return the template as JSON blocks.
		 */
		public function get_fields() {
			$fields = array();

			$block_templates = get_block_templates();

			if ( ! $block_templates ) {
				return $fields;
			}

			foreach ( $block_templates as $block_template ) {
				$field_title = $this->get_block_template_title( $block_template->title );

				$fields[ $field_title ] = array(
					'type'          => 'JSON',
					'description'   => __( 'Returns site editor templates as JSON blocks', 'wp-graphql-block-templates' ),
					'template_id'   => $block_template->id,
					'template_type' => $block_template->type,
					'args'          => array(
						'showHeader' => array(
							'type'         => 'Boolean',
							'defaultValue' => false,
							'description'  => 'Return the header template part or not if its part of the template',
						),
						'showFooter' => array(
							'type'         => 'Boolean',
							'defaultValue' => false,
							'description'  => 'Return the footer template part or not if its part of the template',
						),
					),
					'resolve'       => function ( $source, $args, $context, $info ) {
					// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						$template = ! empty( $info->fieldDefinition->config['template_id'] ) ? $info->fieldDefinition->config['template_id'] : null;
						$type     = ! empty( $info->fieldDefinition->config['template_type'] ) ? $info->fieldDefinition->config['template_type'] : 'wp_template';

						return wp_json_encode( $this->get_site_editor_templates( $template, apply_filters( 'wpgraphql_site_editor_templates_type', $type ), $args ) );
					},
				);
			}

			return apply_filters( 'wpgraphql_site_editor_templates_fields', $fields );
		}

		/**
		 * Get the site editor templates.
		 *
		 * @param string $template Template key.
		 * @param string $type Template type.
		 * @param array  $args Field arguments.
		 *
		 * @return string
		 */
		public function get_site_editor_templates( $template, $type, $args = array() ) {
			if ( ! $template ) {
				return;
			}

			$site_template = get_block_template( $template, $type );

			if ( ! empty( $site_template->content ) ) {
				$site_template->content = parse_blocks( $site_template->content );
				$processed_blocks       = array();

				foreach ( (array) $site_template->content as $key => $block ) {
					if ( null === $block['blockName'] ||
					( ( empty( $args['showHeader'] ) || false === $args['showHeader'] ) && 'core/template-part' === $block['blockName'] && 'header' === $block['attrs']['slug'] ) ||
					( ( empty( $args['showFooter'] ) || false === $args['showFooter'] ) && 'core/template-part' === $block['blockName'] && 'footer' === $block['attrs']['slug'] ) ) {
						continue;
					}

					if ( class_exists( 'NextGraphQL\Fields\Block' ) ) {
						$processed_blocks[] = new NextGraphQL\Fields\Block( $block, 1, $site_template->content, array(), '', '' );
					} else {
						$processed_blocks[] = $block;
					}
				}

				$site_template->content = $processed_blocks;
			}

			return apply_filters( 'wpgraphql_site_editor_templates_resolve', $site_template, $template, $type );
		}

		/**
		 * Get the snake case of the block template title.
		 *
		 * @param string $block_template_title Block template title.
		 */
		public function get_block_template_title( $block_template_title ) {
			$field_title = $block_template_title;
			$field_title = preg_replace( '/[^a-zA-Z0-9\s]/', '', $field_title );
			$field_title = str_replace( ' ', '', ucwords( strtolower( $field_title ) ) );

			return lcfirst( $field_title );
		}

		/**
		 * Register the WPGraphQL field.
		 */
		public function register_graphql_field() {
			register_graphql_field(
				'RootQuery',
				'blockTemplates',
				array(
					'type'        => 'blockTemplatesList',
					'description' => __( 'Returns site editor templates as JSON blocks', 'wp-graphql-block-templates' ),
					'resolve'     => function () {
						return true;
					},
				)
			);

			register_graphql_object_type(
				'blockTemplatesList',
				array(
					'fields' => $this->get_fields(),
				),
			);
		}
	}

	// Initialize.
	WPGraphQLBlockTemplates::instance();
}
