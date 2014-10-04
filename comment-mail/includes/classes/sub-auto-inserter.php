<?php
/**
 * Auto Sub Inserter
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_auto_inserter'))
	{
		/**
		 * Auto Sub Inserter
		 *
		 * @since 14xxxx First documented version.
		 */
		class sub_auto_inserter extends abstract_base
		{
			/**
			 * @var \stdClass|null Post object.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $post;

			/**
			 * @var \WP_User|null Post author.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $post_author;

			/**
			 * @var array Auto-subscribable post types.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $post_types;

			/**
			 * @var \WP_User|null Current user.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $current_user;

			/**
			 * @var integer Insertion ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $insert_id;

			/**
			 * Class constructor.
			 *
			 * @param integer|string $post_id Post ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct($post_id)
			{
				parent::__construct();

				$post_id = (integer)$post_id;

				if($post_id) // If possible.
					$this->post = get_post($post_id);

				if($this->post && $this->post->post_author)
					if($this->plugin->options['auto_subscribe_post_author'])
						$this->post_author = new \WP_User($this->post->post_author);

				$this->post_types = strtolower($this->plugin->options['auto_subscribe_post_types']);
				$this->post_types = preg_split('/[;,\s]+/', $this->post_types, NULL, PREG_SPLIT_NO_EMPTY);

				$this->current_user = wp_get_current_user();

				$this->insert_id = 0; // Default value.

				$this->maybe_insert(); // If applicable.
			}

			/**
			 * Inserts subscribers, if not already in the system.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_insert()
			{
				if(!$this->post)
					return; // Not applicable.

				if(!$this->post->ID)
					return; // Not applicable.

				if(!$this->plugin->options['auto_subscribe_enable'])
					return; // Not applicable.

				if(!$this->post->post_type // Just in case.
				   || !in_array($this->post->post_type, $this->post_types, TRUE)
				) return; // Not applicable.

				if(in_array($this->post->post_type, array('revision', 'nav_menu_item'), TRUE))
					return; // Not applicable.

				$this->maybe_insert_post_author();
				$this->maybe_insert_recipients();
			}

			/**
			 * Inserts post author, if not already in the system.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_insert_post_author()
			{
				if(!$this->post_author)
					return; // Not applicable.

				if(!$this->post_author->exists())
					return; // Not applicable.

				if(!$this->post_author->ID)
					return; // Not applicable.

				if(!$this->post_author->user_email)
					return; // Not applicable.

				if(!$this->plugin->options['auto_subscribe_enable'])
					return; // Not applicable.

				if(!$this->plugin->options['auto_subscribe_post_author'])
					return; // Not applicable.

				if(!in_array($this->plugin->options['auto_subscribe_deliver'],
				             array('asap', 'hourly', 'daily', 'weekly'), TRUE)
				) return; // Not applicable.

				if($this->check_existing_post_author())
					return; // Can't subscribe again.

				$insertion_ip = $last_ip = $this->current_ip_post_author();

				$data = array(
					'key'              => $this->plugin->utils_enc->uunnci_key_20_max(),
					'user_id'          => (integer)$this->post_author->ID,
					'post_id'          => (integer)$this->post->ID,
					'comment_id'       => 0, // Auto-subscribe to all comments.
					'deliver'          => $this->plugin->options['auto_subscribe_deliver'],

					'fname'            => $this->first_name_post_author(),
					'lname'            => $this->last_name_post_author(),
					'email'            => $this->post_author->user_email,
					'insertion_ip'     => $insertion_ip,
					'last_ip'          => $last_ip,

					'status'           => 'subscribed',

					'insertion_time'   => time(),
					'last_update_time' => time()
				);
				if(!$this->plugin->utils_db->wp->replace($this->plugin->utils_db->prefix().'subs', $data))
					throw new \exception(__('Sub insertion failure.', $this->plugin->text_domain));

				if(!($sub_id = $this->insert_id = (integer)$this->plugin->utils_db->wp->insert_id))
					throw new \exception(__('Sub insertion failure.', $this->plugin->text_domain));

				new sub_event_log_inserter(array_merge($data, array('sub_id' => $sub_id, 'event' => 'subscribed')));

				$this->delete_others_post_author(); // Delete other subscriptions now.
			}

			/**
			 * Inserts recipients, if not already in the system.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_insert_recipients()
			{
				if(!$this->plugin->options['auto_subscribe_enable'])
					return; // Not applicable.

				if(!$this->plugin->options['auto_subscribe_recipients'])
					return; // Not applicable.

				if(!in_array($this->plugin->options['auto_subscribe_deliver'],
				             array('asap', 'hourly', 'daily', 'weekly'), TRUE)
				) return; // Not applicable.

				$recipients = $this->plugin->options['auto_subscribe_recipients'];
				$recipients = $this->plugin->utils_mail->parse_recipients_deep($recipients);

				foreach($recipients as $_recipient)
				{
					if(!$_recipient->email)
						continue; // Not applicable.

					if($this->check_existing_recipient($_recipient))
						continue; // Can't subscribe again.

					$_insertion_ip = $_last_ip = ''; // Unknown.

					$_data = array(
						'key'              => $this->plugin->utils_enc->uunnci_key_20_max(),
						'user_id'          => 0, // Unknown user ID.
						'post_id'          => (integer)$this->post->ID,
						'comment_id'       => 0, // Auto-subscribe to all comments.
						'deliver'          => $this->plugin->options['auto_subscribe_deliver'],

						'fname'            => $_recipient->fname,
						'lname'            => $_recipient->lname,
						'email'            => $_recipient->email,
						'insertion_ip'     => $_insertion_ip,
						'last_ip'          => $_last_ip,

						'status'           => 'subscribed',

						'insertion_time'   => time(),
						'last_update_time' => time()
					);
					if(!$this->plugin->utils_db->wp->replace($this->plugin->utils_db->prefix().'subs', $_data))
						throw new \exception(__('Sub insertion failure.', $this->plugin->text_domain));

					if(!($_sub_id = $this->insert_id = (integer)$this->plugin->utils_db->wp->insert_id))
						throw new \exception(__('Sub insertion failure.', $this->plugin->text_domain));

					new sub_event_log_inserter(array_merge($_data, array('sub_id' => $_sub_id, 'event' => 'subscribed')));

					$this->delete_others_recipient($_recipient); // Delete other subscriptions now.
				}
				unset($_recipient, $_insertion_ip, $_last_ip, $_data, $_sub_id); // Housekeeping.
			}

			/**
			 * Check existing subscription(s).
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean TRUE if a subscription already exists.
			 */
			protected function check_existing_post_author()
			{
				$existing_sub = $this->check_existing_sub_post_author();

				if($existing_sub && $existing_sub->status === 'subscribed')
					return TRUE; // A subscription already exists.

				return FALSE; // Does NOT exist yet.
			}

			/**
			 * Check existing subscription(s).
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $recipient Recipient object to check.
			 *
			 * @return boolean TRUE if a subscription already exists.
			 */
			protected function check_existing_recipient(\stdClass $recipient)
			{
				$existing_sub = $this->check_existing_sub_recipient($recipient);

				if($existing_sub && $existing_sub->status === 'subscribed')
					return TRUE; // A subscription already exists.

				return FALSE; // Does NOT exist yet.
			}

			/**
			 * Check existing subscription(s).
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return \stdClass|null Existing sub; else NULL.
			 */
			protected function check_existing_sub_post_author()
			{
				$sql = "SELECT * FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `post_id` = '".esc_sql($this->post->ID)."'".

				       " AND (`user_id` = '".esc_sql($this->post_author->ID)."'".
				       "       OR `email` = '".esc_sql($this->post_author->user_email)."')".

				       " LIMIT 1"; // We should only have one anyway.

				if(($row = $this->plugin->utils_db->wp->get_row($sql)))
					$row = $this->plugin->utils_db->typify_deep($row);

				return $row ? $row : NULL;
			}

			/**
			 * Check existing subscription(s).
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $recipient Recipient object to check.
			 *
			 * @return \stdClass|null Existing sub; else NULL.
			 */
			protected function check_existing_sub_recipient(\stdClass $recipient)
			{
				$sql = "SELECT * FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `post_id` = '".esc_sql($this->post->ID)."'".

				       " AND `email` = '".esc_sql($recipient->email)."'".

				       " LIMIT 1"; // We should only have one anyway.

				if(($row = $this->plugin->utils_db->wp->get_row($sql)))
					$row = $this->plugin->utils_db->typify_deep($row);

				return $row ? $row : NULL;
			}

			/**
			 * Delete other subscriptions.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @throws \exception If a deletion failure occurs.
			 */
			protected function delete_others_post_author()
			{
				$sql = "DELETE FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `post_id` = '".esc_sql($this->post->ID)."'".

				       " AND (`user_id` = '".esc_sql($this->post_author->ID)."'".
				       "       OR `email` = '".esc_sql($this->post_author->user_email)."')".

				       " AND `ID` != '".esc_sql($this->insert_id)."'";

				if($this->plugin->utils_db->wp->query($sql) === FALSE)
					throw new \exception(__('Deletion failure.', $this->plugin->text_domain));
			}

			/**
			 * Delete other subscriptions.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $recipient Recipient object to check.
			 *
			 * @throws \exception If a deletion failure occurs.
			 */
			protected function delete_others_recipient(\stdClass $recipient)
			{
				$sql = "DELETE FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `post_id` = '".esc_sql($this->post->ID)."'".

				       " AND `email` = '".esc_sql($recipient->email)."'".

				       " AND `ID` != '".esc_sql($this->insert_id)."'";

				if($this->plugin->utils_db->wp->query($sql) === FALSE)
					throw new \exception(__('Deletion failure.', $this->plugin->text_domain));
			}

			/**
			 * Post author's first name.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Post author's first name; else `name` in email address.
			 */
			protected function first_name_post_author()
			{
				$name  = $this->clean_name_post_author();
				$fname = $this->post_author->first_name;

				if(!$fname && strpos($name, ' ', 1) !== FALSE)
					list($fname,) = explode(' ', $name, 2);
				else if(!$fname) $fname = $name;

				$fname = trim($fname); // Cleanup first name.

				if(!$fname) // Fallback on the email address.
					$fname = strstr($this->post_author->user_email, '@', TRUE);

				return $fname; // First name.
			}

			/**
			 * Post author's last name.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Post author's last name; else empty string.
			 */
			protected function last_name_post_author()
			{
				$name  = $this->clean_name_post_author();
				$lname = $this->post_author->last_name;

				if(!$lname && strpos($name, ' ', 1) !== FALSE)
					list(, $lname) = explode(' ', $name, 2);

				$lname = trim($lname); // Cleanup last name.

				return $lname; // Last name.
			}

			/**
			 * Post author's clean name.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Post author's clean name; else empty string.
			 */
			protected function clean_name_post_author()
			{
				return $this->plugin->utils_string->clean_name($this->post_author->display_name);
			}

			/**
			 * Post author's IP address.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Post author's IP address; else empty string.
			 */
			protected function current_ip_post_author()
			{
				if($this->post_author->ID === $this->current_user->ID)
					return $this->plugin->utils_env->user_ip();

				return ''; // Post author not current user.
			}
		}
	}
}