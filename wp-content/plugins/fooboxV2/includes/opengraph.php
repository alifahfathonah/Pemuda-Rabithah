<?php
/**
 * Adds Opengraph Meta Tags
 *
 * @author 	Brad Vincent
 * @package 	foobox/includes
 * @version     1.2
 */

if (!class_exists('foo_opengraph')) {

  class foo_opengraph {

    function add_meta() {

      //only add meta tags for single posts
      the_post();

      $meta = array();
      $meta['og:title'] = $this->title();
      $meta['og:type'] = $this->type();
      $meta['og:url'] = $this->url();

      $meta['og:description'] = esc_attr( strip_tags( stripslashes( $this->description() ) ) );
      $meta['og:site_name'] = $this->site_name();;
      $meta['og:locale'] = $this->locale();

      if (current_theme_supports('post-thumbnails')) {
        if (is_singular() && has_post_thumbnail()) {
          $meta['og:image'] = wp_get_attachment_url(get_post_thumbnail_id());
        }
      }

      rewind_posts();

      $this->render($meta);

    }

    function render($meta) {
      foreach ( $meta as $key=>$value ) {
        $esc_value = esc_attr ( $value );
        echo "<meta property=\"{$key}\" content=\"{$esc_value}\" />\n";
      }
    }

    function is_aio_installed() {
      return function_exists('aiosp_meta');
    }

    function url() {
      global $wp;
      return add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
    }

    function title() {
      if (is_singular()) {
        if ( $this->is_aio_installed() ) {
          return get_post_meta(get_the_ID(), '_aioseop_title', true);
        }
        return get_the_title();
      }
      return $this->site_name();
    }

    function type() {
      return is_single() ? 'article' : 'website';
    }

    function description() {
      if (is_singular()) {
        if ( $this->is_aio_installed() ) {
          return get_post_meta(get_the_ID(), '_aioseop_description', true);
        }
        $desc = get_the_excerpt();
        return empty($desc) ? get_bloginfo('description') : $desc;
      }
      return get_bloginfo('description');
    }

    function site_name() {
      return strip_tags(get_bloginfo('name'));
    }

    function locale() {
      return strtolower(str_replace('-', '_', get_bloginfo('language')));
    }

  }

}