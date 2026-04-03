<?php
/*
* Plugin Name: Honeypot comments
* Description: Visually hide comments, but allow bots to inject comments
* Version: 0.1
* Author: gqqnbig
* Requires PHP:
*/


class HoneypotComments
{
	public function __construct()
	{
		add_action('comment_form_before', array($this, 'hide_comment_form_before'));
		add_action('comment_form_after', array($this, 'hide_comment_form_after'));
		add_filter('pre_comment_approved', array($this, 'pre_comment_approved'), 5, 2);
	}


	function hide_comment_form_before()
	{
		echo '<div>鉴于大量垃圾留言，留言表单已经被关闭。请发邮件到<span id="comment-email1" tabindex="0">antitrust</span>@<span id="comment-email2" tabindex="0">ftc.gov</span>发表评论。</div>';
		echo '<script>';
		echo 'jQuery("#comment-email1").one("mouseover focus", function(){this.innerText="gqqnb2005"});';
		echo 'jQuery("#comment-email2").one("mouseover focus", function(){this.innerText="gmail.com"});';
		echo '</script>';
		echo '<div style="width: 1px; height: 1px; overflow: hidden;">';
	}


	function hide_comment_form_after()
	{
		echo '</div>';
	}

	function pre_comment_approved($approved, $commentdata)
	{
		// Check for pingbacks only
		if (isset($commentdata['comment_type']) && $commentdata['comment_type'] === 'pingback') {
			if (!empty($commentdata['user_ID'])) {
				$user = get_userdata($commentdata['user_ID']);

				if ($user && in_array('administrator', (array)$user->roles, true)) {
					// Approve pingback from admin
					return 1;
				}
			}
		}

		if (!empty($commentdata['comment_author_IP'])) {
			// Read the log from  /var/log/nginx/error.log
			//error_log( 'pre_comment_approved shell_exec: ' . shell_exec('sudo --non-interactive /usr/sbin/ufw status 2>&1'));
			exec("sudo --non-interactive /usr/sbin/ufw insert 1 deny from $commentdata[comment_author_IP]/24 comment wordpress");
			// HTTP connection is usually persistent.
			exec("sudo --non-interactive /usr/sbin/conntrack -D -s $commentdata[comment_author_IP]");
		}

		//return new WP_Error('db_update_error', 'Not enough disk space', 500);
		return 'trash';
	}
}

new HoneypotComments();
