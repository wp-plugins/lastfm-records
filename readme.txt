=== Last.Fm Records ===
Contributors: hondjevandirkie
Tags: lastfm, last.fm, cd, cover, cd cover, plugin, widget, music, image, images, sidebar
Requires at least: 2.8
Tested up to: 2.9
Stable tag: 1.5.4

This plugin shows cd covers for cds your listened to, according to last.fm. It can behave as a widget.

== Description ==

This plugin shows cd covers on your Wordpress weblog. It connects to last.fm and grabs the list of cds you listened to recently and tries to find the cover images at last.fm.

== Installation ==

1. Upload the folder to the `wp-content/plugins` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure under `Settings` >> `Last.Fm Records`

To determine where the cd covers are displayed, use one of the following:
4a. If you want to show the cd covers in your sidebar, go to the widgets settings and enable the widget. Here you can add a title for the widget.
4b. you can use [lastfmrecords|period|count] (for example [lastfmrecords|overall|4]) in your page/blogpost. It will be replaced by a list of covers with the same HTML as the widget one, so you can add the stylesheet in the settings. The period option can be set to `recenttracks`, `7day`, `3month`, `6month`, `12month`, `overall`, `topalbums` and `lovedtracks`.

== Changelog ==  

= Planned for a next version =
* add jQuery dynamically when not included in theme
* make combination of [lastfmrecords|period|count] and widget possible

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

Yes you can! Just include the javascript file from the zip and call it from your webpage.

An example:

<div id="lastfmrecords"></div>
<!-- do not forget to include jQuery if it is not already included -->
<script type='text/javascript' src='/PATH/TO/jquery.js'></script>
<script type='text/javascript' src='/PATH/TO/last.fm.records.js'></script>
<script type="text/javascript">
  jQuery(document).ready( function() {
  var _config = {
    username: 'YOURUSERNAME', // last.fm username
    count: 10,                // number of images to show
    period: '3month',         // period to get last.fm data from
    refresh: 1,               // when to get new data from last.fm (in minutes)
    offset: 1                 // difference between your timezone and GMT.
  };
 lastFmRecords.init(_config);
</script>

The period option can be set to `recenttracks`, `7day`, `3month`, `6month`, `12month`, `overall`, `topalbums` and `lovedtracks`.
