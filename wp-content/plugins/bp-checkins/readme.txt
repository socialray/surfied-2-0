=== BP Checkins ===
Contributors: imath
Donate link: http://imathi.eu/donations/
Tags: BuddyPress, checkins
Requires at least: 3.4.1
Tested up to: 3.5.2
Stable tag: 1.2.2
License: GPLv2

BuddyPress plugin to post checkins or places.

== Description ==

BP Checkins is a plugin that uses HTML5 Geolocation API to publish checkins or places in a <a href="http://buddypress.org">BuddyPress</a> powered website.

In activity Component :

A location marker in the top right of the activity status box will show. On click, the browser will prompt the user for permission before detecting and braodcasting the location.
Once the check-in is posted to the activity stream, it will have its own permalink. On click, it will position the user on a map.
Members can also visit each other’s profiles to see a map of the user’s checkins at the top of the page.
Many thanks to Sarah Gooding for her <a href="http://wpmu.org/buddypress-location-check-ins-plugin-now-in-beta/" target="_blank">article on it on wpmu.org</a>

New since version 1.0 :

The superadmin can now activate a new checkins / places area where users will be able :

* to share their checkins and attach photos to it.
* to share places.
* import if they wish to their foursquare checkins.

This plugin is available in english, french and thanks to <a href="http://twitter.com/seluvega" target="_blank">@seluvega</a> in spanish.

http://vimeo.com/43250056

<strong>NB : make sure to activate the plugin in the network admin if you run a multisite WordPress</strong>

== Installation ==

You can download and install BP Checkins using the built in WordPress plugin installer. If you download BP Checkins manually, make sure it is uploaded to "/wp-content/plugins/bp-checkins/".

Activate BP Checkins in the "Plugins" admin panel using the "Network Activate" (or "Activate" if you are not running a network) link.

== Frequently Asked Questions ==

= If you have any question =

Please add a comment <a href="http://imathi.eu/tag/bp-checkins/">here</a>

== Screenshots ==

1. Map once position found.
2. photo attached to a checkin
3. Community places.
4. attach a photo, a checkin, a comment to a place.
5. Customize the image of a place category

== Changelog ==

= 1.2.2 =
* Small fix in order to adapt the plugin to normalized way of naming BuddyPress cookies in 1.8

= 1.2.1 =
* corrects a bug in the comments of the component places

= 1.2 =
* brings support for BuddyPress Theme Compat

= 1.1 =
* corrects a js bug when on friendship requests page
* now the places component can be used with or without BuddyPress group component activated
* info box displaying the checked-in friends of the loggedin user on places.

= 1.0 =
* new checkins and places area
* add a photo to checkins or place comments
* live commenting for live places
* foursquare import
* language supported : french, english, spanish

= 0.1 =
* use HTML5 geolocation API to add checkins
* language supported : french, english, dutch, persian
* Plugin birth..

== Upgrade Notice ==

= 1.2.2 =
make sure to have at least BuddyPress 1.7 installed, and to back up your db before upgrading

= 1.2.1 =
make sure to have at least BuddyPress 1.7 installed, and to back up your db before upgrading

= 1.2 =
make sure to have at least BuddyPress 1.7 installed, and to back up your db before upgrading

= 1.1 =
make sure to have at least BuddyPress 1.5.6 installed, and to back up your db before upgrading

= 1.0 =
make sure to have at least BuddyPress 1.5.6 installed before upgrading

= 0.1 =
no upgrades, just a first experiment..