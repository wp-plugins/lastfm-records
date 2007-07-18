<?

# Plugin Name: Last.Fm Records
# Version: 1.2
# Plugin URI: http://dirkie.nu/
# Description: The Last.Fm Records plugin lets you show what you are listening to, with a little help from our friends at last.fm.
# Author: Dog Of Dirk
# Author URI: http://dirkie.nu/

$_lfm_version = "1.2";

if (!function_exists('get_option')) {
  # why do I always get me in this kind of trouble working outside of wordpress?
  # include_once(dirname(__FILE__) . '/../../../wp-config.php');
  # include_once(dirname(__FILE__) . '/../../../wp-includes/wp-db.php');
  require_once(dirname(__FILE__) . '/../../../wp-blog-header.php');
}

##################################################################
# direct calls to this script with a $_GET['t'] and a $_GET['a'] #
# result in a header redirect to the cd cover (if found).        #
##################################################################

if ((array_key_exists('t', $_GET)) && (array_key_exists('a', $_GET))) {

  # fallback image
  $_img = 'http://panther1.last.fm/depth/catalogue/noimage/cover_large.gif';

  $_title  = urlencode($_GET['t']);
  $_artist = urlencode($_GET['a']);

  $lfm = new lastfmrecords();
  $_cd = $lfm->getimageurl($_title, $_artist);
  if (is_array($_cd)) {
  	$_img = $_cd['image'];
  } else {
    # if the artist is really two artists, let's see if the album is under one of their names 
    # e.g. 'The River In Reverse' by 'Elvis Costello & Allen Toussaint' is under Elvis Costello -- shame!

    # this code is commented out, as it gives too many wrong images
    # e.g. 'Way Down In The Hole" by 'Tom Waits & Kronos Quartet' is then on 'Beautiful Maladies: The Island Years'
    # this is wrong, as that is the original version and not the beautiful new version with the Kronos Quartet
    # if you don't mind, restore the next 9 lines below.
    # this bit of code is also in the function findcdtitlefortrack, so you'll have to restore it there too.

    # if (false !== strpos($_artist, '%2B%2526%2B')) {
    #   $_partners = explode('%2B%2526%2B', $_artist);
    #   foreach($_partners as $_partner) {
    #     $_cd = $lfm->getimageurl($_title, $_partner);
    #     if (is_array($_cd)) {
  	#       $_img = $_cd['image'];
    #       break;
    #     }
    #   }
    # }
	}
  header("Location: " . $_img);
  exit;
}

function lastfmrecords_display($period = false, $count = false) {
  $lfm = new lastfmrecords();
  $lfm->display($period, $count);
}

function lastfmrecords_add_pages() {
 if (function_exists('add_options_page')) {
    # Lorelle told me to
    add_submenu_page('plugins.php', 'Last.Fm Records', 'Last.Fm Records', 8, basename(__FILE__), 'lastfmrecords_options_page');
  }
}

function lastfmrecords_options_page() {
  $lfm = new lastfmrecords();
  $lfm->options_page();
}

function lastfmrecords_stylesheet() {
  $lfm = new lastfmrecords();
  $lfm->stylesheet();
}

function lastfmrecords_siteurl() {
  $lfm = new lastfmrecords();
  return $lfm->siteurl();
}

class lastfmrecords {
  # yes, it's a class. Very simple though, as I'm supporting PHP4.
  # for now it functions as a namespace.  

  function init() {
	  # nuttin' in it
  }
  
  function path() {
  	return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  }
  
  function siteurl() {
    $_file  = pathinfo($this->path(), PATHINFO_BASENAME);
    $_siteurl = substr($this->path(), 0, -1 * strlen($_file));
    # for when we're in the backend
    $_siteurl = str_replace('wp-admin/', '', $_siteurl);

    return $_siteurl;
  }

  function options() {
    return get_option('lastfm-records');
  }

  ###################################################################
  # this is the function that echoes the html for the cd covers     #
  # it adds calls to this script for cds it can't find in the cache #
  # (see top of this script)                                        #
  ###################################################################

