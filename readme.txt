=== Last.Fm Records ===
License: GPL2
Contributors: jeroensmeets
Donate link: http://amzn.com/w/2XZPC0CD6SILM
Tags: lastfm, last.fm, cd, cover, cd cover, plugin, widget, music, image, images, sidebar
Requires at least: 3.0
Tested up to: 4.1.1
Stable tag: 1.7.8

Last.Fm Records shows cd covers for cds your listened to, according to last.fm. It can show covers in a page or post, and you can add it as a widget.

== Description ==

This plugin shows cd covers on your WordPress site. It connects to last.fm and grabs the list of cds you listened to recently and tries to find the cover images at last.fm.

== Installation ==

1. Upload the folder to the `wp-content/plugins` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure under `Settings` >> `Last.Fm Records`

4a. (widget) If you want to show the cd covers in your sidebar, go to the widgets settings and enable the widget. Here you can add a title for the widget.

4b. (shortcode) this plugin now has its own shortcode: `[lastfmrecords]`. It uses the global plugin settings, but comes with arguments to overrule them. More info on the arguments under `Shortcode info`.

== Frequently Asked Questions ==

= Why doesn't it work? =

It's not easy to answer this question. Lots of testing has been done to make sure the plugin works in different browsers, and as part of different themes. If you are not seeing cd covers on your site, there are a couple of things you can check:

= Did you add a widget or shortcode? =

Installing the plugin doesn't add anything to your site. You have to specify where the cd covers will be added. If your theme supports widgets, you can use them. To show the cd covers on a page or post, shortcodes are available.

= Do you see javascript errors? =

The plugin depends on javascript (a programming language in your browser) to display the cd covers. If another script in your page is generating errors, this can prevent this plugin from working correctly.

= Do you use a caching plugin? =

Some caching plugins (like W3 Total Cache) combine different javascript files into one. Before contacting me for help, please disable the caching and check if this resolves the issue.

= Other reasons =

If you still have troubles, please set the debug option in the settings to "yes" and check the [javascript console](http://webmasters.stackexchange.com/questions/8525/how-to-open-the-javascript-console-in-different-browsers).

= Can I make a suggestion for the plugin? =

Thanks to all the great feedback for this plugin, it is what it is now. I try to answer all questions in the [forums](http://wordpress.org/support/plugin/lastfm-records) and keep the users of this plugin happy. Strangely enough, [this list](http://amzn.com/w/2XZPC0CD6SILM) sees little action.

== Changelog ==

= 1.7.8 =

* fixed a warning when stylesheet was not set in the shortcode
* tested under WordPress 4.1.1

= 1.7.7 = 

* switched to https for requests to last.fm api
* added option to set your own last.fm api key
Thanks to wordpress.org user [sunpig](https://wordpress.org/support/topic/https-for-call-to-audioscrobbler-and-api-key?replies=3) for both suggestions.

= 1.7.6 = 

* cleaned up some messy code
* fixed a bug in Safari
* fixed: jQuery logged to console even when told not to in the settings

= 1.7.5 =

* default thumbnail image no longer at last.fm, yet in the plugin folder.
* tested with WordPress 4.0
* show bigger images when available at last.fm
* added icons and header image to plugin

= 1.7.4 =

* fixed a bug where album covers were not shown for your topalbums (thanks to wordpress.org user nszumowski for solving the bug for me)

= 1.7.3 =

* added topArtists to the period options. Thanks to [matthew_darcy](http://www.last.fm/user/matthew_darcy) for suggesting it.
* fixed some typos in the readme.txt

= 1.7.2 =

Fixed the stylesheet issue that was fixed in 1.7.1

= 1.7.1 =

* only load javascript when widget or shortcode is found on the page
* get last.fm username from settings when not specified in shortcode
* stylesheet fixes (resizing of covers)
* fixed logging to console (doubt anyone cares but me)

= 1.7 =

* restructured the code, the javascript now is a jQuery plugin
* added a WordPress shortcode
* changed "you have to" into "please" in the last.fm.records.js file

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

== WordPress Shortcode ==

The `[lastfmrecords]` shortcode gives you the opportunity to show cd covers in your posts. It uses the settings from the plugin, but you can add arguments to overrule them:

* `period="***"`: can be set to `recenttracks`, `lovedtracks`, `tracks7day`, `tracks3month`, `tracks6month`, `tracks12month`, `tracksoverall`, `topalbums7day`, `topalbums3month`, `topalbums6month`, `topalbums12month` and `topalbumsoverall`
* `count`: number of covers to show
* `stylesheet`: `simple` or `hover`
* `imgwidth`: width (and height) of the cd covers
* `user`: last.fm username
* `refreshmin`: time between updates (only works for period `recenttracks`)
* `ownapikey`: use your own Last.Fm API key

For example:

`[lastfmrecords user="xample" period="lovedtracks" count="14" stylesheet="hover"]`

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