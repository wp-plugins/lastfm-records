<?php
/*
Plugin Name: Last.Fm Records
Description: The Last.Fm Records plugin lets you show what you are listening to, with a little help from our friends at last.fm.
Author: Jeroen Smeets
Version: 1.5.4
Plugin URI: http://jeroensmeets.net/lastfmrecords/
Author URI: http://jeroensmeets.net/
License:  GPL
*/

// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

// has this plugin been installed in its own directory?
define('LFR_URL', (basename(dirname(__FILE__)) != 'plugins') ? WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)) : '');

	//////////////////////////////////////////////////////////////////////
	// WRITE JAVASCRIPT TO THE HEADER WHEN ENOUGH CONFIG HAS BEEN SAVED //
	//////////////////////////////////////////////////////////////////////

function lfr_add_javascript() {
	$options = get_option('lastfm-records');
	if ('' == trim($options['username'])) {
?>

  <!-- ### Attention! last.fm username is not set in the settings for the plugin Last.Fm Records -->

<?php
	} else {
?>

  <!-- Last.Fm Records start -->
<?php
		// display stylesheet? version 1.5.3 had 0 or 1, so we build from there
		switch($options['stylesheet']) {
			case 1:
?>
  <style type="text/css">
    #lastfmrecords        { padding: 0px; padding-bottom: 10px; }

    /* thx to http://cssglobe.com/lab/overflow_thumbs/ */
    #lastfmrecords ol,
      #lastfmrecords li        { margin: 0; padding: 0; list-style: none; }
    #lastfmrecords li          { float: left; margin: 0px 5px 5px 0px; }
    #lastfmrecords a           { display: block; float: left; width: <?php echo $options['imgwidth']; ?>px; height: <?php echo $options['imgwidth']; ?>px; line-height: <?php echo $options['imgwidth']; ?>px; overflow: hidden; position: relative; z-index: 1; }
    #lastfmrecords a img       { float: left; position: absolute; margin: auto; min-height: <?php echo $options['imgwidth']; ?>px; }
    /* mouse over */
    #lastfmrecords a:hover     { overflow:visible; z-index:1000; border:none; }
    #lastfmrecords a:hover img { border: 1px  solid #999; background: #fff; padding: 3px; margin-top: -20px; margin-left: -20px; min-height: <?php echo $options['imgwidth'] + 20; ?>px;  }
  </style>
<?php
			break;
			case 2:
?>
  <style type="text/css">
    #lastfmrecords             { padding: 0px; padding-bottom: 10px; }
    #lastfmrecords ol,
      #lastfmrecords li        { margin: 0; padding: 0; list-style: none; }
    #lastfmrecords li          { display: inline; margin: 0px 5px 5px 0px; }
    #lastfmrecords a img       { width: <?php echo $options['imgwidth']; ?>px; height: <?php echo $options['imgwidth']; ?>px; }
  </style>
<?php
			break;
			case 3:
			break;
		}

?>
  <script type='text/javascript' src='<?php echo LFR_URL; ?>/last.fm.records.js'></script>
  <script type='text/javascript'>
    var _config = { username: '<?php echo $options['username']; ?>',
                    placeholder: 'lastfmrecords',
                    defaultthumb: '<?php echo $options['defaultthumb']; ?>',
                    count: <?php echo $options['count']; ?>,
                    period: '<?php echo $options['period']; ?>',
                    refresh: <?php echo $options['refresh']; ?>,
                    offset: <?php echo $options['offset'] . "\n"; ?>
                  };
    jQuery(document).ready( function() {
<?php
		if (true == $options['debug']) { echo "      lastFmRecords.debug();\n"; }
?>
      lastFmRecords.init(_config);
    });
  </script>
  <!-- Last.Fm Records end -->

<?php
	}
}

# add stylesheet and scripts to head
add_action('wp_head', 'lfr_add_javascript');

	////////////////////////////
	// NEXT UP: CONFIGURATION //
	////////////////////////////

function lfr_add_pages() {
	add_submenu_page('options-general.php', 'Last.Fm Records', 'Last.Fm Records', 8, basename(__FILE__), 'lfr_options');
}

