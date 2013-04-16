<?php
/*
Plugin Name: Last.Fm Records
Plugin URI: http://wordpress.org/extend/plugins/lastfm-records/
Description: The Last.Fm Records plugin lets you show what you are listening to, with a little help from our friends at last.fm.
Author: Jeroen Smeets
Author URI: http://jeroensmeets.net/
Version: 1.7.3
License: GPL2
*/

////////////////////////////////////
// Enqueue scripts and stylesheet //
////////////////////////////////////

add_action('init', 'lfr_add_js');
function lfr_add_js() {

	// only load script when needed
	// great idea from http://scribu.net/wordpress/optimal-script-loading.html
	global $lfr_addscript;
	$lfr_addscript = false;

	// remember settings for each widget and shortcode
	global $_lfr_settings;
	$_lfr_settings				= array();
	$_lfr_settings['shortcode']	= array();

	wp_register_script('lastfmrecords', plugins_url('lastfm-records/last.fm.records.js'), array('jquery'), '1.0');
}

add_action('wp_footer', 'lfr_printscript');
function lfr_printscript() {
	global $lfr_addscript;

	if ( ! $lfr_addscript )
		return;

	lfr_add_js_options();
	wp_print_scripts('lastfmrecords');
	lfr_add_widget_settings();
}

function lfr_get_stylesheet_fullname($_int) {

	// display stylesheet? version 1.5.3 had 0 or 1, so we build from there

	$_stylesheet = false;
	switch($_int) {
		case 1:
			$_stylesheet = 'hover';
			break;
		case 2:
			$_stylesheet = 'simple';
			break;
/*
  	case 3:
  	  $_stylesheet = 'slideshow';
  	  break;
*/
	}

	return $_stylesheet;
}

function lfr_add_js_options() {
	global $lfr_addscript, $_lfr_settings;
 
	if ( ! $lfr_addscript )
		return;

	// use wordpress offset
	$opts = get_option('lastfm-records');
	$opts['offset'] = get_option('gmt_offset');

	// why did I use two different names for the same setting?
	$opts['user'] = $opts['username'];
	unset($opts['username']);

	$_stylesheet = lfr_get_stylesheet_fullname($opts['stylesheet']);

	// the stylesheet itself is added by the javascript
	$opts['stylesheet'] = $_stylesheet;

	$_lfr_settings['widget'] = $opts;

	// options for shortcodes have been added to this array in function that displays the html for the shortcode
	// but they are probably incomplete
	foreach($_lfr_settings['shortcode'] as $_shortcode_nr => $_shortcode_settings) {
		$_lfr_settings['shortcode'][$_shortcode_nr] = wp_parse_args($_shortcode_settings, $opts);
	}

	// thx: http://wordpress.stackexchange.com/questions/8655/pass-object-json-to-wp-localize-script
	$_tojs = array( 'l10n_print_after' => 'lfr_config = ' . json_encode( $_lfr_settings ) . ';' );

	wp_localize_script('lastfmrecords', 'lfr_config', $_tojs);
}

function lfr_add_widget_settings() {
?>
  <script type='text/javascript'>
    jQuery(document).ready( function() {
      jQuery('.lastfmrecords').lastFmRecords(lfr_config);
    });
  </script>
<?php
}

///////////////////
// Add shortcode //
///////////////////

// [lastfmrecords period="recenttracks" count="8"]
add_shortcode('lastfmrecords', 'lfr_shortcode');
function lfr_shortcode($atts) {
	global $lfr_addscript, $_lfr_settings;
	$lfr_addscript = true;

	static $lfr_count = 0;
	$lfr_count++;

	// save settings for inclusion in javascript in wp_footer action
	$_lfr_settings['shortcode'][$lfr_count] = $atts;
	
	$_style = lfr_get_stylesheet_fullname($atts['stylesheet']);
	if ($_style) {
		$_result  = "      <div id='lfr_shortcode" . $lfr_count . "' class='lastfmrecords lfr_widget lfr_" . $_style . "'></div>\n\n";
	} else {
		$_result  = "      <div id='lfr_shortcode" . $lfr_count . "' class='lastfmrecords lfr_widget'></div>\n\n";
	}

	return $_result;
}

//////////////////////////////////////////////
// Add link to settings in 'Manage plugins' //
//////////////////////////////////////////////

add_filter('plugin_action_links', 'set_plugin_meta', 10, 2);
function set_plugin_meta($links, $file) {
	$plugin = basename(__FILE__);

	// create link
	if (basename($file) == $plugin) {
		return array_merge(
					array('<a href="options-general.php?page=' . $plugin . '">' . __('Settings') . '</a>'),
					$links
		);
	}

	return $links;
}

////////////
// Widget //
////////////

