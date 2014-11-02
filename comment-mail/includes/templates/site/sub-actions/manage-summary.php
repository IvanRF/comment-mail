<?php
namespace comment_mail;

/**
 * @var plugin         $plugin Plugin class.
 *
 * Other variables made available in this template file:
 *
 * @var string         $site_header Parsed site header template.
 * @var string         $site_footer Parsed site footer template.
 *
 * @var string         $sub_key Key granting access to summary; if applicable.
 *
 *    Note that a `$sub_key` is only present if the summary is accessed w/ a key.
 *    The summary is allowed to be accessed w/o a key also, and in this case
 *    the summary for the current user (and/or the current email cookie)
 *    is display instead. Therefore, this value is mostly irrelevant
 *    for templates — only provided for the sake of being thorough.
 *
 * @var \stdClass      $sub_email Email address that we're displaying the summary for.
 *
 *    Note that we may also display a summary of any comment subscriptions
 *    that are indirectly related to this email address, but still belong to the
 *    current user. e.g. if the `$sub_email` has been associated with one or more user IDs
 *    within WordPress, subscriptions for those user IDs will be summarized also.
 *    See `$sub_user_ids` for access to the array of associated WP user IDs.
 *
 * @var integer[]      $sub_user_ids An array of any WP user IDs associated w/ the email address.
 *
 * @var \stdClass      $query_vars Nav/query vars; consisting of: `page`, `per_page`, `post_id`, `status`.
 *    Note that `post_id` will be `NULL` when there is no specific post ID filter applied to the list of `$subs`.
 *    Note that `status` will be empty when there is no specific status filter applied to the list of `$subs`.
 *
 * @var \stdClass[]    $subs An array of all subscriptions to display as part of the summary on this `$query_vars->page`.
 *    Note that all query vars/filters/etc. will have already been applied; a template simply needs to iterate and display a table row for each of these.
 *
 * @var \stdClass|null $pagination_vars Pagination vars; consisting of: `page`, `per_page`, `total_subs`, `total_pages`.
 *    Note that `page` and `per_page` are simply duplicated here for convenience; same as you'll find in `$query_vars`.
 *
 * @var boolean        $processing Are we (i.e. did we) process an action? e.g. a deletion from the list perhaps.
 *
 * @var array          $processing_errors An array of any/all processing errors.
 *    Array keys are error codes; array values are predefined error messages.
 *    Note that predefined messages in this array are in plain text format.
 *
 * @var array          $processing_error_codes An array of any/all processing error codes.
 *    This includes the codes only; i.e. w/o the full array of predefined messages.
 *
 * @var array          $processing_errors_html An array of any/all processing errors.
 *    Array keys are error codes; array values are predefined error messages.
 *    Note that predefined messages in this array are in HTML format.
 *
 * @var array          $processing_successes An array of any/all processing successes.
 *    Array keys are success codes; array values are predefined success messages.
 *    Note that predefined messages in this array are in plain text format.
 *
 * @var array          $processing_success_codes An array of any/all processing success codes.
 *    This includes the codes only; i.e. w/o the full array of predefined messages.
 *
 * @var array          $processing_successes_html An array of any/all processing successes.
 *    Array keys are success codes; array values are predefined success messages.
 *    Note that predefined messages in this array are in HTML format.
 *
 * @var array          $error_codes An array of any/all major error codes; excluding processing error codes.
 *    Note that you should NOT display the summary at all, if any major error exist here.
 */
