=== Last.Fm Records ===
Contributors: hondjevandirkie
Donate link: http://dirkie.nu/
Tags: lastfm, last.fm, cd cover, amazon
Requires at least: 2.0
Tested up to: 2.1
Stable tag: 1.0

This plugin shows cd covers from your Last.FM account.

== Description ==

This plugin shows cd covers on your Wordpress weblog. It connects to last.fm and 
grabs the list of cds you listened to recently and tries to find the cover images 
at Amazon. To speed things up, it only fetches these data once a day and keeps a 
local copy for the rest of the day.

My widget Run For Cover does exactly the same, so if you prefer a widget look for it in the Wordpress Plugin directory.

== Installation ==

1. Upload the folder `last.fm` to the `wp-content/plugins` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure under `Options` >> `Last.Fm Records`
4. Use `<?php lastfmrecords_display(); ?>` in your templates

   This function accepts two (optional!) arguments: `period` (recenttracks, weekly, 3month, 6month, 12month or overall) and `count` (any number bigger than 0)
   
   For example: `<?php lastfmrecords_display('weekly', 4); ?>` displays the 4 cds you listened to the most in the last week. If you do not add these options, the values from the options page will be used.

== Frequently Asked Questions ==

= Why does the plugin show no images for my last.fm username? = 

Please note that if you registered less than 7 days ago at last.fm, your plugin will 
display no images. Last.fm regenerates the needed pages usually on Sunday or Monday.

And there is always the `recent tracks` setting for `period`.

= What are all this options I get? =

* **Last.fm username**: your username. Default is my username, so if you like Randy Newman cd covers, do not change it ;-)
* **Period**: last.fm can go back in time for the cds you listened to. Leave it set to weekly if you want to see lots of different cd covers.
* **Max. displayed**: the maximum number of cd covers you want. As not all cds have cover images at Amazon, you will not always get the exact number you set here.
* **Thumbnail size**: the desired width and height of the images. You can set this to zero and use img.runforcover in your stylesheet.If you do not know what this means, try some numbers between 75 and 160.
* **Text when no images**: when the plugin can find no images, this text is used. In rare occassions this message is used as error message.
* **Link to**: you can choose where the images link to: Last.fm (default) or Amazon. If you choose to link to the Amazon product page, please note that this includes my associate id for Amazon.

= Does your plugin support multi-user wordpress installations? =

The widget this plugin was based on supports Wordpress MU. I haven't had a chance to test this plugin.