  function display($period = false, $count = false) {
    $options = $this->options();
    if ($period) {
      $options['period'] = $period;
    }
    if ($count) {
      $options['count'] = $count;
    }

    # get list for this user
    $_lastfmlist = $this->getlist($options);
    if (!$_lastfmlist) {
  	  echo $options['noimages'];
      return;
    }

    echo "\n  <ol id=\"lastfmrecords\">\n";
    $_count = 0;
    $_albums_done = array();
    foreach($_lastfmlist as $_title => $_artist) {

      if ($_count == intval($options['count'])) {
        break;
      }

      if ((($_count + 1) == intval($options['count'])) && ('onebig.css' == $options['display'])) {
        break;
      }

      # $_title = urlencode($_title);

      # prevent duplicate cd covers
      if (array_key_exists($_title, $_albums_done)) {
    	  continue;
      }

      echo "    <!-- Looking for $_title by $_artist: ";
      # is the image in the cache?
      $_cacheresult = $this->imageincache($_title, $_artist);
      if (is_array($_cacheresult)) {
    	  echo "image found -->\n";
    	  $_imgurl = $_cacheresult['image'];
      } else if ('noimage' == $_cacheresult) {
    	  echo "cache says there's no image (you can try to add an image on the options page) -->\n";
    	  continue;
      } else {
    	  echo "cache doesn't know, we'll try our luck with a call from the img src -->\n";
        $_imgurl = $this->siteurl() . 'wp-content/plugins/last.fm/last.fm.php?t=' . $_title . '&a=' . $_artist;
      }

      $_albums_done[$_title] = $_artist;

      $_safe_title  = $this->cleanuptext($_title);
      $_safe_artist = $this->cleanuptext($_artist);
?>
    <li>
<?
      if ('imgwtext.css' == $options['display']) {
      	# I prefer something other than a table, but that's hard to keep the text together next to the image...
?>
      <table><tr valign="top"><td>
<?
      }
?>
        <a href='http://www.last.fm/music/<?= $_artist ?>/<?= $_title ?>/'>
          <img class='cdcover cover<?= $_count + 1 ?>' src='<?= $_imgurl ?>' title='<?= $_safe_artist ?>: <?= $_safe_title ?>' alt='<?= $_safe_title ?>' />
        </a>
<?
      if ('imgwtext.css' == $options['display']) {
?>
      </td><td>
        <a href='http://www.last.fm/music/<?= $_artist ?>/<?= $_title ?>/'><?= $_safe_title ?></a>
        <br />
        <?= $_safe_artist ?>
      </td></tr></table>
<?
    }
?>
    </li>
<?
      $_count++;
    }

    echo "  </ol>\n";

    global $_lfm_version;

    echo "  <!--\n";
    echo "    Last.Fm Records plugin (version " . $_lfm_version . ")\n";
    echo "    last.fm username: " . $options['username'] . "\n";
    echo "    period setting: " . $options['period'] . "\n";
    echo "    display setting: " . $options['display'] . "\n";
    echo "  -->\n";

  }

  ################################################################
  # get feed from last.fm with cds that the user has listened to #
  # and parse it into an array that looks like                   #
  #                                                              #
  # $array[title] = artist                                       #
  ################################################################

