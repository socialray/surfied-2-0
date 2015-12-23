=== BP Show Friends ===
Contributors: imath
Donate link: http://imathi.eu/donations/
Tags: BuddyPress, friends, followers, widget
Requires at least: 3.7
Tested up to: 3.7.1
Stable tag: 2.0
License: GPLv2

Displays the friends of the logged in user or of the displayed user once in BuddyPress Member area

== Description ==

BP Show Friends requires <a href="http://buddypress.org/">BuddyPress</a> (1.8.1 since version 2.0 of the plugin) and adds a widget to display the friends of the logged in user or of the displayed member.
In the Appearence/Widgets menu of the WordPress administration, you can drag and drop the 'Friends' widget into the sidebar of your active theme. You can customize the number of avatars to display and their size.
In the front end, the widget will load the friends of the logged in user or if in BuddyPress member area, the user displayed friends.
Beneath the widget title, there are 2 links : one to show the friends of the user that are online and one to show the one that were recently actives.
There's a language directory where you can add your translations. As i'm french, i added my language.

* Thanks to <a href="http://buddypress.org/community/members/bluelf/">bluelf</a> Spanish translation is available.
* Thanks to <a href="http://buddypress.org/community/members/ultrix/">Ultrix</a> Brasilian Portuguease translation is available.
* Thanks to <a href="http://buddypress.org/community/members/martonisches/">Marten</a> German translation is now available.
* Thanks to <a href="http://buddypress.org/community/members/czz/">czz</a> Italian translation is now available.

== Installation ==

You can download and install BP Show Friends using the built in WordPress plugin installer. If you download BP Show Friends manually, make sure it is uploaded to "/wp-content/plugins/bp-show-friends/".

Activate BP Show Friends in the "Plugins" admin panel using the "Network Activate" (or "Activate" if you are not running a network) link.

== Frequently Asked Questions ==

= If you have any question =

Please add a comment <a href="http://imathi.eu/tag/bp-show-friends/">here</a>

== Screenshots ==

1. Customization of the widget from the Appearance/Widget Administration menu.
2. Widget in the front end (sidebar).

== Changelog ==

= 2.0 =
* requires at least BuddyPress 1.8.1
* fixes the ajax bug reported in plugin's forum by Vernon
* the total number of friends is now displayed after the link to all user's friends
* a hook allows you to add extra informations to avatars
* Avatar sizes can be customized from the widget administration panel
* CSS of the plugin can be overriden by theme adding a bp-show-friends.css file into a "css" folder

= 1.2 =
* adds a filter to change the displayed users order

= 1.1.2 =
* if BP > 1.2.10 change bp_is_member (deprecated) for bp_is_user (since BP 1.5)

= 1.1.1 =
* Italian translation added.
* corrects a bug when viewing displayed user all friends.

= 1.1 =
* Widget now always load and display the logged in user friends, or the user displayed friends once in BuddyPress member area.

= 1.0.3 =
* German language added.

= 1.0.2 =
* Brasilian Portuguease language added.

= 1.0.1 =
* Spanish language added.

= 1.0 =
* Widget birth..

== Upgrade Notice ==

= 2.0 =
* requires at least BuddyPress 1.8.1

= 1.2 =
nothing particular.

= 1.1.2 =
nothing particular.

= 1.1.1 =
nothing particular.

= 1.1 =
nothing particular.

= 1.0.3 =
no core upgrade, German language added.

= 1.0.2 =
no core upgrade, Brasilian Portuguease language added.

= 1.0.1 =
no core upgrade, spanish language added.

= 1.0 =
no upgrades, just a first install..
