=== Last.Fm Records ===
Contributors: hondjevandirkie
Tags: lastfm, last.fm, cd cover, amazon, plugin, widget, music, images, sidebar
Requires at least: 2.0
Tested up to: 2.8.1
Stable tag: 1.5.2

This plugin shows cd covers for cds your listened to, according to last.fm. It can behave as a widget.

== Description ==

This plugin shows cd covers on your Wordpress weblog. It connects to last.fm and grabs the list of cds you listened to recently and tries to find the cover images at last.fm.

== Installation ==

1. Upload the folder `last.fm` to the `wp-content/plugins` directory.
2. Make sure jQuery is included in your template. If it is not, you can add the following line to header.php in your theme

`<script type='text/javascript' src='/wp-includes/js/jquery/jquery.js'></script>`

If your blog is in a subdirectory, it should be changed to

`<script type='text/javascript' src='/SUBDIRECTORY/wp-includes/js/jquery/jquery.js'></script>`
   
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure under `Settings` >> `Last.Fm Records`
5. If you want to show the cd covers in your sidebar and your Wordpress installation is widget-ready, go to the widgets settings and enable the widget. Here you can add a title for the widget.

== Use it without Wordpress ==

= Can I use this widget without Wordpress? =

Yes you can! Just include the javascript file from the zip and call it from your webpage.

An example:

<div id="lastfmrecords"></div>
<script type='text/javascript' src='/PATH/TO/jquery.js'></script>
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

You also need some styling to get things nice and shiny:

<style type="text/css">
  #lastfmrecords        { padding: 0px; padding-bottom: 10px; }

  /* thx to http://cssglobe.com/lab/overflow_thumbs/ */
  #lastfmrecords ol,
    #lastfmrecords li        { margin: 0; padding: 0; list-style: none; }
  #lastfmrecords li          { float: left; margin: 0px 5px 5px 0px; }
  #lastfmrecords a           { display: block; float: left; width: 100px; height: 100px;
                               line-height: 100px; overflow: hidden; position: relative; z-index: 1; }
  #lastfmrecords a img       { float: left; position: absolute; margin: auto; min-height: 100px; }

  /* mouse over */
  #lastfmrecords a:hover     { overflow:visible; z-index:1000; border:none; }
  #lastfmrecords a:hover img { border: 1px  solid #999; background: #fff; padding: 3px; 
                               margin-top: -20px; margin-left: -20px; min-height: 120px;  }
</style>