  function getlist($options) {

    # where would the cached list be?
    $_cachefile = $this->cachefilename($options);

    if (file_exists($_cachefile)) {
      # cachefile exists
      return unserialize(file_get_contents($_cachefile));
    } else {
      # not cached, get list from last.fm and parse into an array
      switch($options['period']) {
        case 'recenttracks':
          $_last_fm_url = 'http://ws.audioscrobbler.com/1.0/user/' . $options['username'] . '/recenttracks.xml';
          $_result      = $this->loadurl($_last_fm_url);
          if (!$_result) {
            return false;
          }
          $_items       = explode('<track streamable', $_result);
          $_parsed = array();
          array_shift($_items);
          foreach($_items as $_item) {
            $_line = $this->stringbetween($_item, '/music/', '</url>');
            if ($_line) {
              # ok, we found a song
              $_line = explode('/_/', trim($_line));

              # let's see if we can find the cd title for this song
              $_title = $this->findcdtitlefortrack($_line[1], $_line[0]);
              if (!$_title) {
                continue;
              } else {
      	        $_title = urlencode($_title);
              }
              
              $_parsed[$_title] = $_line[0];
            }
          }
          $this->writecachefile($_parsed, $_cachefile);
          return $_parsed;
          break;
        case 'weekly':
          $_last_fm_url = 'http://ws.audioscrobbler.com/1.0/user/' . $options['username'] . '/weeklyalbumchart.xml';
          $_result      = $this->loadurl($_last_fm_url);
          if ($_result) {
            $_items       = explode('<album>', $_result);
            $_parsed = array();
            array_shift($_items);
            foreach($_items as $_item) {
              $_line = trim($this->stringbetween($_item, 'http://www.last.fm/music/', '</url>'));
              if ($_line) {
                $_line = explode('/', $_line);
                $_parsed[$_line[1]] = $_line[0];
              }
            }
            $this->writecachefile($_parsed, $_cachefile);
            return $_parsed;
          } else {
            return false;
          }
          break;
        default:
          $_last_fm_url = 'http://www.last.fm/user/' . $options['username'] . '/charts/?subtype=album&charttype=' . $options['period'];
          $_result      = $this->loadurl($_last_fm_url);
          if ($_result) {
            $_parsed = $this->parsehtml($_result, $options);
            $this->writecachefile($_parsed, $_cachefile);
            return $_parsed;
          } else {
    	      return false;
    	    }
      }
    }
  }

  ############################
  # used by $this->getlist() #
  ############################

  function parsehtml($_html, $options) {
    # parse the html from last.fm
    # this is what we call screenscraping
    $_albums = explode('<a href="/music/', $_html);
    array_shift($_albums);

    $_ta = array();
    foreach ($_albums as $_k => $_v) {

      $_v         = substr($_v, 0, strpos($_v, '"'));
      $_parts     = explode('/', $_v);

      $_artist    = $_parts[0];
      # for 'recenttracks', this is the songtitle
      # we'll deal with this later
      $_title     = ('recenttracks' == $options['period']) ? $_parts[2] : $_parts[1];

      if (('+charts' != $_artist) && ('' != $_artist) && ('' != $_title)) {
        $_ta[$_title] = $_artist;
      }
    }

    return $_ta;
  }

  #########################################################################
  # check if we already know where the image of the cd cover can be found #
  #########################################################################

  function imageincache($_title, $_artist) {
    # please note that this function can return:
    # 1. an array with data for the url of the image
    # 2. 'noimage' (without the quotes, image is in cache, but no image was found)
    # 3. false: image is not in cache

    # is the image data already in the cache?
    $_cachefile = $this->cachefile_albumdata($_title, $_artist);
    if (file_exists($_cachefile)) {
  	  return unserialize(file_get_contents($_cachefile));
    } else {
  	  return false;
    }
  }

  ############################################
  # find the image for a cd title and artist #
  ############################################

  function getimageurl($_title, $_artist) {
    $_r = "http://ws.audioscrobbler.com/1.0/album/" . $_artist . "/" . $_title . "/info.xml";

    # obviously: TODO
    $_r = str_replace('%5C', '', $_r);

    $_lastfm_xml = $this->loadurl($_r);

    if ($_lastfm_xml) {
      # large image available?
      $_imgurl = $this->stringbetween($_lastfm_xml, "<large>", "</large>");
      if ((!$_imgurl) || (strpos($_imgurl, 'noimage') > 0)) {
        # large image not available, try medium one
        $_imgurl = $this->stringbetween($_lastfm_xml, "<medium>", "</medium>");
      }
      if (($_imgurl) && (false === strpos($_imgurl, 'noimage'))) {
        $_imgarray = array(
                       'image'      => $_imgurl,
                       'cdtitle'    => $_title,
                       'artist'     => $_artist
                     );

        $_options = $this->options();
        if ('1' == $_options['localthumbs']) {
          # create local thumbnail in cache
          $_localthumb = $this->createlocalthumb($_imgarray);
          if ($_localthumb) {
            $_imgarray['image'] = $_localthumb;
          }
        }

        $this->writecachefile($_imgarray, $this->cachefile_albumdata($_title, $_artist));
        return $_imgarray;
      } else {
        $this->writecachefile('noimage', $this->cachefile_albumdata($_title, $_artist));
        return false;
      }
    }
    return false;
  }

