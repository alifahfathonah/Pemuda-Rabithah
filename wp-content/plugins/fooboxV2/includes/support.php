<?php
/**
 * Support Tab on Foobox Settings Page
 *
 * @author      Brad Vincent
 * @package     foobox/includes
 * @version     1.0
 */

if (!class_exists('foobox_support')) {

	class foobox_support {

		public function render($args) {
			?>
			<h3><?php _e('FooBox Priority Support', 'foobox'); ?></h3>
			<p>
				<?php
				$link = sprintf('<strong><a target="_blank" href="http://fooplugins.com/foobox/support/?%s">%s</a></strong>', http_build_query($args), __('from this link', 'foobox'));
				echo sprintf( __('The quickest way to get priority support is to fill in our FooBox support form %s.', 'foobox'), $link );
				echo '<br /><strong>' . __('Please note:', 'foboox') . '</strong> ';
				_e('we try to pass as much information as we can to the form to save you from filling in every field.', 'foobox');
				?>
			</p>
			<p>
				<?php _e('We are in the process of building the support form directly into this page. That is coming soon in an upcoming version of FooBox', 'foobox'); ?>
			</p>
		<?php
		}

		public function render_invalid() {
			?>
			<h3><?php _e('Validation Required!', 'foobox'); ?></h3>
			<p>
				<?php
				_e('Support options only show after validating your license key. Please validate your license key under the "General" tab above, and make sure to click "Save Changes" after a successful validation.', 'foobox');
				?>
			</p>
		<?php
		}
	}
}