?>
<?php echo // Sets document <title> tag via `%%title%%` replacement code in header.
str_replace('%%title%%', __('My Comment Subscriptions', $plugin->text_domain), $site_header); ?>

	<div class="manage-summary">

		<?php if($error_codes): // Any major errors? ?>

			<div class="alert alert-danger" role="alert">
				<p style="margin-top:0; font-weight:bold; font-size:120%;">
					<?php echo __('Please review the following error(s):', $plugin->text_domain); ?>
				</p>
				<ul class="list-unstyled" style="margin-bottom:0;">
					<?php foreach($error_codes as $_error_code): ?>
						<li style="margin-top:0; margin-bottom:0;">
							<i class="fa fa-warning fa-fw"></i>
							<?php switch($_error_code)
							{
								case 'missing_sub_key':
									echo __('Missing subscription key; unable to display summary.', $plugin->text_domain);
									break; // Break switch handler.

								case 'invalid_sub_key':
									echo __('Invalid subscription key; unable to display summary.', $plugin->text_domain);
									break; // Break switch handler.

								default: // Anything else that is unexpected/unknown at this time.
									echo __('Unknown error; unable to display summary.', $plugin->text_domain);
							} ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

		<?php else: // Display summary; there are no major errors. ?>

		<?php if ($processing && $processing_errors): // Any processing errors? ?>

			<div class="alert alert-danger" role="alert">
				<p style="margin-top:0; font-weight:bold; font-size:120%;">
					<?php echo __('Please review the following error(s):', $plugin->text_domain); ?>
				</p>
				<ul class="list-unstyled" style="margin-bottom:0;">
					<?php foreach($processing_errors_html as $_error_code => $_error_html): ?>
						<li style="margin-top:0; margin-bottom:0;">
							<i class="fa fa-warning fa-fw"></i>
							<?php echo $_error_html; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

		<?php endif; ?>

		<?php if ($processing && $processing_successes): // Any processing successes? ?>

			<div class="alert alert-success" role="alert">
				<p style="margin-top:0; font-weight:bold; font-size:120%;">
					<?php echo __('Submission accepted; thank you :-)', $plugin->text_domain); ?>
				</p>
				<ul class="list-unstyled" style="margin-bottom:0;">
					<?php foreach($processing_successes_html as $_success_code => $_success_html): ?>
						<li style="margin-top:0; margin-bottom:0;">
							<i class="fa fa-check fa-fw"></i>
							<?php echo $_success_html; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

		<?php endif; ?>

			<h2 style="margin-top:0;">
				<i class="fa fa-envelope pull-right"></i>
				<?php echo __('My Comment Subscriptions', $plugin->text_domain); ?>
			</h2>

		<hr />

		<?php if (empty($subs)): ?>
			<p class="center-block" style="font-size:120%;">
				<?php echo __('No subscriptions at this time.', $plugin->text_domain); ?>
			</p>
		<?php endif; ?>

			<div class="subs-table table-responsive">
				<table class="table table-striped table-hover">
					<thead>
					<tr>
						<th class="manage-summary-subscr-email">
							<?php echo __('Email Address', $plugin->text_domain); ?>
						</th>
						<th class="manage-summary-subscr-to">
							<?php echo __('Subscribed To', $plugin->text_domain); ?>
						</th>
						<th class="manage-summary-subscr-type">
							<?php echo __('Type', $plugin->text_domain); ?>
						</th>
						<th class="manage-summary-subscr-status">
							<?php echo __('Status', $plugin->text_domain); ?>
						</th>
						<th class="manage-summary-subscr-delivery-op">
							<?php echo __('Deliver', $plugin->text_domain); ?>
						</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach($subs as $_sub): ?>
						<tr>
							<td>
								<?php
								/*
								 * Here we define a few more variables of our own, for each subscription we iterate.
								 * All of this data is based on what's already provided in the array of `$subs`;
								 *    ~ as documented at the top of this file.
								 */
								// Post they are subscribed to.
								$_sub_post              = get_post($_sub->post_id);
								$_sub_post_url          = get_permalink($_sub->post_id);
								$_sub_post_comments_url = get_comments_link($_sub->post_id);
								$_sub_post_title_clip   = $_sub_post ? $plugin->utils_string->clip($_sub_post->post_title) : '';
								$_sub_post_type         = $_sub_post ? get_post_type_object($_sub_post->post_type) : '';
								$_sub_post_type_label   = $_sub_post_type ? $_sub_post_type->labels->singular_name : '';

								// A possible comment they are subscribed to; instead of just "all" comments.
								$_sub_comment            = $_sub->comment_id ? get_comment($_sub->comment_id) : NULL;
								$_sub_comment_url        = $_sub->comment_id ? get_comment_link($_sub->comment_id) : '';
								$_sub_comment_date_utc   = $_sub_comment ? $plugin->utils_date->i18n_utc('M jS, Y @ g:i a T', strtotime($_sub_comment->comment_date_gmt)) : '';
								$_sub_comment_date_local = $_sub_comment ? $plugin->utils_date->i18n('M jS, Y @ g:i a T', strtotime($_sub_comment->comment_date_gmt)) : '';
								$_sub_comment_time_ago   = $_sub_comment ? $plugin->utils_date->approx_time_difference(strtotime($_sub_comment->comment_date_gmt)) : '';

								// URLs that allow for actions to be performed against the subscription.
								$_sub_edit_url   = $plugin->utils_url->sub_manage_sub_edit_url($_sub->key, NULL, TRUE);
								$_sub_delete_url = $plugin->utils_url->sub_manage_sub_delete_url($_sub->key, NULL, TRUE);

								// Type of subscription; one of `comment` or `comments`.
								$_sub_type = $_sub->comment_id ? 'comment' : 'comments';

								$_sub_name_email_args = array('anchor_to' => $_sub_edit_url);
								// This is the subscriber's `"name" <email>` w/ HTML markup enhancements.
								$_sub_name_email_markup = $plugin->utils_markup->name_email($_sub->fname.' '.$_sub->lname, $_sub->email, $_sub_name_email_args);

								$_subscribed_to_own_comment = // Subscribed to their own comment?
									$_sub_comment && strcasecmp($_sub_comment->comment_author_email, $_sub->email) === 0;
								?>
								<i class="fa fa-envelope"></i>
								<?php echo $_sub_name_email_markup; ?><br />

								<div class="hover-links">
									<a href="<?php echo esc_attr($_sub_edit_url); ?>"
									   title="<?php echo esc_attr(__('Edit Subscription', $plugin->text_domain)); ?>"
										><i class="fa fa-pencil-square-o"></i> <?php echo __('Edit Subscr.', $plugin->text_domain); ?></a>

									<span class="text-muted">|</span>

									<a data-action="<?php echo esc_attr($_sub_delete_url); ?>" href="<?php echo esc_attr($_sub_delete_url); ?>"
									   data-confirmation="<?php echo esc_attr(__('Delete subscription? Are you sure?', $plugin->text_domain)); ?>"
									   title="<?php echo esc_attr(__('Delete Subscription', $plugin->text_domain)); ?>" class="text-danger"
										><?php echo __('Delete', $plugin->text_domain); ?> <i class="fa fa-times-circle"></i></a>
								</div>
							</td>
							<td>
								<?php if($_sub_post && $_sub_post_type_label): ?>
									<?php echo sprintf(__('%1$s ID# <a href="%2$s"><code>%3$s</code></a> <a href="%4$s">%5$s</a>', $plugin->text_domain), esc_html($_sub_post_type_label), esc_attr($_sub_post_url), esc_html($_sub->post_id), esc_attr($_sub_post_comments_url), esc_html($_sub_post_title_clip)); ?>
								<?php else: // Post no longer exists for whatever reason; display post ID only in this case. ?>
									<?php echo sprintf(__('Post ID# <code>%1$s</code>', $plugin->text_domain), esc_html($_sub->post_id)); ?>
								<?php endif; ?>

								<?php if($_sub->comment_id): ?><br />
									<i class="fa fa-level-up fa-rotate-90"></i>

									<?php if($_sub_comment): ?>
										<?php if($_subscribed_to_own_comment): ?>
											<?php echo sprintf(__('Replies to <a href="%1$s">your comment</a>; ID# <a href="%1$s"><code>%2$s</code></a> posted %3$s', $plugin->text_domain), esc_attr($_sub_comment_url), esc_html($_sub->comment_id), esc_html($_sub_comment_time_ago)); ?>
										<?php else: // It's not their own comment; i.e. it's by someone else. ?>
											<?php echo sprintf(__('Replies to <a href="%1$s">comment ID# <code>%2$s</code></a> posted %3$s', $plugin->text_domain), esc_attr($_sub_comment_url), esc_html($_sub->comment_id), esc_html($_sub_comment_time_ago)); ?>
										<?php endif; ?>
										<?php if($_sub_comment->comment_author): ?>
											<?php echo sprintf(__('by: <a href="%1$s">%2$s</a>', $plugin->text_domain), esc_attr($_sub_comment_url), esc_html($_sub_comment->comment_author)); ?>
										<?php endif; ?>
									<?php else: // Comment no longer exists for whatever reason; display comment ID only in this case. ?>
										<?php echo sprintf(__('Comment ID# <code>%1$s</code>', $plugin->text_domain), esc_html($_sub->comment_id)); ?>
									<?php endif; ?>

								<?php endif; ?>
							</td>
							<td>
								<?php echo esc_html($plugin->utils_i18n->subscr_type_label($_sub_type)); ?>
							</td>
							<td>
								<?php echo esc_html($plugin->utils_i18n->status_label($_sub->status)); ?>
							</td>
							<td>
								<?php echo esc_html($plugin->utils_i18n->deliver_label($_sub->deliver)); ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<script type="text/javascript">
				(function($)
				{
					'use strict'; // Strict standards enable.

					var plugin = {}, $window = $(window), $document = $(document),

						namespace = '<?php echo $plugin->utils_string->esc_js_sq(__NAMESPACE__); ?>',
						namespaceSlug = '<?php echo $plugin->utils_string->esc_js_sq(str_replace('_', '-', __NAMESPACE__)); ?>',

						ajaxEndpoint = '<?php echo $plugin->utils_string->esc_js_sq(home_url('/')); ?>',
						pluginUrl = '<?php echo $plugin->utils_string->esc_js_sq(rtrim($plugin->utils_url->to('/'), '/')); ?>';

					/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

					plugin.onReady = function() // On DOM ready handler.
					{
						/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

						$('.manage-summary').find('[data-action]').on('click', function(e)
						{
							e.preventDefault(), e.stopImmediatePropagation();

							var $this = $(this), data = $this.data();
							if(typeof data.confirmation !== 'string' || confirm(data.confirmation))
								location.href = data.action;
						});
						/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */
					};
					$document.ready(plugin.onReady); // On DOM ready handler.
				})(jQuery);
			</script>

		<?php endif; // end: display summary when no major errors. ?>

	</div>

<?php echo $site_footer; ?>