<?php
/*
 * Taxonomies in search pages found in the "search everything" WordPress plugin
 */
Class TAXONOMIES_FOR_PAGES {
	
	protected static $instance = null;
	private $query_instance;

	private function __construct() {

		add_action( 'init', array( __CLASS__, 'register_taxonomies' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'archive_tags_and_categories' ) );

		// Filters for modifying the search query
		add_filter( 'posts_join', array( &$this, 'search_join' ) );
		add_filter( 'posts_where', array( &$this, 'search_pages' ) );
		add_filter( 'posts_search', array( &$this, 'search_where' ), 10, 2 );
		add_filter( 'posts_request', array( &$this, 'remove_duplicates' ) );
		//add_filter( 'posts_request', array( &$this, 'print_query'), 10, 2 );
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

	/**
	 * search_join function
	 * This function will modify the search query to join the taxonomies tables
	 */
	function search_join( $join ) {
		global $wpdb;

		if( !empty( $this->query_instance->query_vars['s'] ) ) {

			// Search for tags
			$on[] = "ttax.taxonomy = 'post_tag'";
			// Search for categories
			$on[] = "ttax.taxonomy = 'category'";
			// Build final string
			$on = ' ( ' . implode( ' OR ', $on ) . ' ) ';

			$join .= "  LEFT JOIN $wpdb->term_relationships AS trel ON ($wpdb->posts.ID = trel.object_id) 
						LEFT JOIN $wpdb->term_taxonomy AS ttax ON ( " . $on . " 
						AND trel.term_taxonomy_id = ttax.term_taxonomy_id) 
						LEFT JOIN $wpdb->terms AS tter ON (ttax.term_id = tter.term_id) "; 
		}

		return $join;

	}

	/**
	 * search_pages function
	 * This function will modify the search query to determine where to search
	 */
	function search_pages( $where ) {

		global $wpdb;

		if( !empty( $this->query_instance->query_vars['s'] ) ) {
			$where = str_replace( '"', '\'', $where );
			// Only search the pages where there is no password
			$where = str_replace( "post_type = 'post'", " AND 'post_password = '' AND ", $where );
		}

		return $where;
	}

	/**
	 * search_where function
	 * This function adds the where clause to the search query
	 */
	function search_where( $where, $wp_query ) {

		if( !$wp_query->is_search() ) {
			return $where;
		}

		$this->query_instance = &$wp_query;
		global $wpdb;

		$search_query = $this->get_search_default();

		$search_query .= $this->create_search_tags();

		$search_query .= $this->create_search_cats();

		if( $search_query != '' ) {
			$where = preg_replace( '#\(\(\(.*?\)\)\)#', '(('.$search_query.'))', $where );
		}

		return $where;
	}

	/**
	 * remove_duplicates function
	 * This function will remove the duplicate posts/pages that show up in the search page
	 */
	function remove_duplicates( $query ) {
		global $wpdb;
		if( !empty( $this->query_instance->query_vars['s'] ) ) {
			if( strstr( $query, 'DISTINCT' ) ) {
				// do nothing if distinct is already in the search params
			}
			else {
				$query = str_replace( 'SELECT', 'SELECT DISTINCT', $query );
			}
		}
		return $query;
	}

	/**
	 * get_search_default function
	 * This function searches for terms in the default places like title and content
	 */
	function get_search_default() {

		global $wpdb;
		$n = ( isset( $this->query_instance->query_vars['exact'] ) && $this->query_instance->query_vars['exact'] ) ? '' : '%';
		$search = '';
		$separator = '';
		$terms = self::get_search();

		$search .= '(';
		foreach( $terms as $term ) {
			$search .= $separator;
			$search .= sprintf( 
				"((%s.post_title LIKE '%s%s%s') OR (%s.post_content LIKE '%s%s%s'))", 
				$wpdb->posts, 
				$n, 
				$term, 
				$n, 
				$wpdb->posts, 
				$n, 
				$term, 
				$n 
			);
			$separator = ' AND ';
		}
		$search .= ')';

		return $search;
	}

	/**
	 * get_search function
	 * This function creates the list of search keywords from the 's' parameters.
	 */
	function get_search() {
		global $wpdb;
		$s = isset( $this->query_instance->query_vars['s'] ) ? $this->query_instance->query_vars['s'] : '';
		$sentence = isset( $this->query_instance->query_vars['sentence'] ) ? $this->query_instance->query_vars['sentence'] : false;
		$search_terms = array();

		if( !empty( $s ) ) {
			$s = stripslashes_deep( $s );
			if( $sentence ) {
				$search_terms = array( $s );
			}
			else {
				preg_match_all( '/".*?("|$)|((?<=[\\s",+])|^)[^\\s",+]+/', $s, $matches );
				$search_terms = array_map( create_function( '$a', 'return trim( $a, "\\"\'\\n\r ");' ), $matches[0] );
			}
		}

		return $search_terms;
	}

	function create_search_tags() {
		global $wpdb;
		$s = $this->query_instance->query_vars['s'];
		$search_terms = self::get_search();
		$exact = $this->query_instance->query_vars['exact'];
		$search = '';

		if( !empty( $search_terms ) ) {
			// Building search query here
			$n = $exact ? '' : '%';
			$search_and = '';
			foreach( $search_terms as $term ) {
				$term = addslashes_gpc( $term );
				$search .= "{$search_and}(tter.name LIKE '{$n}{$term}{$n}')";
				$search_and = ' AND ';
			}
			$sentence_term = $wpdb->escape( $s );
			if( !$sentence && count( $search_terms ) > 1 && $search_terms[0] != $sentence_term ) {
				$search = "($search) OR (tter.name LIKE '{$n}{$sentence_term}{$n}')";
			}
			if( !empty( $search ) ) {
				$search = " OR ({$search}) ";
			}
		}

		return $search;
	}

	function create_search_cats() {
		global $wpdb;
		$s = $this->query_instance->query_vars['s'];
		$search_terms = self::get_search();
		$exact = $this->query_instance->query_vars['exact'];
		$search = '';

		if( !empty( $search_terms ) ) {
			// Building search query for cat slug
			$n = $exact ? '' : '%';
			$search_and = '';
			$search_slug = '';
			foreach( $search_terms as $term ) {
				$term = addslashes_gpc( $term );
				$search_slug .= "{$search_and}(tter.slug LIKE '{$n}" . sanitize_title_with_dashes( $term ) . "{$n}')";
				$search_and = ' AND '; 
			}
			if ( !$sentence && count( $search_terms ) > 1 && $search_terms[0] != $s ) {
				$search_slug = "($search_slug) OR (tter.slug LIKE '{$n}" . sanitize_title_with_dashes( $s ) . "{$n}')";
			}
			if( !empty( $search_slug ) ) {
				$search = " OR ({$search_slug}) ";
			}
			// Building search query for categories description
			$search_and = '';
			$search_desc = '';
			foreach( $search_terms as $term ) {
				$term = addslashes_gpc( $term );
				$search_desc .= "{$search_and}(ttax.description LIKE '{$n}{$term}{$n}')";
				$search_and = ' AND ';
			}
			$sentence_term = $wpdb->escape( $s );
			if( !$sentence && count( $search_terms ) > 1 && $search_terms[0] != $sentence_term ) {
				$search_desc = "($search_desc) OR (ttax.description LIKE '{$n}{$sentence_term}{$n}')";
			}
			if( !empty( $search_desc ) ) {
				$search = $search . " OR ({$search_desc}) ";
			}
		}

		return $search;
	}

	function print_query( $query, $wp_query ) {
		if( $wp_query->is_search ) {
			print_r( $query );
		}
		return $query;
	}

}