  #######################################################
  # load external image, create thumb and save to cache #
  # if one of these steps fails, return false           #
  #######################################################

  function createlocalthumb($_imagedata) {

    $_ext = pathinfo($_imagedata['image']);
    $_ext = strtolower($_ext['extension']);

    $_newname = base64_encode($_imagedata['artist'] . '#_#' . $_imagedata['cdtitle']) . '.' . $_ext;
    $_newurl  = $this->siteurl() . 'cache/' . base64_encode($_imagedata['artist'] . '#_#' . $_imagedata['cdtitle']) . '.' . $_ext;
    # does the thumb already exist?
    if (file_exists($_newname)) {
    	return $_newurl;
    }
    
    # do we have a bit of image support (PHP >= 4.3.0)?
    if (!function_exists('gd_info')) {
      return false;
    }

    # let's check if this image type is supported
    $_gdinfo = gd_info();

    # jpeg
    if (('jpg' == $_ext) || ('jpeg' == $_ext)) {
      if (!$_gdinfo['JPG Support']) {
        return false;
      }
    }

    # gif
    if ('gif' == $_ext) {
      if ((!$_gdinfo['GIF Read Support']) || (!$_gdinfo['GIF Create Support'])) {
        return false;
      }
    }

    # png
    if ('png' == $_ext) {
      if (!$_gdinfo['PNG Support']) {
        return false;
      }
    }
    
    # load image from external site
    $_i = $this->loadurl($_imagedata['image']);
    if (!$_i) {
      return false;
    }

    # TODO
    if (!function_exists('imagecreatefromstring')) {
      return false;
    }
    
    # try to create an image 'object'
    $_im = imagecreatefromstring($_i);
    if (!$_im) {
    	return false;
    }

    # try to resize it
    $options = $this->options();
    
    # if you didn't specify an image width, this won't work
    if (0 == $options['imgwidth']) {
      return false;
    }

    $_resized = imagecreatetruecolor($options['imgwidth'], $options['imgwidth']);
    if (!imagecopyresampled($_resized, $_im, 0, 0, 0, 0, $options['imgwidth'], $options['imgwidth'], imagesx($_im), imagesy($_im))) {
      # resizing failed
      return false;
    }

    # destroy original image
    imagedestroy($_im);

    # write new thumb to cache folder
    switch($_ext) {
    	case 'jpg':
    	case 'jpeg':
    	  if (imagejpeg($_resized,  $this->cachedir() . $_newname, 100)) {
    	    return $_newurl;
    	  }
    	  break;
    	case 'gif':
    	  if (imagegif($_resized, $this->cachedir() . $_newname)) {
    	    return $_newurl;
    	  }
    	  break;
    	case 'png':
    	  if (imagepng($_resized, $this->cachedir() . $_newname, 0)) {
    	    return $_newurl;
    	  }
    	  break;
    }
    
    return false;
  }

  ####################################################
  # send tracktitle and artist to the Amazon API and #
  # hopefully get a cd title back                    #
  ####################################################

