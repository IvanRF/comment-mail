<?php
/**
 * User Register
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\user_register'))
	{
		/**
		 * User Register
		 *
		 * @since 14xxxx First documented version.
		 */
		class user_register extends abstract_base
		{
			/**
			 * @var integer User ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $user_id;

			/**
			 * Class constructor.
			 *
			 * @param integer|string $user_id User ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct($user_id)
			{
				parent::__construct();

				$this->user_id = (integer)$user_id;

				$this->maybe_update_subs();
			}

			/**
			 * Update subscribers; set user ID.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @throws \exception If a deletion failure occurs.
			 */
			protected function maybe_update_subs()
			{
				if(!$this->user_id)
					return; // Nothing to do.

				$user = new \WP_User($this->user_id);

				if(!$user->exists() || !$user->ID || !$user->user_email)
					return; // Not applicable.

				$sql = "DELETE FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".
				       " WHERE `user_id` = '".esc_sql($user->ID)."'";

				if($this->plugin->utils_db->wp->query($sql) === FALSE)
					throw new \exception(__('Deletion failure.', $this->plugin->text_domain));
				// The user ID should NOT exist; we just make absolutely sure in case of corruption.

				$sql = "UPDATE `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".
				       " SET `user_id` = '".esc_sql($user->ID)."'".

				       " WHERE `user_id` = '0'".
				       " AND `email` = '".esc_sql($user->user_email)."'";

				if($this->plugin->utils_db->wp->query($sql) === FALSE)
					throw new \exception(__('Update failure.', $this->plugin->text_domain));
			}
		}
	}
}