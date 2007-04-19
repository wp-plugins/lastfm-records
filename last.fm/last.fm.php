<?

# Plugin Name: Last.Fm Records
# Version: 1.0
# Plugin URI: http://dirkie.nu/
# Description: The Last.Fm Records plugin lets you show what you are listening to, with a little help from our friends at last.fm.
# Author: Dog Of Dirk
# Author URI: http://dirkie.nu/

# TODO

# linkto option
# frontend function
# behave as widget
# preview in backend
# several stylesheets for special ways of showing covers



function lastfmrecords_init() {
	#
}

function lastfmrecords_display($period = false, $count = false) {
  $options = get_option('lastfm-records');
  if ($period) {
    $options['period'] = $period;
  }
  if ($count) {
    $options['count'] = $count;
  }

  # echo "\n\n<h2>" . $options['title'] . "</h2>\n";
  echo "  <span id=\"lastfmrecords\">\n";
  echo lastfmrecords_gethtml($options);
  echo "  </span>\n\n";
}

function lastfmrecords_gethtml($options) {
  $_cachefile = lastfmrecords_cachefilename($options);

  # has the html already been generated?
  if (file_exists($_cachefile)) {
    # cachefile exists
    return file_get_contents($_cachefile);
  } else {
    # no; go fetch the recent list from last.fm and the images from Amazon
    return lastfmrecords_refreshhtml($options);
  }
}

function lastfmrecords_refreshhtml($options) {
  # this function:
  #   1. connects to last.fm to find the cds the user has listened to and parses the html
  #      (yes, it doesn't use the never updating xml-feed)
  #   2. for each cd, connects to Amazon to find the url of image of the cd cover
  #      (it doesn't download the images itself!)
  #   3. generates the HTML with the urls for the imnages in it
  #   4. if ok, saves the html to the cache directory and returns it
  #   5. if not ok, returns the error text that has been set in the options

  $_last_fm_url = 'http://www.last.fm/user/' . $options['username'] . '/charts/?subtype=album&charttype=' . $options['period'];
  $_url_contents = lastfmrecords_loadurl($_last_fm_url);

  if (false == $_url_contents) {
  	# feed not available, let's hope it's there next time.
  	return $options['noimages'];
  }

  # parse the html from last.fm
  # this is what we call screenscraping
  $_albums = explode('<a href="/music/', $_url_contents);
  array_shift($_albums);

  $_ta = array();
  foreach ($_albums as $_k => $_v) {
    
    $_v         = substr($_v, 0, strpos($_v, '"'));
    $_parts     = explode('/', $_v);

    $_artist    = $_parts[0];
    if ('recenttracks' == $options['period']) {
    	# under 'recenttracks', the title is for the song, not the cd
    	# this function tries to find the cd it's on
    	$_cdtitle = lastfmrecords_findcdfortrack($_parts[2], $_parts[0]);
    	if (!$_cdtitle) {
    		# no cd found where this track appears on
    		continue;
    	} else {
    		# found
    		$_cdtitle = urlencode($_cdtitle);
    	}
    } else {
      $_cdtitle = $_parts[1];
    }

    # make sure every cd gets listed once
    $_ta[$_cdtitle] = $_artist;
  }

  $_nr_displayed = 0;
  $_result = "";
  
  foreach($_ta as $_cdtitle => $_artist) {  
    if ($_nr_displayed >= intval($options['count'])) {
      break;
    }

    # let's find the cd cover
    $_imghtml = lastfmrecords_getimagehtml($_cdtitle, $_artist, $options);

    if ($_imghtml) {
      $_result = $_result . $_imghtml . "\n";
    	$_nr_displayed++;
    }
  }

  # save result to cache?
  if ('' != $_result) {
  	# debug info
    $_result = $_result . "\n  <!--\n";
    $_result = $_result . "    number of songs in " . $options['period'] . " list: " . count($_albums) . "\n";
    $_result = $_result . "    maximum number to display: " . $options['count'] . "\n";
    $_result = $_result . "    last.fm username: " . $options['username'] . "\n";
    $_result = $_result . "  -->\n";

    $_file = @fopen(lastfmrecords_cachefilename($options), 'w');
    if ($_file) {
      fwrite($_file, $_result, strlen($_result));
      fclose($_file);
    }

    # delete old html files in cache
    lastfmrecords_cleanupcache(lastfmrecords_cachedir(), $options['username']);
  } else {
  	# no need to save error messages to the cache
  	$_result = $options['noimages'];
  }

	return $_result;
}

