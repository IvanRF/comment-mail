<?php
/**
 * Template
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\template'))
	{
		/**
		 * Template
		 *
		 * @since 14xxxx First documented version.
		 */
		class template extends abs_base
		{
			/**
			 * @var string Template file.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $file;

			/**
			 * @var string Template file option key.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $file_option_key;

			/**
			 * @var string Template file contents.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $file_contents;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $file Template file.
			 *
			 * @throws \exception If `$file` is empty.
			 */
			public function __construct($file)
			{
				parent::__construct();

				$this->file = (string)$file;
				$this->file = $this->plugin->utils_string->trim_deep($this->file, '', '/');

				if(!$this->file) // Empty file property?
					throw new \exception(__('Empty file property.', $this->plugin->text_domain));

				// e.g. `site/comment-form/subscription-ops.php`.
				// e.g. `email/confirmation-request-message.php`.
				// becomes: `template_site_comment_form_subscription_ops`.
				// becomes: `template_email_confirmation_request_message`.
				$this->file_option_key = preg_replace('/\.php$/i', '', $this->file);
				$this->file_option_key = str_replace(array('/', '-'), '_', $this->file_option_key);
				$this->file_option_key = 'template_'.$this->file_option_key;

				$this->file_contents = $this->get_file_contents();
			}

			/**
			 * Parse template file.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $vars Optional array of variables to parse.
			 *
			 * @return string Parsed template file contents.
			 */
			public function parse(array $vars = array())
			{
				$vars['plugin'] = plugin(); // Plugin class.

				if(strpos($this->file, 'site/') === 0)
					$vars = array_merge($vars, $this->site_vars($vars));

				if(strpos($this->file, 'email/') === 0)
					$vars = array_merge($vars, $this->email_vars($vars));

				return trim($this->plugin->utils_php->evaluate($this->file_contents, $vars));
			}

			/**
			 * Site template vars.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $vars Optional array of variables to parse.
			 *
			 * @return array An array of all site template vars.
			 */
			protected function site_vars(array $vars = array())
			{
				if($this->file === 'site/site-easy-header.php')
					return array(); // Prevent infinite loop.

				if($this->file === 'site/site-header.php')
					return array(); // Prevent infinite loop.

				if($this->file === 'site/site-easy-footer.php')
					return array(); // Prevent infinite loop.

				if($this->file === 'site/site-footer.php')
					return array(); // Prevent infinite loop.

				// Header templates. Both the easy and full templates.

				if(is_null($site_easy_header_template = &$this->cache_key(__FUNCTION__, 'site_easy_header_template')))
					$site_easy_header_template = new template('site/site-easy-header.php');

				if(is_null($site_header_template = &$this->cache_key(__FUNCTION__, 'site_header_template')))
					$site_header_template = new template('site/site-header.php');
				/**
				 * @var $site_easy_header_template template For IDEs.
				 * @var $site_header_template template For IDEs.
				 */
				$site_easy_header = $site_easy_header_template->parse($vars);
				$site_header      = $site_header_template->parse(array_merge($vars, compact('site_easy_header')));

				// Footer templates. Both the easy and full templates.

				if(is_null($site_easy_footer_template = &$this->cache_key(__FUNCTION__, 'site_easy_footer_template')))
					$site_easy_footer_template = new template('site/site-easy-footer.php');

				if(is_null($site_footer_template = &$this->cache_key(__FUNCTION__, 'site_footer_template')))
					$site_footer_template = new template('site/site-footer.php');
				/**
				 * @var $site_easy_footer_template template For IDEs.
				 * @var $site_footer_template template For IDEs.
				 */
				$site_easy_footer = $site_easy_footer_template->parse($vars);
				$site_footer      = $site_footer_template->parse(array_merge($vars, compact('site_easy_footer')));

				return compact('site_header', 'site_footer'); // Header/footer.
			}

			/**
			 * Email template vars.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $vars Optional array of variables to parse.
			 *
			 * @return array An array of all email template vars.
			 */
			protected function email_vars(array $vars = array())
			{
				if($this->file === 'email/email-easy-header.php')
					return array(); // Prevent infinite loop.

				if($this->file === 'email/email-header.php')
					return array(); // Prevent infinite loop.

				if($this->file === 'email/email-easy-footer.php')
					return array(); // Prevent infinite loop.

				if($this->file === 'email/email-footer.php')
					return array(); // Prevent infinite loop.

				// Header templates. Both the easy and full templates.

				if(is_null($email_easy_header_template = &$this->cache_key(__FUNCTION__, 'email_easy_header_template')))
					$email_easy_header_template = new template('email/email-easy-header.php');

				if(is_null($email_header_template = &$this->cache_key(__FUNCTION__, 'email_header_template')))
					$email_header_template = new template('email/email-header.php');
				/**
				 * @var $email_easy_header_template template For IDEs.
				 * @var $email_header_template template For IDEs.
				 */
				$email_easy_header = $email_easy_header_template->parse($vars);
				$email_header      = $email_header_template->parse(array_merge($vars, compact('email_easy_header')));

				// Footer templates. Both the easy and full templates.

				if(is_null($email_easy_footer_template = &$this->cache_key(__FUNCTION__, 'email_easy_footer_template')))
					$email_easy_footer_template = new template('email/email-easy-footer.php');

				if(is_null($email_footer_template = &$this->cache_key(__FUNCTION__, 'email_footer_template')))
					$email_footer_template = new template('email/email-footer.php');
				/**
				 * @var $email_easy_footer_template template For IDEs.
				 * @var $email_footer_template template For IDEs.
				 */
				$email_easy_footer = $email_easy_footer_template->parse($vars);
				$email_footer      = $email_footer_template->parse(array_merge($vars, compact('email_easy_footer')));

				// Add "powered by" note at the bottom of all email templates?

				if(!$this->plugin->is_pro || $this->plugin->options['email_footer_powered_by_enable'])
				{
					$powered_by   = '<hr /><p style="color:#888888;">'. // Powered by note at the bottom of all email templates.
					                ' '.sprintf(__('~ powered by %1$s™ for WordPress', $this->plugin->text_domain), esc_html($this->plugin->name)).
					                '  &lt;<a href="'.esc_attr($this->plugin->utils_url->product_page()).'">'.esc_html($this->plugin->utils_url->product_page()).'</a>&gt;'.
					                '</p>';
					$email_footer = str_ireplace('</body>', $powered_by.'</body>', $email_footer); // Before closing body tag.
				}
				return compact('email_header', 'email_footer'); // Header/footer.
			}

			/**
			 * Template file contents.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @throws \exception If unable to locate the template.
			 */
			protected function get_file_contents()
			{
				// e.g. `site/comment-form/subscription-ops.php`.
				// e.g. `email/confirmation-request-message.php`.
				// becomes: `template_site_comment_form_subscription_ops`.
				// becomes: `template_email_confirmation_request_message`.
				if(!empty($this->plugin->options[$this->file_option_key]))
					return $this->plugin->options[$this->file_option_key];

				// e.g. `wp-content/themes/[theme]/comment-mail`.
				// e.g. `wp-content/themes/[theme]/comment-mail/site/comment-form/subscription-ops.php`.
				// e.g. `wp-content/themes/[theme]/comment-mail/email/confirmation-request-message.php`.
				$dirs[] = get_stylesheet_directory().'/'.$this->plugin->slug;
				$dirs[] = get_template_directory().'/'.$this->plugin->slug;
				$dirs[] = dirname(dirname(__FILE__)).'/templates';

				foreach($dirs as $_dir /* In order of precedence. */)
					// Note: don't check `filesize()` here; templates CAN be empty.
					if(is_file($_dir.'/'.$this->file) && is_readable($_dir.'/'.$this->file))
						return file_get_contents($_dir.'/'.$this->file);
				unset($_dir); // Housekeeping.

				throw new \exception(sprintf(__('Missing template for: `%1$s`.', $this->plugin->text_domain), $this->file));
			}
		}
	}
}