// register LastFmRecords widget
add_action('widgets_init', create_function('', 'return register_widget("LastFmRecordsWidget");'));

class LastFmRecordsWidget extends WP_Widget {

	function LastFmRecordsWidget() {
		parent::WP_Widget(false, $name = 'LastFmRecords');	
	}

	function widget($args, $instance) {		
		global $lfr_addscript;
		$lfr_addscript = true;

		extract($args);
		$options = get_option('lastfm-records');

		$_style = (array_key_exists('stylesheet', $options)) ? ' lfr_' . $options['stylesheet'] : '';

		echo "\n\n" . $before_widget . $before_title . $instance['title'] . $after_title . "\n";
		echo "<div id='lastfmrecords' class='lastfmrecords" . $_style . "'></div>\n";
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

//////////////////
// Settings API //
//////////////////

class LastfmRecords {

	function init() {
		register_setting('Last_fm_Records', 'lastfm-records', array('LastfmRecords', 'validate'));

		// settings for Last.fm
		add_settings_section('lastfm-section', 'Last.fm' , array('LastfmRecords', 'section_lastfm'), basename(__FILE__));

		add_settings_field('username', 'Last.fm Username',  array('LastfmRecords', 'setting_username'), basename(__FILE__), 'lastfm-section');
		add_settings_field('period', 'Period to get data for',  array('LastfmRecords', 'setting_period'), basename(__FILE__), 'lastfm-section');

		// settings for displaying
		add_settings_section('visuals-section', 'Visuals' , array('LastfmRecords', 'section_visuals'), basename(__FILE__));

		add_settings_field('stylesheet', 'Add some style',  array('LastfmRecords', 'setting_stylesheet'), basename(__FILE__), 'visuals-section');
		add_settings_field('count', 'Number of covers',  array('LastfmRecords', 'setting_count'), basename(__FILE__), 'visuals-section');
		add_settings_field('imgwidth', 'Image width (pixels)',  array('LastfmRecords', 'setting_imgwidth'), basename(__FILE__), 'visuals-section');
		add_settings_field('defaultthumb', 'Default Thumbnail (url)', array('LastfmRecords', 'setting_defaultthumb'), basename(__FILE__), 'visuals-section');

		// optional settings
		add_settings_section('optional-section', 'Optional settings' , array('LastfmRecords', 'section_optional'), basename(__FILE__));

		add_settings_field('linknewscreen', 'Open links in new window',  array('LastfmRecords', 'setting_linknewscreen'), basename(__FILE__), 'optional-section');
		add_settings_field('refresh', 'Refresh covers every x minutes',  array('LastfmRecords', 'setting_refresh'), basename(__FILE__), 'optional-section');
		add_settings_field('offset', 'Your timezone',  array('LastfmRecords', 'setting_offset'), basename(__FILE__), 'optional-section');
		add_settings_field('debug', 'Show debug info',  array('LastfmRecords', 'setting_debug'), basename(__FILE__), 'optional-section');
	}

	function admin_menu() {
		if (!function_exists('current_user_can') || !current_user_can('manage_options')) {
			return;
		}

		if (function_exists('add_options_page')) {
			add_options_page('Last.fm Records', 'Last.fm Records', 'manage_options', basename(__FILE__), array('LastfmRecords', 'showform'));
		}
	}

	function showform() {
		$options = get_option('lastfm-records');
?>
        <div class="wrap">
          <?php screen_icon("options-general"); ?>
          <h2>Last.fm Records</h2>
          <form action="options.php" method="post">
            <?php settings_fields('Last_fm_Records'); ?>
            <?php do_settings_sections(basename(__FILE__)); ?>
            <p class="submit">
              <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
            </p>
          </form>
          <?php // LastfmRecords::show_donate_button(); ?>
        </div> 
<?php 
	}

	function validate($input) {
		return $input;
	}

	function section_lastfm() {
		// echo "Please fill in your last.fm username and the period you want to show covers for. If you can want, you can overrule the image last.fm uses when no cover is available.";
	}

	function section_visuals() {
		// echo "Here you can specify how the covers will be displayed.";
	}

	function section_optional() {
		// echo "These settings are not necessary for the plugin to function. Yet, if you played with Lego when you were young, you might want to play with them.";
	}

	function setting_username() {
		$options = get_option('lastfm-records');
		echo "<input id='plugin_username' name='lastfm-records[username]' size='40' type='text' value='{$options['username']}' />";
	}

	function setting_defaultthumb() {
		$options	= get_option('lastfm-records');
		$cover		= ('' != trim($options['defaultthumb']))
					? "<br /><img src='" . $options['defaultthumb'] . "' style='margin-top: 10px; max-height: 80px; border: 1px solid #ddd;' />" 
					: "";

		echo "<input id='plugin_defaultthumb' name='lastfm-records[defaultthumb]' size='40' type='text' value='{$options['defaultthumb']}' />" . $cover;
	}

	function setting_count() {
		$options = get_option('lastfm-records');
		echo "<input id='plugin_count' name='lastfm-records[count]' size='10' type='text' value='{$options['count']}' />";
	}

	function setting_imgwidth() {
		$options = get_option('lastfm-records');
		echo "<input id='plugin_imgwidth' name='lastfm-records[imgwidth]' size='10' type='text' value='{$options['imgwidth']}' />";
	}

	function setting_period() {
		$options = get_option('lastfm-records');
		$items = array(
					array('recenttracks', 'Recent tracks'),
					array('lovedtracks', 'Loved tracks'),

					array('tracks7day', 'Tracks -- last 7 days'),
					array('tracks3month', 'Tracks -- last 3 months'),
					array('tracks6month', 'Tracks -- last 6 months'),
					array('tracks12month', 'Tracks -- last 12 months'),
					array('tracksoverall', 'Tracks -- all time'),

					array('topalbums7day', 'Albums -- last 7 days'),
					array('topalbums3month', 'Albums -- last 3 months'),
					array('topalbums6month', 'Albums -- last 6 months'),
					array('topalbums12month', 'Albums -- last 12 months'),
					array('topalbumsoverall', 'Albums -- all time'),

					array('topartists7day', 'Artists -- last 7 days'),
					array('topartists3month', 'Artists -- last 3 months'),
					array('topartists6month', 'Artists -- last 6 months'),
					array('topartists12month', 'Artists -- last 12 months'),
					array('topartistsoverall', 'Artists -- all time')

				);
		echo "<select id='plugin_period' name='lastfm-records[period]'>\n";
		foreach($items as $item) {
			$selected = ($options['period'] == $item[0]) ? 'selected="selected"' : '';
			echo "<option value='" . $item[0] . "' " . $selected . ">" . $item[1] . "</option>\n";
		}
		echo "</select>\n";
	}

	function setting_stylesheet() {
		$options = get_option('lastfm-records');
		$_stylesheet = (!$options['stylesheet']) ? 2 : $options['stylesheet'];

		$items = array(
					array('0', 'None'),
					array('2', 'Plain and simple'),
					array('1', 'Fancy hovering effect'),
					// array('3', 'Slideshow')
				);
		echo "<select id='plugin_stylesheet' name='lastfm-records[stylesheet]'>\n";
		foreach($items as $item) {
			$selected = ($_stylesheet == $item[0]) ? 'selected="selected"' : '';
			echo "<option value='" . $item[0] . "' " . $selected . ">" . $item[1] . "</option>\n";
		}
		echo "</select>\n";
	}

	function setting_debug() {
		$options = get_option('lastfm-records');
		$items = array(
			array('0', 'No'),
			array('1', 'Yes')
		);
		echo "<select id='plugin_debug' name='lastfm-records[debug]'>\n";
		foreach($items as $item) {
			$selected = ($options['debug'] == $item[0]) ? 'selected="selected"' : '';
			echo "<option value='" . $item[0] . "' " . $selected . ">" . $item[1] . "</option>\n";
		}
		echo "</select>\n";
		echo "<br /><i>If your browser supports it, you can view debug info in the javascript console. For a slightly better performance, keep this set to 'No'.</i>";
	}

	function setting_linknewscreen() {
		$options = get_option('lastfm-records');
		$items = array(
					array('0', 'No'),
					array('1', 'Yes')
				);
		echo "<select id='plugin_linknewscreen' name='lastfm-records[linknewscreen]'>\n";
		foreach($items as $item) {
			$selected = ($options['linknewscreen'] == $item[0]) ? 'selected="selected"' : '';
			echo "<option value='" . $item[0] . "' " . $selected . ">" . $item[1] . "</option>\n";
		}
		echo "</select>\n";
	}

	function setting_refresh() {
		$options = get_option('lastfm-records');
		echo "<input id='plugin_refresh' name='lastfm-records[refresh]' size='10' type='text' value='{$options['refresh']}' /><br />"
		   . "<i>This setting only works when 'period' is set to Recent Tracks.</i>";
	}

	function setting_offset() {
		if (get_option('gmt_offset') < 0) {
			echo "gmt -" . get_option('gmt_offset');
		} else if (get_option('gmt_offset') > 0) {
			echo "gmt +" . get_option('gmt_offset');
		} else {
			echo "You're on gmt";
		}
		echo "<br /><i>The plugin uses the <a href='" . get_admin_url() . "options-general.php'>WordPress setting</a>.</i>";
	}

	// class is over
}
 
add_action('admin_init', array('LastfmRecords', 'init'));
add_action('admin_menu', array('LastfmRecords', 'admin_menu'));

?>