  function findcdtitlefortrack($_title, $_artist) {
    # 1. musicbrainz.org
    # 2. amazon

    $_url = "http://musicbrainz.org/ws/1/track/?type=xml&title=" . $_title . "&artist=" . $_artist;

    $_musicbrainz = $this->loadurl($_url);

    if (!$_musicbrainz) {
      # as the url could be unavailable, we try amazon
      echo "<!--  niet gevonden by musicbrainz, we proberen amazon -->\n";
      return $this->findcdtitlefortrackatamazon($_title, $_artist);
    }

    $_items = explode('<track id', $_musicbrainz);
    array_shift($_items);
    foreach($_items as $_item) {
      $_artistfound = $this->stringbetween($_item, '<name>', '</name>');
      echo "<!-- de gevonden artist is " . $_artistfound . " -->\n";
      # does this artist's name 'sound' like the one we're looking for?
      # this way, Sansévérino and Sanseverino are matched
      $_cleanartist = urldecode($_artist);
      if (soundex($_artistfound) == soundex($_cleanartist)) {
    	  $_songtitle = $this->stringbetween($_item, '<release-list>', '</release-list>');
    	  if ($_songtitle) {
    	    $_songtitle = $this->stringbetween($_songtitle, '<title>', '</title>');
    	    echo "<!-- ja gevonden bij musicbrainz, cd heet " . $_songtitle . " -->\n";
    	    return $_songtitle;
    	  }
      }
    }

    # we have received an answer from musicbrainz, but the song we are looking for isn't in the answer
    # if the artist is really two artists, we could check if the album is under one of their names
    # e.g. 'The River In Reverse' by 'Elvis Costello & Allen Toussaint' is under Elvis Costello -- shame!

    # this code is commented out, as it gives too many wrong images
    # e.g. 'Way Down In The Hole" by 'Tom Waits & Kronos Quartet' is then on 'Beautiful Maladies: The Island Years'
    # this is wrong, as that is the original version and not the beautiful new version with the Kronos Quartet
    # if you don't mind, restore the next 9 lines below.

    # if (false !== strpos($_artist, '%2B%2526%2B')) {
    #   $_partners = explode('%2B%2526%2B', $_artist);
    #   foreach($_partners as $_partner) {
    #     $_cdtitle = $this->findcdtitlefortrack($_title, $_partner);
    #     if ($_cdtitle) {
  	#       return $_cdtitle;
    #     }
    #   }
    # }

    # peter%2B%2526%2Bkate say: just give up
    return false;
  }

  function findcdtitlefortrackatamazon($_title, $_artist) {
    $_apikey = '17CBJCAMVX5V38CR0F02';

    $_r = "http://webservices.amazon.com/onca/xml?Service=AWSECommerceService&SearchIndex=MusicTracks&" . 
          "AWSAccessKeyId=" . $_apikey . "&Operation=ItemSearch&ResponseGroup=Small&" . 
          "Keywords=" . $_title;

    $_amazon_xml = $this->loadurl($_r);
  
    if (!$_amazon_xml) {
		  return false;
	  }

    $_artist = urldecode($_artist);

    # terrible way of parsing XML
    $_items = explode('<Item>', $_amazon_xml);
	  array_shift($_items);
    foreach ($_items as $_k => $_v) {
      $_artistfound = $this->stringbetween($_v, '<Artist>', '</Artist>');
      echo "<!-- de gevonden artist is " . $_artistfound . " -->\n";
      # does this artist's name 'sound' like the one we're looking for?
      # this way, Sansévérino and Sanseverino are matched
      if (soundex($_artistfound) == soundex($_artist)) {
    	  $_title = $this->stringbetween($_v, '<Title>', '</Title>');
   	    echo "<!-- ja gevonden bij amazon, cd heet " . $_songtitle . " -->\n";
    	  return $_title;
      }
    }
    return false;
  }

  ############################################
  # serialize $_array and write it to $_file #
  ############################################

  function writecachefile($_array, $_file, $_append = false) {
    $_ser = serialize($_array);
    # write to cache
    if ($_append) {
      $_f = fopen($_file, 'w');
    } else {
      $_f = fopen($_file, 'w+');
    }
    if ($_f) {
      fwrite($_f, $_ser, strlen($_ser));
      fclose($_f);
    }
  }

