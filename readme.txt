=== Last.Fm Records ===
Contributors: hondjevandirkie
Tags: lastfm, last.fm, cd cover, amazon, plugin, widget, music, images, sidebar
Requires at least: 2.0
Tested up to: 2.8.1
Stable tag: 1.5

This plugin shows cd covers for cds your listened to, according to last.fm. It can behave as a widget.

== Description ==

This plugin shows cd covers on your Wordpress weblog. It connects to last.fm and grabs the list of cds you listened to recently and tries to find the cover images at last.fm. To speed things up, it only fetches these data once a day and keeps a local copy for the rest of the day.

== Installation ==

1. Upload the folder `last.fm` to the `wp-content/plugins` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure under `Settings` >> `Last.Fm Records`
4. If you want to show the cd covers in your sidebar and your Wordpress installation is widget-ready, go to the widgets settings and enable the widget. Here you can add a title for the widget.
6. If you want to display the cd covers on a wordpress page or in a post, add

   `[lastfmrecords]`
   
   to the page. This text will be replaced by the images. If you don't want the values from the options page, you can override them using pipes:

   `[lastfmrecords|x|y]`

   where x is the period and y the number of covers you want to display. So for example:

   `[lastfmrecords|recenttracks|1]`

   will display the cover of the last cd you listened to.
7. In all other cases, use `<?php lastfmrecords_display(); ?>` in your templates

   This function accepts two (optional!) arguments: `period` (recenttracks, weekly, 3month, 6month, 12month, overall or lovedtracks) and `count` (any number bigger than 0)
   
   For example: `<?php lastfmrecords_display('weekly', 4); ?>` displays the 4 cds you listened to the most in the last week. If you do not add these, the values from the options page will be used.

== Frequently Asked Questions ==

= What are all this options I get? =

* **last.fm username**: your username at last.fm.
* **add stylesheet**: the plugin can add some layout info.
* **image count**: the maximum number of cd covers you want.
* **image width**: the desired width and height of the images. You can set this to zero and use `img.cdcover` in your stylesheet. If you do not know what this means, try some numbers between 75 and 130.

== todo list ==

1. 'Get more images if necessary', that is getting info from last week/month/ etc. is not working in version 1.5. Prio 1.
2. Using [lastfmrecords|x|y] in a page or post doesn't work in version 1.5.
3. fix highslide/lightbox option