function lastfmrecords_findcdfortrack($_title, $_artist) {
  $_apikey = '17CBJCAMVX5V38CR0F02';

	$_r = "http://webservices.amazon.com/onca/xml?Service=AWSECommerceService&SearchIndex=MusicTracks&" . 
	      "AWSAccessKeyId=" . $_apikey . "&Operation=ItemSearch&ResponseGroup=Small&" . 
	      "Keywords=" . $_title;

	$_amazon_xml = lastfmrecords_loadurl($_r);

  if (!$_amazon_xml) {
		return false;
	}

  $_artist = urldecode($_artist);

  # terrible way of parsing XML
  $_items = explode('<Item>', $_amazon_xml);
	array_shift($_items);
  foreach ($_items as $_k => $_v) {
    if (false !== strpos($_v, $_artist)) {
    	# echo "<!-- $_title van $_artist staat op " . rfc_stringBetween($rfc_v, '<Title>', '</Title>') . "-->\n";
    	return lastfmrecords_stringbetween($_v, '<Title>', '</Title>');
    } else {
    	# echo "<!-- $_title van $_artist staat niet op " . rfc_stringBetween($rfc_v, '<Title>', '</Title>') . "-->\n";
    }
  }

  return false;
}

function lastfmrecords_getimagehtml($_title, $_artist, $options) {

  if (('' == $_title) || ('' == $_artist)) {
  	return false;
  }

  $_AMZimg = lastfmrecords_getamazonimagedata($_title, $_artist);

  if (!is_array($_AMZimg)) {
  	return false;
  }

  $_safe_title  = str_replace("'", "`", urldecode($_title));
  $_safe_artist = str_replace("'", "`", urldecode($_artist));

  $_imgHTML  = "<img src='" . $_AMZimg['image'] . "' ";
  $_imgHTML .= "title='" . $_safe_title . ": " . $_safe_artist . "' ";
  $_imgHTML .= "alt='cover of cd " . $_safe_title . " by " . $_safe_artist . "' ";

  # people using their own class in css?
  if (0 == $options['imgwidth']) {
    $_imgHTML .= "class='cdcover' ";
  } else {
  	# no -- so give the image the width specified and add some margin to keep them apart
    $_imgHTML .= "style='height: " . $options['imgwidth'] . "px; width: " . $options['imgwidth'] . "px; margin: 0px 5px 5px 0px;' ";
  }
  $_imgHTML .= "/>";

  // add Last.fm link
  $_imgHTML = "    <a href='http://www.last.fm/music/" . $_artist . "/" . $_title . "/'>\n      " . $_imgHTML . "\n    </a>";

  return $_imgHTML;
}

function lastfmrecords_getamazonimagedata($_title, $_artist) {
  $_apikey = '17CBJCAMVX5V38CR0F02';

  $_r = "http://webservices.amazon.com/onca/xml?Service=AWSECommerceService&SearchIndex=Music&" . 
        "AWSAccessKeyId=" . $_apikey . "&Operation=ItemSearch&ResponseGroup=Images&" .
        "Title=" . urlencode($_title) . "&Artist=" . urlencode($_artist);

  $_amazon_xml = lastfmrecords_loadurl($_r);

  $_url = false;

  if ($_amazon_xml) {
    $_url = lastfmrecords_stringbetween($_amazon_xml, '<MediumImage>', '</MediumImage>');
    # $_url = lastfmrecords_stringbetween($_amazon_xml, '<SmallImage>', '</SmallImage>');
    if ($_url) {
    	$_largeurl = lastfmrecords_stringbetween($_amazon_xml, '<LargeImage>', '</LargeImage>');
      $_largeimage = ($_largeurl) ? lastfmrecords_stringbetween($_largeurl, "<URL>", "</URL>"): false;
    	return array(
               "asin"       => lastfmrecords_stringbetween($_amazon_xml, '<ASIN>', '</ASIN>'),
               "image"      => lastfmrecords_stringbetween($_url, "<URL>", "</URL>"),
               "largeimage" => $_largeimage
             );
    }
  }

  return false;
}

