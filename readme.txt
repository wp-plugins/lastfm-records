=== Last.Fm Records ===
License: GPL2
Contributors: jeroensmeets
Donate link: http://amzn.com/w/2XZPC0CD6SILM
Tags: lastfm, last.fm, cd, cover, cd cover, plugin, widget, music, image, images, sidebar
Requires at least: 3.0
Tested up to: 3.4
Stable tag: 1.7

This plugin shows cd covers for cds your listened to, according to last.fm. It can show covers in a page or post, and you can add it as a widget.

== Description ==

This plugin shows cd covers on your Wordpress weblog. It connects to last.fm and grabs the list of cds you listened to recently and tries to find the cover images at last.fm.

== Installation ==

1. Upload the folder to the `wp-content/plugins` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure under `Settings` >> `Last.Fm Records`

4a. (widget) If you want to show the cd covers in your sidebar, go to the widgets settings and enable the widget. Here you can add a title for the widget.

4b. (shortcode) this plugin now has its own shortcode: `[lastfmrecords]`. It uses the global plugin settings, but comes with arguments to overrule them. More info on the arguments on the `Shortcode info` tab.

== Upgrade Notice ==

= 1.7 =

A completely rewritten plugin, tested up to WordPress 3.4. Comes with WordPress shortcodes, and the javascript file is now a jQuery plugin.

== Changelog ==

= 1.7 =

* restructured the code, the javascript now is a jQuery plugin
* added a WordPress shortcode
* changed "you have to" into "please" in the last.fm.records.js file

= todo list =

* a slideshow feature is in the works, but not offered yet.

= 1.6.2 =
* fixed periods `tracks7day`, `tracks6month`, `tracks12month` and `tracksoverall`

= 1.6.1 =
* last.fm added a new check, fixed it in the javascript
* moved donation notification below settings
* revised list of periods (see Options)

= 1.6 =
* plugin code adds jQuery dynamically when not included in theme
* rewritten configuration page using WordPress Settings API
* the plugin now uses the timezone you have set in WordPress
* added option to open links in new screen
* processed buglist

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

== Shortcode ==

The `[lastfmrecords]` shortcode gives you the opportunity to show cd covers in your posts. It uses the settings from the plugin, but you can add arguments to overrule them:

* `period="***"`: can be set to `recenttracks`, `lovedtracks`, `tracks7day`, `tracks3month`, `tracks6month`, `tracks12month`, `tracksoverall`, `topalbums7day`, `topalbums3month`, `topalbums6month`, `topalbums12month` and `topalbumsoverall`
* `count`: number of covers to show
* `stylesheet`: `simple` or `hover`
* `imgwidth`: width (and height) of the cd covers
* `user`: last.fm username
* `refreshmin`: time between updates (only works for period `recenttracks`)

== jQuery plugin ==

Starting with version 1.7, the javascript in this plugin is a jQuery plugin. It means you can use it on non-WordPress sites with a little bit of code:

1. include jQuery, and the 'last.fm.records.js' file from this plugin
2. add `<div id="lastfmrecords_elem"></div>` to your HTML (the id name is yours to choose, of course)
3. use this piece of jQuery to show covers
`
jQuery(document).ready( function() {
  jQuery("#lastfmrecords_elem").lastFmRecords(
    {"period": "recentttracks", "user": "lastfmusername", "count": "4"}
  );
});
`
4. check the shortcode options for all arguments.