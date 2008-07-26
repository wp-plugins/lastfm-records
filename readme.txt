=== Last.Fm Records ===
Contributors: hondjevandirkie
Tags: lastfm, last.fm, cd cover, amazon, plugin, widget, music, images, sidebar
Requires at least: 2.0
Tested up to: 2.6
Stable tag: 1.4

This plugin shows cd covers for cds your listened to, according to last.fm. It can behave as a widget.

== Description ==

This plugin shows cd covers on your Wordpress weblog. It connects to last.fm and grabs the list of cds you listened to recently and tries to find the cover images at last.fm. To speed things up, it only fetches these data once a day and keeps a local copy for the rest of the day.

== Installation ==

1. Upload the folder `last.fm` to the `wp-content/plugins` directory.

   The main script should be at `/wp-content/plugins/last.fm/last.fm.php`.

2. Make sure the `cache` folder exists in the `last.fm` folder and that PHP can write files in it
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure under `Plug-ins` >> `Last.Fm Records`
5. If you want to show the cd covers in your sidebar and your Wordpress installation is widget-ready, go to the widgets settings and enable the widget. You can set the title to use here.
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

= How do I clear the cache? =

Use an FTP program and navigate to the cache folder. Select all files and delete them.

= What are all this options I get? =

* **last.fm username**: your username at last.fm.
* **period**: last.fm can go back in time for the cds you listened to. Loved tracks is new in version 1.4.
* **add stylesheet**: the plugin adds some layout info by default. You can that off here.
* **image count**: the maximum number of cd covers you want. As not all cds have cover images at Amazon, you will not always get the exact number you set here.
* **get more images if necessary**: the new kid on the block in 1.4. If the plugin doesn't have enough images to display and this option is set to 'yes', it will look for more cd's  on last.fm. So if you have set 'image count' to 6 and 'period' to weekly and the plugin finds 4 cd covers, it switches to the 3month period and adds the first 3 images it finds there. This way, you always have the same amount of images on your site. One exception: if you set the period to 'lovedtracks', this option doesn't do nuttin'.
* **image width**: the desired width and height of the images. You can set this to zero and use `img.cdcover` in your stylesheet. If you do not know what this means, try some numbers between 75 and 130.
* **error message**: when the plugin can find no images, this text is used. In rare occassions this message is used as error message.

= What kind of HTML does the plugin produce? =

I try to make all my plugins XHTML 1.0 strict. One funny thing: if you use this plugin as a widget and call it from a page as well, two ol's with the same id's are generated.

== todo list ==

1. uploading images has been disabled in 1.4
2. selecting a style has been disabled.
3. Make an option to use Lightbox or its siblings.
3. clean up the svn trunk structure to enable automatic upgrading of the plugin (needs changes of directory name inside the plugin as well).
4. make the refresh time for the cache an option (e.g. look up recent tracks every 15 minutes)