function lastfmrecords_loadurl($_url) {
  $_result = false;

  # added curl for Dreamhost etc.
  if (function_exists('curl_exec')) {
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $_url);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $_result = curl_exec($ch);
    curl_close($ch);
  } else {
    $fp = @fopen($_url, 'r');
    if ($fp) {
      $_result = "";
      while ($data = fgets($fp)) {
        $_result .= $data;
      }
      fclose($fp);
    }
  }
  
  return $_result;
}

function lastfmrecords_cleanupcache($_dir, $_lastfmusername) {
	# in theory, in multi user wordpress environments,
	# diffent blogs can display cd covers from the same
	# last.fm user
	
	# so we keep all cache files, except the ones for the current last.fm user that are not for today

	# this means when a user has cache files for "recenttracks", the cache will keep
	# 24 files before deleting them. oh well.. the recenttracks option is hidden for now anyway.
  if ($handle = opendir($_dir)) {
    while (false !== ($_file = readdir($handle))) {
      # first: is this a cache file? I would like to be able to add covers by hand for cds Amazon
      # doesn't know in a future version, so we skip everything that's not cached html
      if ("cache" == substr($_file, -5)) {
      	# ok, it's cached html. is it for the current last.fm user?
        if ($_lastfmusername == substr($_file, 0, strlen($_lastfmusername))) {
          # now, if it's not from today, we can delete it
          if (false === strpos($_file, "." . date("ymd"))) {
            @unlink($_dir . $_file);
          }
      	}
      }
    }
  }

  # we're always happy
  return true;
}

function lastfmrecords_stringbetween($s, $start, $end) {
  if ((strpos($s, $start) === false) || (strpos($s, $end) === false)) {
    return false;
  }
  $s = substr($s, strpos($s, $start) + strlen($start));
  return substr($s, 0, strpos($s, $end));
}

function lastfmrecords_add_pages() {
	if (function_exists('add_options_page')) {
    add_submenu_page('options-general.php', 'Last.Fm Records', 'Last.Fm Records', 8, basename(__FILE__), 'lastfmrecords_options_page');
  }
}

function lastfmrecords_cachefilename($options) {
  # this function returns
  # [lastfmname].[datepart].[period].cache

  # refresh every hour for recent tracks
  $_datepart = ("recenttracks" == $options['period']) ? date("ymdH") : date("ymd");

  return lastfmrecords_cachedir() . $options['username'] . "." . $_datepart . "." . $options['period'] . ".cache";
}

function lastfmrecords_cachedir() {
  # for reading from and writing to cache
  return dirname(__FILE__) . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR;
}

function lastfmrecords_urlbase($httphost, $phpself) {
  # for pointing to url's etc.

  $_path    = 'http://' . $httphost . $phpself;
  # ignore the wp-admin when request comes from an admin page
  if (false !== strpos($_path, 'wp-admin')) {
    $urlbase = substr($_path, 0, strpos($_path, 'wp-admin')) . 'wp-content/plugins/last.fm/';
  } else {
    $_file    = pathinfo($_path, PATHINFO_BASENAME);
    $urlbase  = substr($_path, 0, -1 * strlen($_file)) . 'wp-content/plugins/last.fm/';
  }
  return $_urlbase;
}