function lfr_options() {
?>
	<div class="wrap">
		<h2>Last.Fm Records Options</h2>
<?php
	# Get our options and see if we're handling a form submission.
	$options = get_option('lastfm-records');
	if (!is_array($options) ) {
		$options = array('title'        => 'last.fm records',
										 'username'     => '',
										 'defaultthumb' => 'http://cdn.last.fm/depth/catalogue/noimage/cover_85px.gif',
										 'stylesheet'   => '0',
										 'imgwidth'     => 85,
										 'count'        => 6,
										 'refresh'      => 1,
										 'offset'       => 1,
										 'debug'        => '0'
										);
	}

	if ('' == trim($options['defaultthumb'])) {
		$options['defaultthumb'] = 'http://cdn.last.fm/depth/catalogue/noimage/cover_85px.gif';
	}

	// title, username, defaultthumb, stylesheet, count, refresh, offset, debug

	if (array_key_exists('lastfm-submit', $_POST)) {
		// title is handled by widget settings
		# $options['title']        = strip_tags(stripslashes($_POST['lastfm-title']));
		$options['username']       = strip_tags(stripslashes($_POST['lastfm-username']));
		$options['period']         = strip_tags(stripslashes($_POST['lastfm-period']));
		$options['defaultthumb']   = strip_tags(stripslashes($_POST['lastfm-defaultthumb']));
		$options['stylesheet']     = strip_tags(stripslashes($_POST['lastfm-stylesheet']));
		$options['imgwidth']       = intval($_POST['lastfm-imgwidth']);
		if ($options['imgwidth'] < 10) {
		  $options['imgwidth'] = 0;
		}
		$options['count']          = intval($_POST['lastfm-count']);
		if ($options['count'] < 1) {
		  $options['count'] = 6;
		}
		$options['refresh']        = intval($_POST['lastfm-refresh']);
		$options['offset']         = strip_tags(stripslashes($_POST['lastfm-offset']));
		if (('+' != substr($options['offset'], 0, 1)) && ('-' != substr($options['offset'], 0, 1))) {
			$options['offset'] = '+0';
		}
		$options['debug']          = strip_tags(stripslashes($_POST['lastfm-debug']));
		
		update_option('lastfm-records', $options);
		echo "		<div id='message' class='updated fade'><p>Your settings have been updated.</p></div>";
	}
?>

		<form method="post">
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="lastfm-username">Last.fm username:</label></th>
					<td><input type="text" name="lastfm-username" value="<?php echo $options['username']; ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="lastfm-stylesheet">Period:</label></th>
					<td>
						<select id="lastfm-period" name="lastfm-period">
							<option value="recenttracks"<?php if ('recenttracks' == $options['period']) { echo ' selected'; } ?>>Recent tracks</option>
							<option value="7day"<?php if ('7day' == $options['period']) { echo ' selected'; } ?>>Last 7 days</option>
							<option value="3month"<?php if ('3month' == $options['period']) { echo ' selected'; } ?>>Last 3 months</option>
							<option value="6month"<?php if ('6month' == $options['period']) { echo ' selected'; } ?>>Last 6 months</option>
							<option value="12month"<?php if ('12month' == $options['period']) { echo ' selected'; } ?>>Last year</option>
							<option value="overall"<?php if ('overall' == $options['period']) { echo ' selected'; } ?>>Everything but the girl</option>
							<option value="topalbums"<?php if ('topalbums' == $options['period']) { echo ' selected'; } ?>>Top albums</option>
							<option value="lovedtracks"<?php if ('lovedtracks' == $options['period']) { echo ' selected'; } ?>>Loved tracks</option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="lastfm-count">Count:</label></th>
					<td><input type="text" name="lastfm-count" value="<?php echo $options['count']; ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="lastfm-refresh">Refresh time (in minutes):</label></th>
					<td><input type="text" name="lastfm-refresh" value="<?php echo $options['refresh']; ?>" /><br />Only used when period is 'Recent tracks'</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="lastfm-offset">Off-set (from GMT +0):</label></th>
					<td><input type="text" name="lastfm-offset" value="<?php echo $options['offset']; ?>" /><br />Use +x or -x (for example +1 or -7)</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="lastfm-defaultthumb">Default thumb:</label></th>
					<td>
						<input type="text" name="lastfm-defaultthumb" value="<?php echo $options['defaultthumb']; ?>" />
<?php
			if ('' != $options['defaultthumb']) {
?>
						<br /><img src="<?php echo $options['defaultthumb']; ?>" />
<?php
			}
?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="lastfm-stylesheet">Add stylesheet:</label></th>
					<td>
						<select id="lastfm-stylesheet" name="lastfm-stylesheet">
							<option value="0"<?php if ('0' == $options['stylesheet']) { echo ' selected'; } ?>>None</option>
							<option value="2"<?php if ('2' == $options['stylesheet']) { echo ' selected'; } ?>>Plain and simple</option>
							<option value="1"<?php if ('1' == $options['stylesheet']) { echo ' selected'; } ?>>Fancy hovering effect</option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="lastfm-imgwidth">Thumbnail width:</label></th>
					<td>
						<input type="text" name="lastfm-imgwidth" value="<?php echo $options['imgwidth']; ?>" />
						<br />Only used when a stylesheet is selected (see previous setting).
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="lastfm-debug">Write debug info:</label></th>
					<td>
						<select id="lastfm-debug" name="lastfm-debug">
							<option value="0"<?php if ('0' == $options['debug']) { echo ' selected'; } ?>>No</option>
							<option value="1"<?php if ('1' == $options['debug']) { echo ' selected'; } ?>>Yes</option>
						</select>
						<br />If your browser supports it, you can view debug info in the javascript console.
						<br />'Normal' visitors of your site will not see this. For a slightly better performance, keep this set to 'No'.
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="lastfm-submit">&nbsp;</label></th>
					<td><input class='button-primary' type="submit" name="lastfm-submit" value="Submit" /></td>
				</tr>
			</table>
		</form>
	</div>
<?php
}

// add link to configuration page
add_action('admin_menu', 'lfr_add_pages');

	//////////////////////
	// MAKE IT A WIDGET //
	//////////////////////


class LastFmRecordsWidget extends WP_Widget {

	function LastFmRecordsWidget() {
		parent::WP_Widget(false, $name = 'LastFmRecords');	
	}

	function widget($args, $instance) {		
		extract($args);
		$options = get_option('lastfm-records');

		echo "\n\n" . $before_widget . $before_title . $instance['title'] . $after_title . "\n";
		echo "<div id='lastfmrecords'></div>\n";
		echo $after_widget . "\n\n";
	}

	function update($new_instance, $old_instance) {				
		return $new_instance;
	}

	function form($instance) {				
		$title = esc_attr($instance['title']);
?>
            <p>
              <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
              </label>
            </p>
<?php 
  }
} // class LastFmRecordsWidget

// register LastFmRecords widget
add_action('widgets_init', create_function('', 'return register_widget("LastFmRecordsWidget");'));

?>