  function cleanupcache($_dir, $_lastfmusername) {
    # in theory, in multi user wordpress environments,
    # diffent blogs can display cd covers from the same
    # last.fm user
    
    # so we keep all cache files, except the ones for the current last.fm user that are not for today
    
    # TODO eventually: clean up really old album data?
    
    # this means when a user has cache files for "recenttracks", the cache will keep
    # 24 files before deleting them.
    if ($handle = @opendir($_dir)) {
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

  function loadurl($_url) {
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

  function stringbetween($s, $start, $end) {
    if ((strpos($s, $start) === false) || (strpos($s, $end) === false)) {
      return false;
    }
    $s = substr($s, strpos($s, $start) + strlen($start));
    return substr($s, 0, strpos($s, $end));
  }

  function cachefilename($options) {

    # TODO: determine if this is the best place to clean up the cache
    $this->cleanupcache($this->cachedir(), $options['username']);

    # this function returns
    # [lastfmname].[datepart].[period].cache

    # refresh every hour for recent tracks
    $_datepart = ("recenttracks" == $options['period']) ? date("ymdH") : date("ymd");

    return $this->cachedir() . $options['username'] . "." . $_datepart . "." . $options['period'] . ".cache";
  }

  function cachefile_albumdata($_title, $_artist) {
    return $this->cachedir() . base64_encode($_artist . '#_#' . $_title) . '.albumdata';
  }

  function cachedir() {
    # for reading from and writing to cache
    return dirname(__FILE__) . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR;
  }

  function cleanuptext($_string) {
    # TODO we have to simplify this a bit ;-)
    return urldecode(str_replace(array("'", "%26"), array("`", "&"), urldecode($_string)));
  }

  function options_page() {

    ######################################################################
    # direct calls to this script with a $_POST['ut'] and a $_POST['ua'] #
    # are an image upload for a missing cover                            #
    ######################################################################

    $_fadingstatus = false;
    if ((array_key_exists('ut', $_POST)) && (array_key_exists('ua', $_POST))) {
      if (!isset($_FILES['uf']['tmp_name'])) {
        $_fadingstatus = 1;
      } else {
        # let's restrict the images to jpg, gif and png
        $_ext = pathinfo($_FILES['uf']['name']);
        $_ext = $_ext['extension'];
        if (!in_array($_ext, array('jpg', 'gif', 'png'))) {
          $_fadingstatus = 2;
        } else {
          $_newname = base64_encode($_POST['ua'] . '#_#' . $_POST['ut']) . '.' . $_ext;
          if (!move_uploaded_file($_FILES['uf']['tmp_name'], $this->cachedir() . $_newname)) {
            $_fadingstatus = 1;
          } else {
      	    $_array = array('image'      => $this->siteurl() . 'wp-content/plugins/last.fm/cache/' . $_newname,
                            'cdtitle'    => $_POST['ut'],
                            'artist'     => $_POST['ua']
                           );
            $this->writecachefile($_array, $this->cachefile_albumdata($_POST['ut'], $_POST['ua']));
      	    $_fadingstatus = 4;
          }
        }
      }
    }

    # Get our options and see if we're handling a form submission.
    $options = $this->options();
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
      # $options['title']       = strip_tags(stripslashes($_POST['lastfm-title']));
      $options['username']    = strip_tags(stripslashes($_POST['lastfm-username']));
      $options['imgwidth']    = intval($_POST['lastfm-imgwidth']);
      if ($options['imgwidth'] < 10) {
    	  $options['imgwidth'] = 0;
      }

      $options['count']       = intval($_POST['lastfm-count']);
      if ($options['count'] < 1) {
        $options['count'] = 6;
      }

      $options['display']     = strip_tags(stripslashes($_POST['lastfm-display']));
      $options['noimages']    = strip_tags(stripslashes($_POST['lastfm-noimages']));
      $options['period']      = strip_tags(stripslashes($_POST['lastfm-period']));
      $options['localthumbs'] = strip_tags(stripslashes($_POST['lastfm-localthumbs']));

      update_option('lastfm-records', $options);

      $_fadingstatus = 3;
	  }

    # $_fadingstatus set?
    if ($_fadingstatus) {
      switch($_fadingstatus) {
        case 1:
          echo "<div id='message' class='updated fade'><p>File upload failed.</p></div>";
          break;
        case 2:
          echo "<div id='message' class='updated fade'><p>This file type is not supported.</p></div>";
          break;
        case 3:
          echo "<div id='message' class='updated fade'><p>The options for Last.Fm Records have been updated.</p></div>";
          break;
        case 4:
          echo "<div id='message' class='updated fade'><p>The cd cover has been uploaded!</p></div>";
          break;
      }
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
              <option value="onebig.css"<?php if ('onebig.css' == $options['display']) { echo ' selected'; } ?>>First image twice as big</option>
              <option value="imgwtext.css"<?php if ('imgwtext.css' == $options['display']) { echo ' selected'; } ?>>Image with text</option>
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
        <tr valign="top"> 
          <th scope="row">save thumbnails to cache</th> 
          <td>
            <select style="width: 200px;" id="lastfm-localthumbs" name="lastfm-localthumbs">
              <option value="0"<?php if ('0' == $options['localthumbs']) { echo ' selected'; } ?>>No</option>
              <option value="1"<?php if ('1' == $options['localthumbs']) { echo ' selected'; } ?>>Yes</option>
            </select><br />
            When set to 'yes', thumbnails will be created by the server and saved<br />
            to the cache folder. The pictures will be less grainy, but it takes<br />
            a little longer when the plugin finds a cd for the first time.<br /><br />
            <b>NB!</b>: Obviously, setting it to yes takes up more disk space.
          </td>
        </tr>
      </table>
      <p class="submit">
        <input type="submit" name="lastfm-submit" value="Update Options &raquo;" />
      </p>
    </fieldset>
  </form>
<?
    # missing cd covers?
    $_missing = $this->getmissingcovers();
    if ($_missing) {
?>

  <script type="text/javascript">
  //<![CDATA[
    function showupload(i) {
      var el = document.getElementById('upload' + i);
      if (el) {
        if ('none' == el.style.display) {
          el.style.display = '';
        } else {
          el.style.display = 'none';
        }
      }
      return false;
    }
  //]]>
  </script>

  <h2>Upload Missing Covers</h2>
  <table class="optiontable"> 
<? echo $_missing; ?>
  </table>
<?
    }
?>
</div>
<?
  }

  function getmissingcovers() {
    $result = "";
    $count = 0;
    $_dir = $this->cachedir();
    if ($handle = @opendir($_dir)) {
      while (false !== ($_file = readdir($handle))) {
        # look for albumdata that have noimage specified
        if ("albumdata" == substr($_file, -9)) {
      	  # what's the artist and title?
      	  # get the correct part of the filename
          $_ta = basename($_file, ".albumdata");
      	  # decode it
      	  $_ta = base64_decode($_ta);
      	  # split it to find artist and title
      	  $_ta = explode('#_#', $_ta);
          $_i = unserialize(file_get_contents($_dir . $_file));
          if ('noimage' == $_i) {
            $count++;

            $result .= '    <tr valign="top">' . "\n";
            $result .= '      <th scope="row">' . urldecode($_ta[0]) . "</th>\n";
            $result .= '      <td>' . "\n";
            $result .= '        <a target="_blank" href="http://images.google.com/images?hl=en&q=+%22' . $_ta[0] . '%22+%22' . $_ta[1] . '%22+site:images.amazon.com">[find image]</a>&nbsp;&nbsp;&nbsp;<a href="#" onclick="return showupload(' . $count . ');">' . urldecode($_ta[1]) . "</a><br /><br />\n";
            $result .= '        <span id="upload' . $count . '" style="display: none;">' . "\n";
            $result .= '          <form enctype="multipart/form-data" method="post" action="' . $_SERVER['PHP_SELF'] . '?page=last.fm.php">' . "\n";
            $result .= '            <input type="hidden" name="ua" value="' . $_ta[0] . '" />' . "\n";
            $result .= '            <input type="hidden" name="ut" value="' . $_ta[1] . '" />' . "\n";
            $result .= '            <input type="file" name="uf" value="" />' . "\n";
            $result .= '            <input type="submit" name="submit" value="upload" />' . "\n";
            $result .= '          </form>' . "\n";
            $result .= '        </span>' . "\n";
            $result .= '      </td>' . "\n";
            $result .= "    </tr>\n";
          }
        }
      }
    }

    if ($result) {
      return $result;
    } else {
  	  return false;
    }
  }

  function stylesheet() {
    $options = $this->options();
?>
  <!-- added by plugin Last.Fm Records -->
  <style type="text/css">
    #lastfmrecords    { padding: 0px; padding-bottom: 10px; }
    #lastfmrecords li { list-style-type: none; margin: 0px; padding: 0px; display: inline; }
<?
  # people using their own class in css?
  if (0 != intval($options['imgwidth'])) {
    switch($options['display']) {
      case 'imgwtext.css':
?>
    #lastfmrecords a  { font-weight: bold; }
    img.cdcover       { height: <?= $options['imgwidth'] ?>px; width: <?= $options['imgwidth'] ?>px; margin: 0px 5px 5px 0px; border: 0px; }
<?
        break;
      case 'onebig.css'  :
?>
    img.cdcover       { height: <?= $options['imgwidth'] ?>px; width: <?= $options['imgwidth'] ?>px; margin: 0px 5px 5px 0px; border: 0px; }
    img.cover1        { height: <?= (2 * intval($options['imgwidth'])) + 7 ?>px; width: <?= (2 * intval($options['imgwidth'])) + 7 ?>px; margin: 0px; }
<?
        break;
      case 'default.css' :
      default:
?>
    img.cdcover       { height: <?= $options['imgwidth'] ?>px; width: <?= $options['imgwidth'] ?>px; margin: 0px 5px 5px 0px; border: 0px; }
<?
      }
    }
?>
  </style>
<?
  }
}

# this function gets called when widgets are supported
function widget_lastfmrecords_init() {

  # does this wordpress environment support widgets?
  if (!function_exists('register_sidebar_widget'))
    return;

  # output for sidebar
  function widget_lastfmrecords($args) {
    extract($args);

    $options = get_option('lastfm-records');

    echo "\n\n" . $before_widget . $before_title . $options['title'] . $after_title . "\n";
    lastfmrecords_display();
		echo $after_widget . "\n\n";
  }

  function widget_lastfmrecords_control() {
    $options = get_option('lastfm-records');

    if (($_POST['lastfmrecords-submit']) && ("" != $_POST['lastfmrecords-title'])) {
      $options['title'] = strip_tags(stripslashes($_POST['lastfmrecords-title']));
      update_option('lastfm-records', $options);
    }

    $title = htmlspecialchars($options['title'], ENT_QUOTES);
?>
    <p style="text-align:right;">
      <label for="lastfmrecords-title">title: 
        <input style="width: 200px;" id="lastfmrecords-title" name="lastfmrecords-title" type="text" value="<?= $title ?>" />
      </label>
    </p>
    <p>Other options are on the <a href="<?= lastfmrecords_siteurl(); ?>wp-admin/plugins.php?page=last.fm.php">options page</a> for this plugin.</p>
    <input type="hidden" id="lastfmrecords-submit" name="lastfmrecords-submit" value="1" />    
<?
  }

  # if you want to use it as a widget, it's available
  register_sidebar_widget('Last.Fm Records', 'widget_lastfmrecords');

  // and we need a small form to add a title in the sidebar
  register_widget_control('Last.Fm Records', 'widget_lastfmrecords_control', 375, 95);
}

# one place were we can change the settings
add_action('admin_menu', 'lastfmrecords_add_pages');

# add stylesheet(s) to head
add_action('wp_head', 'lastfmrecords_stylesheet');

# widget variant
add_action('plugins_loaded', 'widget_lastfmrecords_init');

?>