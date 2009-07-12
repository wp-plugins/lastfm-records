<?php

# Plugin Name: Last.Fm Records
# Version: 1.5
# Plugin URI: http://jeroensmeets.net/lastfmrecords/
# Description: The Last.Fm Records plugin lets you show what you are listening to, with a little help from our friends at last.fm.
# Author: Jeroen Smeets
# Author URI: http://jeroensmeets.net/

// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

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
  <script type='text/javascript' src='<?php echo WP_PLUGIN_URL; ?>/last.fm.records/last.fm.records.js'></script>
  <script type='text/javascript'>
    var _config = { username: '<?php echo $options['username']; ?>',
                    placeholder: '<?php echo $options['placeholder']; ?>',
                    defaultthumb: '<?php echo $options['defaultthumb']; ?>',
                    count: <?php echo $options['count']; ?>,
                    refresh: <?php echo $options['refresh']; ?>,
                    offset: <?php echo $options['offset']; ?>
                  };
    jQuery(document).ready( function() {
<?php
		if (true == $options['debug']) { echo "      lastFmRecords.debug();"; }
?>
      lastFmRecords.init(_config);
    });
  </script>
<?php
		if (true == $options['stylesheet']) {
?>
  <!-- added by plugin Last.Fm Records -->
  <style type="text/css">
    #lastfmrecords        { padding: 0px; padding-bottom: 10px; }

    /* thx to http://cssglobe.com/lab/overflow_thumbs/ */
    #lastfmrecords ul,
      #lastfmrecords li        { margin: 0; padding: 0; list-style: none; }
    #lastfmrecords li          { float: left; margin: 0px 5px 5px 0px; }
    #lastfmrecords a           { display: block; float: left; width: 100px; height: 100px; line-height: 100px; overflow: hidden; position: relative; z-index: 1; }
    #lastfmrecords a img       { float: left; position: absolute; margin: auto; min-height: 100px; }
    /* mouse over */
    #lastfmrecords a:hover     { overflow:visible; z-index:1000; border:none; }
    #lastfmrecords a:hover img { border: 1px  solid #999; background: #fff; padding: 3px; margin-top: -20px; margin-left: -20px; min-height: 120px;  }
  </style>

<?php
		}
	}
}

# add stylesheet(s) to head
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
										 'placeholder'  => 'lastfmrecords',
										 'defaultthumb' => 'http://cdn.last.fm/depth/catalogue/noimage/cover_85px.gif',
										 'stylesheet'   => '0',
										 'imgwidth'     => 85,
										 'count'        => 6,
										 'refresh'      => 1,
										 'offset'       => 1,
										 'debug'        => '0'
										);
	}

	// for those that had the old plugin
	if ('' == trim($options['placeholder']))  {
		$options['placeholder'] = 'lastfmrecords';
	}
	if ('' == trim($options['defaultthumb'])) {
		$options['defaultthumb'] = 'http://cdn.last.fm/depth/catalogue/noimage/cover_85px.gif';
	}

	// title, username, placeholder, defaultthumb, stylesheet, count, refresh, offset, debug

	if (array_key_exists('lastfm-submit', $_POST)) {
		// title is handled by widget settings
		# $options['title']        = strip_tags(stripslashes($_POST['lastfm-title']));
		$options['username']       = strip_tags(stripslashes($_POST['lastfm-username']));
		$options['placeholder']    = strip_tags(stripslashes($_POST['lastfm-placeholder']));
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
		$options['offset']         = intval($_POST['lastfm-offset']);
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
					<th scope="row"><label for="lastfm-count">Count:</label></th>
					<td><input type="text" name="lastfm-count" value="<?php echo $options['count']; ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="lastfm-refresh">Refresh time (in minutes):</label></th>
					<td><input type="text" name="lastfm-refresh" value="<?php echo $options['refresh']; ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="lastfm-offset">Off-set (from GMT +0):</label></th>
					<td><input type="text" name="lastfm-offset" value="<?php echo $options['offset']; ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="lastfm-placeholder">Placeholder:</label></th>
					<td><input type="text" name="lastfm-placeholder" value="<?php echo $options['placeholder']; ?>" /></td>
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
							<option value="0"<?php if ('0' == $options['stylesheet']) { echo ' selected'; } ?>>No</option>
							<option value="1"<?php if ('1' == $options['stylesheet']) { echo ' selected'; } ?>>Yes</option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="lastfm-imgwidth">Thumbnail width:</label></th>
					<td>
						<input type="text" name="lastfm-imgwidth" value="<?php echo $options['imgwidth']; ?>" />
						<br />Only used when Stylesheet is set to Yes.
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="lastfm-debug">Write debug info to the console:</label></th>
					<td>
						<select id="lastfm-debug" name="lastfm-debug">
							<option value="0"<?php if ('0' == $options['debug']) { echo ' selected'; } ?>>No</option>
							<option value="1"<?php if ('1' == $options['debug']) { echo ' selected'; } ?>>Yes</option>
						</select>
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

# output for sidebar
function lfr_show_widget($args) {
	extract($args);

	$options = get_option('lastfm-records');

	echo "\n\n" . $before_widget . $before_title . $options['title'] . $after_title . "\n";
	echo "<div id='" . $options['placeholder'] . "'></div>\n";
	echo $after_widget . "\n\n";
}

function lfr_widget_options() {
	$options = get_option('lastfm-records');

	if (($_POST['lastfmrecords-submit']) && ("" != $_POST['lastfmrecords-title'])) {
		$options['title'] = strip_tags(stripslashes($_POST['lastfmrecords-title']));
		update_option('lastfm-records', $options);
	}
	$title = htmlspecialchars($options['title'], ENT_QUOTES);
?>
    <p style="text-align:right;">
      <label for="lastfmrecords-title">title: 
        <input style="width: 200px;" id="lastfmrecords-title" name="lastfmrecords-title" type="text" value="<?php echo $title ?>" />
      </label>
    </p>
    <p>Other options are on the <a href="<?php echo get_option( 'siteurl' ); ?>/wp-admin/options-general.php?page=<?php echo strtolower(basename(__FILE__)); ?>">options page</a> for this plugin.</p>
    <input type="hidden" id="lastfmrecords-submit" name="lastfmrecords-submit" value="1" />    
<?php
}

# does this wordpress environment support widgets?
if (function_exists('register_sidebar_widget')) {
  register_sidebar_widget('Last.Fm Records', 'lfr_show_widget');
  // and we need a small form to add a title in the sidebar
  register_widget_control('Last.Fm Records', 'lfr_widget_options', 375, 95);
}

?>