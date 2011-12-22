=== Custom Registration Link ===
Contributors: GabSoftware
Donate link: http://www.gabsoftware.com/donate/
Tags: registration, link, custom, spam, bot, spam-bot, protect
Requires at least: 3.0.0
Tested up to: 3.2.0
Stable tag: 1.0.0

Let you modify the registration link of your WordPress website.

== Description ==

<strong>Introduction</strong>
<p>
Custom Registration Link let you modify the registration link of your WordPress website.
This will result in greatly reduced spam registrations.
The default registration link is "wp-login.php?action=register" and is heavily targeted by spam-bots.
This plugin will change this link to another one and make the old one invalid.
</p>

<strong>Reduce spam-bot registration</strong>
<p>
User agents who will try to access the normal registration link will be redirected to a page telling them
that registration is disabled (wp-login.php?registration=disabled), in order to reduce spam registrations
and with the potential benefit that learning spam-bots will not try next time as they will "think" that your website
doesn't accept registrations. Normal visitors will go to the new registration link.
The day I installed Custom Registration Link, all of my spam-bot registrations suddenly stopped.
</p>

<strong>Customizable</strong>
<p>
The new link is customizable in your WordPress dashboard,
and you are strongly advised to change the default value for something of your choice so that your website is unique.
</p>

<strong>This plugin requires PHP 5 and Wordpress 3.x</strong>

== Installation ==

<p>
Just extract the plugin directory into your wp-content/plugins directory.
</p>

== Frequently Asked Questions ==

= Is this plugin the ultimate spam-bot protection? =
Of course not, but it is a great additional protection against them.

= Can I put some HTML into the new link? =

No! The only valid characters are [a-zA-Z0-9_-] and in general any character valid in an URL-encoded string.

= Why isn't Custom Registration Link translated in my language? =

Most probably because nobody submitted a translation for your language yet. But you can help.
Read our guide for plugins translators at http://www.gabsoftware.com/tips/a-guide-for-wordpress-plugins-translators-gettext-poedit-locale/

== Screenshots ==

1. Custom Registration Link settings in the Wordpress administration area

== Changelog ==

= 1.0.0 =
* Initial release