function lastfmrecords_options_page() {

  # Get our options and see if we're handling a form submission.
  $options = get_option('lastfm-records');
  if (!is_array($options) ) {
    $options = array('title'      => 'last.fm records',
                     'username'   => '',
                     'count'      => '6',
                     'imgwidth'   => '85',
                     'noimages'   => 'No images to display',
                     'period'     => 'weekly',
                     'htmlbefore' => '',
                     'htmlafter'  => '');
  }

	if (array_key_exists('lastfm-submit', $_POST)) {
    $options['title']     = strip_tags(stripslashes($_POST['lastfm-title']));
    $options['username']  = strip_tags(stripslashes($_POST['lastfm-username']));
    $options['imgwidth']  = intval($_POST['lastfm-imgwidth']);
    if ($options['imgwidth'] < 10) {
    	$options['imgwidth'] = 0;
    }

    $options['count']     = intval($_POST['lastfm-count']);
    if ($options['count'] < 1) {
      $options['count'] = 6;
    }

    $options['display']    = strip_tags(stripslashes($_POST['lastfm-display']));
    $options['noimages']   = strip_tags(stripslashes($_POST['lastfm-noimages']));
    $options['period']     = strip_tags(stripslashes($_POST['lastfm-period']));

    update_option('lastfm-records', $options);

    echo "<div id='message' class='updated fade'><p>The options for Last.Fm Records have been updated.</p></div>";
	}

  # html for options page
?>
<div class="wrap">
  <h2>Last.fm Records Options</h2>
  <form method=post action="<?php echo $_SERVER['PHP_SELF']; ?>?page=last.fm.php">
    <input type="hidden" name="update" value="true">
    <fieldset class="options">
      <table class="optiontable"> 
        <tr valign="top"> 
          <th scope="row">last.fm username</th> 
          <td>
            <input name="lastfm-username" type="text" id="lastfm-username" value="<?php echo $options['username']; ?>" size="40" /><br />
            If you don't have a username, go get a free account at <a href="http://www.last.fm/" target="_blank">last.fm</a>. This plugin<br />
            needs special account pages at last.fm to function. These pages<br />
            are empty when you start using last.fm (takes approx. 5 days).
          </td>
        </tr>
        <!--
        <tr valign="top">
          <th scope="row">title</th> 
          <td>
            <input name="lastfm-title" type="text" id="lastfm-title" value="<?php echo $options['title']; ?>" size="40" /><br />
            Title to show over images
          </td>
        </tr>
        -->
        <tr valign="top"> 
          <th scope="row">period</th>
          <td>
            <select style="width: 200px;" id="lastfm-period" name="lastfm-period">
              <option value="recenttracks"<?php if ('recenttracks' == $options['period']) { echo ' selected'; } ?>>recent tracks</option>
              <option value="weekly"<?php  if ('weekly'  == $options['period']) { echo ' selected'; } ?>>last week</option>
              <option value="3month"<?php  if ('3month'  == $options['period']) { echo ' selected'; } ?>>last 3 months</option>
              <option value="6month"<?php  if ('6month'  == $options['period']) { echo ' selected'; } ?>>last 6 months</option>
              <option value="12month"<?php if ('12month' == $options['period']) { echo ' selected'; } ?>>last 12 months</option>
              <option value="overall"<?php if ('overall' == $options['period']) { echo ' selected'; } ?>>give me everything</option>
            </select><br />
            Last.fm provides summarized data over several periods. You can select the period here.
          </td>
        </tr>
        <tr valign="top"> 
          <th scope="row">how to display the images</th>
          <td>
            <select style="width: 200px;" id="lastfm-display" name="lastfm-display">
              <option value="default.css"<?php if ('default.css' == $options['display']) { echo ' selected'; } ?>>All images equal in size</option>
            <!-- <option value="onebig.css"<?php if ('onebig.css' == $options['display']) { echo ' selected'; } ?>>First image twice as big</option> -->
            </select>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row">image count</th>
          <td>
            <input name="lastfm-count" type="text" id="lastfm-count" value="<?php echo $options['count']; ?>" size="10" /><br />
            The maximum of cd covers to display
          </td>
        </tr>
        <tr valign="top"> 
          <th scope="row">image width</th> 
          <td>
            <input name="lastfm-imgwidth" type="text" id="lastfm-imgwidth" value="<?php echo $options['imgwidth']; ?>" size="10" /><br />
            The width of the images
          </td>
        </tr>
        <tr valign="top"> 
          <th scope="row">error message</th> 
          <td><input name="lastfm-noimages" type="text" id="lastfm-noimages" value="<?php echo $options['noimages']; ?>" size="40" /><br />
            Text to display when there are no images to display
          </td>
        </tr>
      </table>

      <p class="submit">
        <input type="submit" name="lastfm-submit" value="Update Options &raquo;" />
      </p>
    </fieldset>
  </form>
</div>
<?
}

# add_action('init', 'lastfmrecords_init');
add_action('admin_menu', 'lastfmrecords_add_pages');

?>