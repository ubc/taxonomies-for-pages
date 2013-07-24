<?php

Class TAXONOMIES_FOR_PAGES {
	
	protected static $instance = null;
	private $query_instance;

	private function __construct() {

		add_action( 'init', array( __CLASS__, 'register_taxonomies' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'archive_tags_and_categories' ) );

	}

	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;

	}

	/**
	 * register_taxonomies function
	 * This function registers the taxonomies for the page post type
	 * and creates the tags and categories boxes
	 */
	public static function register_taxonomies() {

		// Categories
		register_taxonomy_for_object_type( 'category', 'page' );

		// Tags
		register_taxonomy_for_object_type( 'post_tag', 'page' );

	}

	/**
	 * archive_tags_and_categories function
	 * This function will hook into the wordpress action when going to archive pages
	 * and display pages along with the posts inside the archive page
	 */
	public static function archive_tags_and_categories( $query ) {

			if( $query->is_archive && ( $query->is_tag || $query->is_category) ) {
				$query->set( 'post_type', array( 'post', 'page' ) );
			}

	}

}