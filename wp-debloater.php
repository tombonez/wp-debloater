<?php

/**
 * Plugin Name:  WP Debloater
 * Plugin URI:   https://github.com/tombonez/wp-debloater
 * Description:  A WordPress plugin for removing unnecessary features and bloat from WordPress.
 * Version:      0.0.2
 * Author:       Tom Taylor
 * Author URI:   https://github.com/tombonez
 */

namespace WPPrettify;

remove_filter( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
remove_filter( 'wp_head', 'rest_output_link_wp_head' );
remove_filter( 'wp_head', 'rsd_link' );
remove_filter( 'wp_head', 'wlwmanifest_link' );
remove_filter( 'wp_head', 'wp_generator' );
remove_filter( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_filter( 'wp_head', 'wp_oembed_add_host_js' );
remove_filter( 'wp_head', 'wp_shortlink_wp_head' );

add_filter( 'get_bloginfo_rss', fn ( $value ) => __( 'Just another WordPress site' ) !== $value ? $value : '' );
add_filter( 'the_generator', '__return_false' );

add_filter(
	'language_attributes',
	function () {
		$attributes = array();

		if ( is_rtl() ) {
			$attributes[] = 'dir="rtl"';
		}

		$lang = esc_attr( get_bloginfo( 'language' ) );

		if ( $lang ) {
			$attributes[] = "lang=\"{$lang}\"";
		}

		return implode( ' ', $attributes );
	}
);

add_filter(
	'style_loader_tag',
	function ( $html ) {
		$html = preg_replace( "/\s+(?:type|id)=('|\")[^'\"]*('|\")/", '', $html );
		return preg_replace( "/\s+media=('|\")all('|\")/", '', $html );
	}
);

add_filter(
	'script_loader_tag',
	function ( $html ) {
		return preg_replace( "/\s+(?:type|id)=('|\")[^'\"]*('|\")/", '', $html );
	}
);

add_filter( 'emoji_svg_url', '__return_false' );
remove_filter( 'wp_head', 'print_emoji_detection_script', 7 );
remove_filter( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_filter( 'wp_print_styles', 'print_emoji_styles' );
remove_filter( 'admin_print_styles', 'print_emoji_styles' );
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

add_action( 'wp_enqueue_scripts', fn () => wp_dequeue_style( 'wp-block-library' ), 200 );

add_filter( 'feed_links_show_comments_feed', '__return_false' );
remove_filter( 'wp_head', 'feed_links_extra', 3 );

add_filter( 'show_recent_comments_widget_style', '__return_false' );

add_filter( 'use_default_gallery_style', '__return_false' );

add_action(
	'template_redirect',
	function () {
		global $wp_rewrite;

		if ( ! isset( $_SERVER['REQUEST_URI'] ) || ! isset( $wp_rewrite ) || ! is_object( $wp_rewrite ) || ! $wp_rewrite->get_search_permastruct() ) {
			return;
		}

		$request = wp_unslash( filter_var( $_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL ) );

		if ( is_search() && ! str_contains( $request, "/{$wp_rewrite->search_base}/" ) && ! str_contains( $request, '&' ) && wp_safe_redirect( get_search_link() ) ) {
			exit;
		}
	}
);

add_filter(
	'wpseo_json_ld_search_url',
	function ( $url ) {
		return str_replace(
			'/?s=',
			'/search/',
			$url
		);
	}
);
