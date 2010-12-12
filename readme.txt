=== Last.Fm Records ===
Contributors: hondjevandirkie
Donate link: http://amzn.com/w/2XZPC0CD6SILM
Tags: lastfm, last.fm, cd, cover, cd cover, plugin, widget, music, image, images, sidebar
Requires at least: 3.0
Tested up to: 3.0.3
Stable tag: 1.6

This plugin shows cd covers for cds your listened to, according to last.fm. It can show covers in a page or post, and you can add it as a widget to you sidebar.

== Description ==

This plugin shows cd covers on your Wordpress weblog. It connects to last.fm and grabs the list of cds you listened to recently and tries to find the cover images at last.fm.

== Installation ==

1. Upload the folder to the `wp-content/plugins` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure under `Settings` >> `Last.Fm Records`

To determine where the cd covers are displayed, use one of the following:
4a. If you want to show the cd covers in your sidebar, go to the widgets settings and enable the widget. Here you can add a title for the widget.
4b. you can use [lastfmrecords|period|count] (for example [lastfmrecords|overall|4]) in your page/blogpost. It will be replaced by a list of covers with the same HTML as the widget one, so you can add the stylesheet in the settings. The period option can be set to `recenttracks`, `7day`, `3month`, `6month`, `12month`, `overall`, `topalbums` and `lovedtracks`.

== Upgrade Notice ==

= 1.6 =
Wow, this version has been a long time coming, 1.5.4 was released one year ago. The plugin is now more robust, and includes jQuery if your theme doesn't. If other scripts on your page generate an error, this plugin catches the error, so it can continue to show covers.

== Changelog ==

= 1.6 =
* plugin code adds jQuery dynamically when not included in theme
* rewritten configuration page using WordPress Settings API
* the plugin now uses the timezone you have set in WordPress
* added option to open links in new screen
* processed buglist

= todo list =

* make combination of [lastfmrecords|period|count] and widget possible

= 1.5.5 =
* added a check for the links back to last.fm

= 1.5.4 =  
* you can choose different styles (it's still possible to disable this and use your own stylesheet)
* changed code for widget functionality to the way it should be for WP2.8 and up
* name of div is no longer in settings, as it can be confusing
* [lastfmrecords|period|count] is back! Use it in your pages and posts!

= 1.5.3 =
* fixed an issue where the width of the image was not actually set

= 1.5.2 =  
* selecting a period is back
  
= 1.5.1 =  
* total rewrite, works again under PHP4
* now works under Wordpress 2.8
* can be used on any site without Wordpress (see readme.txt)
* auto refresh (in minutes) added to settings

== Use it without Wordpress ==

= Can I use this widget without Wordpress? =

Yes you can! It's a two step procedure:
1. include the javascript file from the zip in your webpage
2. check [this page on my site](http://jeroensmeets.net/wordpress/lastfmrecords/ "page with more info on configuring the javascript") for more info on configuring and calling the javascript.
