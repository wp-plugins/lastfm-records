=== Last.Fm Records ===
Contributors: hondjevandirkie
Donate link: http://dirkie.nu/
Tags: lastfm, last.fm, cd cover, amazon, plugin, widget
Requires at least: 2.0
Tested up to: 2.2
Stable tag: 1.2

This plugin shows cd covers for cds your listened to, according to last.fm. It can behave as a widget.

== Description ==

This plugin shows cd covers on your Wordpress weblog. It connects to last.fm and grabs the list of cds you listened to recently and tries to find the cover images at last.fm. To speed things up, it only fetches these data once a day and keeps a local copy for the rest of the day.

You can upload cd cover images yourself if you want to on the options page.

== Installation ==

1. Upload the folder `last.fm` to the `wp-content/plugins` directory.

   The main script should be at `/wp-content/plugins/last.fm/last.fm.php`. If you put it in the widgets directory, the plugin will not work!

2. Make sure the `cache` folder exists in the `last.fm` folder and that PHP can write files in it
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure under `Plug-ins` >> `Last.Fm Records`
5. If you want to show the cd covers in your sidebar and your Wordpress installation is widget-ready, go to the widgets settings and drag the widget on the sidebar. You can set the title to use here.
6. In all other cases, use `<?php lastfmrecords_display(); ?>` in your templates

   This function accepts two (optional!) arguments: `period` (recenttracks, weekly, 3month, 6month, 12month or overall) and `count` (any number bigger than 0)
   
   For example: `<?php lastfmrecords_display('weekly', 4); ?>` displays the 4 cds you listened to the most in the last week. If you do not add these, the values from the options page will be used.

== Frequently Asked Questions ==

= Why does the plugin show no images for my last.fm username? = 

Please note that if you registered less than 7 days ago at last.fm, your plugin will display no images. Last.fm regenerates the needed pages usually on Sunday or Monday.

The obvious exception is the `recent tracks` setting for `period`.

= What's the thing with uploading images myself? =

If the plugin can't find an image for a cd, it will add it to the list of missing covers on the options page. If you click on the title, a little form to upload an image will appear. Beware that the image will be saved in the cache folder, so uploading big images can take up lots of disk space.

You can try finding the image by clicking the `[find image]` link.

= How do I clear the cache? =

Use an FTP program and navigate to the cache folder. Select all files and delete them.

= What are all this options I get? =

* **last.fm username**: your username at last.fm.
* **period**: last.fm can go back in time for the cds you listened to. Leave it set to weekly if you want to see lots of different cd covers.
* **how to display the images**: three different layouts, try them out! The `First image twice as big` only looks nice if you make sure there are two cd images per row.
* **image count**: the maximum number of cd covers you want. As not all cds have cover images at Amazon, you will not always get the exact number you set here.
* **image width**: the desired width and height of the images. You can set this to zero and use `img.cdcover` in your stylesheet. If you do not know what this means, try some numbers between 75 and 130.
* **error message**: when the plugin can find no images, this text is used. In rare occassions this message is used as error message.

= What kind of HTML does the plugin produce? =

I try to make all my plugins XHTML 1.0 strict.

= Thank you's =

Jim Smart did some great suggestions.
Martijn de Waal helped with testing.