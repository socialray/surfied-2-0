=== Categories for AnsPress ===
Contributors: nerdaryan
Donate link: https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development
Tags: anspress, question, answer, q&a, forum, stackoverflow, quora
Requires at least: 4.1.1
Tested up to: 4.4
Stable tag: 1.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add category support in AnsPress.

== Description ==

Support forum: http://anspress.io/questions/

Categories for AnsPress is an extension for AnsPress, which add category (taxonomy) support for questions. This extensions will add two pages in AnsPress:

* Categories page (list all categories of AnsPress)
* Category page (Single category page, list questions of a specfic category)

This extension will also add categories widget, which can be used to to show questions categories anywhere in WordPress.

== Installation ==

Simply go to WordPress plugin installer and search for categories for anspress and click on install button and then activate it.

Or if you want to install it manually simple follow this:

    * Download the extension zip file, uncompress it.
    * Upload categories-for-anspress folder to the /wp-content/plugins/ directory
    * Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 1.4 =

* Removed subscription page tab
* Fix: query var
* Update language pot
* Added de_DE
* Fixed subscribe button
* Set subscribe button type
* Added option for categories page order and orderby
* Add warning message if AnsPress version is lower then 2.4-RC
* Added option to change category and categories slug
* Move “Categories title” from “layout” to ”pages”.
* Updated fr mo
* Support utf8 in permalink and show 404 if category not found
* Added trkish translation by nsaral


= 1.3.9 =

* Added turkish translation and fixed textdomain
* Improved category.php
* added widget wrapper div
* Removed Question category from title
* Fixed wrong callback
