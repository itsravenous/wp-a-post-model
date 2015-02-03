<?php
/**
 * Plugin Name: A Post Model
 * Description: Provides a base class for post and custom post model classes. These classes pre-process a post object, adding any required metadata (e.g. permalink URI, category terms) to the object
 * Author: Tom Jenkins
 * Author URI: http://itsravenous.com
 */

class a_post
{

	/**
	 * {Integer}
	 * Length at which to cut off excerpts
	 */
	private $excerpt_length = 255;

	public function __construct($post)
	{
		$this->post = $post;

		// Add standard taxonomy terms
		$post->categories = $this->get_terms('category');
		$post->tags = $this->get_terms('post_tag');

		// Add permalink
		$post->link = get_the_permalink($post->ID);

		// Create more view-friendly aliases for commonly used properties
		$post->type = $post->post_type;
		$post->title = $post->post_title;

		// Format the content in various ways
		$post->content = apply_filters('the_content', $post->post_content);
		$post->content_plain = strip_tags($post->content);
		$post->excerpt = strlen($post->content_plain) > $this->excerpt_length ? substr($post->content_plain, 0, $this->excerpt_length) . '&hellip;' : $post->content_plain;

		// Get the post image - thumb and large
		$post->thumb = $this->get_image();
		$post->image = $this->get_image('large');

		// If ACF is installed, fetch custom field
		if (function_exists('get_fields')) {
			$post->custom = get_fields($post);
		}
	}

	/**
	 * Returns all terms attached to the post for a given taxonomy. Terms be processed to include a permalink for their archive page
	 * @param {String} taxonomy
	 * @return {Array}
	 */
	public function get_terms ($taxonomy = 'category')
	{
		$terms = get_the_terms($this->post->ID, $taxonomy);
		if (!empty($terms))
		{
			$terms = array_map(function ($term) {
				$term->link = get_term_link($term);
				return $term;
			}, $terms);
		}

		return $terms;
	}

	/**
	 * Fetches the URI of the post's featured image
	 * @param {String} image size preset to use
	 * @return {String}
	 */
	public function get_image($size = 'thumbnail')
	{
		$img = wp_get_attachment_image_src(get_post_thumbnail_id($this->post->ID), $size);
		return $img ? $img[0] : $img;
	}

}
