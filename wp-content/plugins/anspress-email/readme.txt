=== AnsPress Email ===
Contributors: nerdaryan
Donate link: https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development
Tags: anspress, question, answer, q&a, forum, stackoverflow, quora
Requires at least: 3.5.1
Tested up to: 4.4
Stable tag: 1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Email notification extension for AnsPress plugin. Notify admin and community about activities.

== Description ==

"AnsPress Email" is an extension for AnsPress. This extension notify site admin and users about activities by email. Site admin can configure for what they wish to receive notification. Email message and subject can be easily configured from AnsPress option panel. Currently this extension send email in simple text. 

= List of activities for which notification are sent: =

1. New question (only admin)
2. New answer (admin and subscribers)
3. New comment (subscribers, as by default WordPress notify admin about new comments)
4. Best Answer (Selected answer author)
5. Edit Question (only admin)
6. Edit Answer (only admin)
7. Delete question (only admin)
8. Delete answer (only admin)
9. Tags subscription
10. Category subscription

== Installation ==

You can simply install it from WP plugin repo

e.g.

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'AnsPress Email'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `anspress_email.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `anspress_email.zip`
2. Extract the `anspress_email` directory to your computer
3. Upload the `anspress_email` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard


== Changelog ==

= 1.3 =
	* Fixed: replaced selecting_userid with het_current_user_id()
	* Removed call for ap_get_comments_subscribers_data
	* Notifications to subscribers on answers.
	* Deprecated ap_get_comments_subscribers_data
	* Deprecated function ap_get_question_subscribers_data
	* Replaced old ap_meta table query with ap_get_subscribers
	* Do not notify user of question edits
	* Typo, added comment excerpt.
	* Removed var_dump
	* Fix: MySql syntax error
	* Sanitized and escaped slash
	* Updated .mo
	* French translation added

