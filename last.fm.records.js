// released together with Last.Fm Records plugin for WordPress
// version 1.7.5

// a plugin for jQuery

// Licensed GPL2 2008 -
// Jeroen Smeets
// http://www.lastfmrecords.com/

// Released under GPL2 license, with an additional remark:
// If you release this code as part of your own package,
// please change the API key. For more info, see
// http://www.last.fm/api/account

(function($) {

	jQuery.fn.lastFmRecords = function(options) {

		var settings = {

			// your last.fm user name
			'user'              : 'hondjevandirkie',

			// choose from
			// - recenttracks
			// - lovedtracks

			// - tracks7day
			// - tracks3month
			// - tracks6month
			// - tracks12month
			// - tracksoverall

			// - topalbums7day
			// - topalbums3month
			// - topalbums6month
			// - topalbums12month
			// - topalbumsoverall

			// - topartists7day
			// - topartists3month
			// - topartists6month
			// - topartists12month
			// - topartistsoverall

			'period'            : 'recenttracks',

			// in pixels
			'imgwidth'          : 80,

			'count'             : 6,

			// can be set to simple, hover
			'stylesheet'        : false,

			// can be highslide, lightbox 
			'styletype'         : '', 

			// only works when period is set to recenttracks
			'refreshmin'        : 3,
			// 'defaultthumb'      : 'http://cdn.last.fm/depth/catalogue/noimage/cover_85px.gif',
			'defaultthumb'		: false,

			// add logging data to console (when console is available)
			'debug'             : false,

			// timezone offset
			'gmt_offset'        : '+1',

			// open links in new browser screen
			'linknewscreen'     : false,

			// capitals to pretend these are constants
			'LASTFM_APIKEY'     : 'fbfa856cc3af93c43359b57921b1e64e',
			'LASTFM_WS_URL'     : 'https://ws.audioscrobbler.com/2.0/'

		};

		// keep track of artist images we found
		// TODO: would be nice to make it usable for other instances
		var _imgs_found    = [];

		var _logStatus = function(text) {
			if ((false !== _settings.debug) && ('0' !== _settings.debug))
				if ('undefined' != typeof console)
					if ('function' == typeof console.log)
						if ('object' == typeof text)
							console.log(text);
						else
							console.log('last.fm.records: ' + text);
		};

		// made it a function so I can unbind it
		var _dontFollowLink = function() {
			return false;
		}

		var _errorInLastFmResponse = function(_data) {
			var _errorfound = false;
			var _errormsg;
			jQuery.each(_data, function(tag, val) {
				if ('error' == tag) {
					_errorfound = true;
					_errormsg = ' (' + val + ')';
				}
				if (_errorfound && ('message' == tag)) {
					_errormsg = val + _errormsg;
				}
			});

			if (_errorfound) {
				_logStatus('last.fm reported error: ' + _errormsg);
			}

			return _errorfound;
		}

		var _findLargestImage = function(_imgarray, _lastfmdefaultimg) {

			_sizeFound	= false;
			_imgUrl		= '';

			jQuery.each(_imgarray, function(j, _img) {

				if ('extralarge' == _img.size) {
					_imgUrl		= _img['#text'];
					// get out of this loop
					return false;
				}

				if ('large' == _img.size) {
					_imgUrl		= _img['#text'];
					_sizeFound	= 'large';
				} else if (('medium' == _img.size) && ('large' != _sizeFound)) {
					_imgUrl		= _img['#text'];
					_sizeFound	= _img.size;
				} else if (('small' == _img.size) && (false === _sizeFound)) {
					_imgUrl		= _img['#text'];
					_sizeFound	= _img.size;
				}
			});

			return _imgUrl;
		}

		function _getPluralS(_c) {
			return (1 == _c) ? '' : 's';
		};

		var _getTimeAgo = function(_t, gmt_offset) {
			// _logStatus('trying to figure out how long ago ' + _t + ' is, in your timezone ' + gmt_offset);

			// difference between then and now
			var _diff = new Date() - new Date(_t);
			// take into account the timezone difference
			_diff = _diff - (gmt_offset * 60000 * 60);

			var _d = {
				'year'   : 1000 * 60 * 60 * 24 * 365,
				'day'    : 1000 * 60 * 60 * 24,
				'hour'   : 1000 * 60 * 60,
				'minute' : 1000 * 60
			};

			var _meantime = [];
			jQuery.each(_d, function(_unit, _amount) {
				var _a = parseInt(_diff / _amount);
				if (_a > 0) {
					_meantime.push(_a + ' ' + _unit + _getPluralS(_a));
					_diff = _diff - (_a * _amount);
				}
			});

			// replace last comma with 'and'
			var _result = _meantime.pop() + ' ago';
			if (_meantime.length > 0) {
				_result = _meantime.join(', ') + ' and ' + _result;
			}

			return _result;
		};

		var _processLastFmData = function(_elem, _data) {

			_settings = _elem.data('settings');

			// error in response?
			if (_errorInLastFmResponse(_data)) {
				return false;
			}

			// get the data from the json
			// _logStatus(_settings.period);
			// _logStatus(_data);

			switch(_settings.period) {
				case 'recenttracks':
					_data = _data.recenttracks.track;
					break;
				case 'lovedtracks':
					_data = _data.lovedtracks.track;
					break;
				case 'toptracks':
				case 'tracks7day':
				case 'tracks3month':
				case 'tracks6month':
				case 'tracks12month':
				case 'tracksoverall':
					_data = _data.toptracks.track;
					break;
				case 'topalbums':
				case 'topalbumsoverall':
				case 'topalbums7day':
				case 'topalbums3month':
				case 'topalbums6month':
				case 'topalbums12month':
					_data = _data.topalbums.album;
					break;
				case 'topartists':
				case 'topartistsoverall':
				case 'topartists7day':
				case 'topartists3month':
				case 'topartists6month':
				case 'topartists12month':
					_data = _data.topartists.artist;
					break;
				default:
					// should've been caught by _getLastFmData()
					_logStatus("Sorry, period '" + _settings.period + "' is unknown.");
					return false;
				}

				if (!_data) {
					_logStatus('No return data from Last.fm');
					return false;
				}

				// JNS 2009-07-30
				// thanks to my friend xample who only listened to 1 album last week,
				// i was able to fix this bug:
				// if only one result is found, data is not an array of albums/tracks but just one album/track.
				if (_data.name && 'string' == typeof _data.name) {
					_data = [_data];
				}

				_logStatus('last fm returned ' + _data.length + ' records.');

				jQuery.each(_data, function(i, _json) {  
					if (i > _settings.count) {
						return false;
					}
					// _logStatus(_json);

					// 20130416 jns
					// now that I'm adding top artists, the variable name "track" is not correct
					// then again, it has been incorrect ever since I added topalbums
					var track = [];
					track.cdcover		= _json.image ? _findLargestImage(_json.image, _settings.LASTFM_DEFAULTIMG) : false;
					if ('topartists' == _settings.period.slice(0, 10)) {
						track.artistname	= _json.name;
						track.artistmbid	= '';
						track.name			= '';
					} else {
						track.artistname	= _json.artist['#text'] || _json.artist.name;
						track.artistmbid	= _json.artist['mbid'];
						track.name			= _json.name;
					}
					track.mbid			= _json.mbid;
					// does the url include 'http://'? if not add it to prevent relative links
					track.url			= ('http://' == _json.url.substr(0, 7).toLowerCase())
										? _json.url
										: 'http://' + _json.url;
					if ('recenttracks' == _settings.period) {
						// aaargh! json has changed!
						if (_json['@attr'] && ('true' == _json['@attr'].nowplaying)) {
							track.time	= 'listening now';
						} else {
							track.time	= ('undefined' == typeof _json.date)
										? 'some time'
										: _getTimeAgo(_json.date['#text'], _settings.gmt_offset);
						}
					} else {
						track.time = '';
					}

					_showCover(i, track, _elem);
				});

				if ((_settings.refreshmin > 0) && ('recenttracks' == _settings.period)) {
					_logStatus(
						'set timer to refresh covers in ' + _settings.refreshmin + 
						' minute' + _getPluralS(_settings.refreshmin) + 
						' for element ' + _elem.attr('id') + '.');
					setTimeout(
						function() {
							_getLastFmData(_elem);
						},
						_settings.refreshmin * 60000
					);
				}
			};

			var _showCover = function(_id, _track, _elem) {

				// store last.fm data about this track in (well, near, thanks to jQuery) the image
				jQuery.each(_track, function(tag, val) {
					jQuery('#lfr_' + _elem.attr("id") + "_" + _id).data(tag, val);
				});

				// always set title of image
				// 20130416 jns: when selecting topartists, there are no track titles
				var _title = _track.artistname;
				if ('' != _track.name) {
					var _title = _track.name + ' by ' + _title;
				}
				if ('' != _track.time) {
					_title += ' (' + _track.time + ')';
				}
				jQuery('#lfr_' + _elem.attr("id") + "_" + _id).attr('title', _title);

				// cover found?
				if (('' != _track.cdcover) && (_track.cdcover.length > 0)) {
					// point src and href of parent a to the image
					// and make link clickable
					jQuery('#lfr_' + _elem.attr("id") + "_" + _id).attr('src', _track.cdcover).parent('a').attr('href', _track.url).unbind('click', _dontFollowLink);
				}

				// are we looking for an image of the artist at the moment?
				else if ('*' == _imgs_found[_track.artistmbid]) {
					// ok, it will be shown when it is found
				}

				// do we already have an image for the artist/band in memory?
				else if (_imgs_found[_track.artistmbid]) {
					jQuery('#lfr_' + _elem.attr("id") + "_" + _id).attr('src', _imgs_found[_track.artistmbid]).parent('a').attr('href', _track.url).unbind('click', _dontFollowLink);
				}

				// ok, no artist image. let's ask last.fm
				else if (_track.artistmbid) {
					_logStatus('cover for ' + _track.name + ' not found, trying to find image of artist ' + _track.artistname);
					// remember this image
					jQuery('#lfr_' + _elem.attr("id") + "_" + _id).addClass(_track.artistmbid);

					// ask last.fm
					_getArtistData(_elem, _track.artistmbid);
				} else {
					_logStatus('no image found for track ' + _track.name);
				}
			};

			var _getLastFmData = function(_elem) {

				_settings = _elem.data('settings');

				// _logStatus("gettings last.fm data for period " + _settings.period + " to show in div " + _elem.attr("id"));

				var _method = false;
				switch(_settings.period) {
					case 'lovedtracks':
						_method = 'user.getlovedtracks';
						break;
					case 'topalbums':
					case 'topalbumsoverall':
						_method = 'user.gettopalbums&period=overall';
						break;
					case 'topalbums7day':
						_method = 'user.gettopalbums&period=7day';
						break;
					case 'topalbums3month':
						_method = 'user.gettopalbums&period=3month';
						break;
					case 'topalbums6month':
						_method = 'user.gettopalbums&period=6month';
						break;
					case 'topalbums12month':
						_method = 'user.gettopalbums&period=12month';
						break;
					case 'toptracks':
						_method = 'user.gettoptracks';
						break;
					case 'tracksoverall':
						_method = 'user.gettoptracks&period=overall';
						break;
					case 'tracks7day':
						_method = 'user.gettoptracks&period=7day';
						break;
					case 'tracks3month':
						_method = 'user.gettoptracks&period=3month';
						break;
					case 'tracks6month':
						_method = 'user.gettoptracks&period=6month';
						break;
					case 'tracks12month':
						_method = 'user.gettoptracks&period=12month';
						break;
					case 'recenttracks':
						_method = 'user.getrecenttracks';
						break;
					case 'topartists':
					case 'topartistsoverall':
						_method = 'user.gettopartists&period=overall';
						break;
					case 'topartists7day':
						_method = 'user.gettopartists&period=7day';
						break;
					case 'topartists3month':
						_method = 'user.gettopartists&period=3month';
						break;
					case 'topartists6month':
						_method = 'user.gettopartists&period=6month';
						break;
					case 'topartists12month':
						_method = 'user.gettopartists&period=12month';
						break;
					default:
						// no default
				}

			if (false == _method) {
				_logStatus("Sorry, period '" + _settings.period + "' is unknown.");
				return false;
			}

			_reqlastfmdata =	_settings.LASTFM_WS_URL + '?method=' + _method + '&limit=' + _settings.count + 
								'&user=' + _settings.user + '&api_key=' + _settings.LASTFM_APIKEY + '&format=json&callback=?';
			_logStatus(_reqlastfmdata);
			jQuery.getJSON(
				_reqlastfmdata,
				function(data) {
					_processLastFmData(_elem, data)
				}
			);
		};

		var _getArtistData = function(_elem, _artistmbid) {
			// Setting a star to know we're already looking for this one
			_imgs_found[_artistmbid] = '*';

			jQuery.getJSON(
				_settings.LASTFM_WS_URL + '?method=artist.getinfo&mbid=' + _artistmbid + '&api_key=' + _settings.LASTFM_APIKEY + '&format=json&callback=?', 
				function(data) {
					_processArtistData(_elem, data);
				}
			);
		};

		var _processArtistData = function(_elem, data) {
			// error in response?
			if (_errorInLastFmResponse(data)) {
				return false;
			}

			jQuery.each(data, function(i, _json) {
				_imgurl = _findLargestImage(_json.image);
				_mbid   = _json.mbid;
				// find images that need to be changed, they have a class with the artist mbid
				jQuery('.' + _mbid).each( function() {
					// point src and href of parent a to the image and make link clickable
					jQuery(this).attr('src', _imgurl).removeClass(_mbid).parent('a').attr('href', _json.url).unbind('click', _dontFollowLink);
				});

				// remember we have an url for this artist
				_imgs_found[_mbid] = _imgurl;

				// stop looping
				return false;
			});
		};

		var _addTempCovers = function(_elem) {

			_settings = _elem.data('settings');

			// add temporary cd covers
			_logStatus('adding temporary cd covers to div ' + _elem.attr("id"));

			// add an ol to the element div
			var _ol = jQuery("<ol></ol>").appendTo(_elem);

			var _img, _li;
			for (var i = 0; i < _settings.count; i++) {
				_li  = jQuery('<li></li>'); //.attr('style', 'display: inline;');

				_a   = jQuery('<a></a>').bind('click', _dontFollowLink).attr('href', '').appendTo(_li);

				// highslide
				if ('highslide' == _settings.styletype)  {
					_a.click( function() { return hs.expand(this); });
				}

				// lightbox
				else if ('lightbox' == _settings.styletype) {
					_a.attr('rel', 'lightbox');
				}

				// add target=_blank to link 
				if ('1' == _settings.linknewscreen) {
					_a.attr('target', '_blank');
				}

				_img = jQuery('<img></img>')
						.attr('src', _settings.defaultthumb)
						.attr('id', 'lfr_' + _elem.attr("id") + "_" + i)
						.on('error', function() { jQuery(this).attr("src", _settings.defaultthumb); })
						.appendTo(_a);

				_li.appendTo(_ol);
			}

			// TODO: start slideshow when all images have been added
/*
      if ('slideshow' == _settings.stylesheet) {
        _elem.lastFmRecordsSlideshow(true);
      }
*/
		};

		var _addStylesheet = function(_elem) {

			// this function creates a piece of css and adds it to the DOM
			// it's not possible to apply the css to the element itself,
			// as not all the children (images, links, etc.) have been added yet

			// TODO: there's overlap between the different pieces of css
			// as every occurance (widgets, shortcodes) gets its own css injected.

			_settings = _elem.data('settings');
			_elemname = '#' + _elem.attr('id');

			_imgwidth = _settings.imgwidth;
			_minwidth = parseInt(_imgwidth) + 48;

			var _css = '';
			switch (_settings.stylesheet) {
				case 'simple':
					_css += _elemname + ' { padding: 0px; padding-bottom: 10px; } ';
					_css += _elemname + ' ol, ' + _elemname + ' li { margin: 0; padding: 0; list-style: none; } ';
					// clear the main list
					_css += _elemname + ' ol:after { content: ""; display: table; clear: both; } ';
					_css += _elemname + ' li { float: left; margin: 0px 5px 5px 0px; } ';
					_css += _elemname + ' a { display: block; float: left; width: ' + _imgwidth + 'px;'
						 + ' height: ' + _imgwidth + 'px; line-height: ' + _imgwidth + 'px;'
						 + 'overflow: hidden; position: relative; z-index: 1; } ';
					_css += _elemname + ' a img { float: left; position: absolute; margin: auto;'
						 + ' width: ' + _imgwidth + 'px; height: ' + _imgwidth + 'px; } ';
					break;
				case 'hover':
					_css += _elemname + ' { padding: 0px; padding-bottom: 10px; } ';
					// starting point was http://cssglobe.com/lab/overflow_thumbs/
					_css += _elemname + ' ol, ' + _elemname + ' li { margin: 0; padding: 0; list-style: none; } ';
					// clear the main list
					_css += _elemname + ' ol:after { content: ""; display: table; clear: both; } ';
					_css += _elemname + ' li { float: left; margin: 0px 5px 5px 0px; } ';
					_css += _elemname + ' a { display: block; float: left; width: ' + _imgwidth + 'px;'
						 + ' height: ' + _imgwidth + 'px; overflow: hidden; position: relative; z-index: 1; } ';
					_css += _elemname + ' a img { float: left; position: absolute; margin: -24px 24px 24px -24px;'
					     + ' padding: 3px; border: 1px solid #888; background-color: white;'
					     + ' width: ' + _minwidth + 'px; height: ' + _minwidth + 'px; max-width: ' + _minwidth + 'px; }';

					// mouse over
					_css += _elemname + ' a:hover { overflow:visible; z-index:1000; } ';
					break;
				// TODO implement slideshow
/*
				case 'slideshow':
					break;
*/
			}

			if ('' != _css) {
				_logStatus('adding css for element ' + _elemname);
				jQuery('<style type="text/css">' + _css + '</style>').appendTo(jQuery('head'));
			}
		}

		return this.each(
			function() {        
				$this	= jQuery(this);
				_opts	= {};

				if (options) {
					if (0 == $this.attr('id').indexOf('lfr_shortcode')) {
						_shortcodenr	= parseInt($this.attr('id').replace('lfr_shortcode', ''));
						_opts			= options['shortcode'][_shortcodenr];
					} else {
						// oops, forgot about the standalone option
						if ('widget' in options) {
							_opts			= options['widget'];
						} else {
							_opts			= options;
						}
					}

					if ( ( typeof _opts.ownapikey != "undefined" ) && ( '' != _opts.ownapikey ) ) {
						_opts.LASTFM_APIKEY = _opts.ownapikey;
						delete _opts.ownapikey;
					}

/*
					if ('' == _opts.defaultthumb) {
						delete _opts.defaultthumb;
					}
*/

					// jQuery.extend alters original options array? didn't use it as first argument.
					// i'm obviously using it wrong, but can't find why
					jQuery.each(settings, function(_key, _value) {
						if (undefined === _opts[_key]) {
							_opts[_key] = _value;
						}
					});

					// 20130918 default thumb at cdn.last.fm has disappeared from the internet
					if (false == _opts.defaultthumb) {
						jQuery.each(
							jQuery('script'),
							function() {
								if (this.src.indexOf('last.fm.records.js') >= 0) {
									_opts.defaultthumb = this.src.substr(0, this.src.lastIndexOf('/')+1) + 'defaultcover.png';
								}
							}
						);
					}
				}

				// save settings for this element to the element, so callback functions can use them
				$this.data('settings', _opts);

				_addStylesheet($this);
				_addTempCovers($this);
				_getLastFmData($this);
			}
		);
	}
})(jQuery);

/*
jQuery.fn.lastFmRecordsSlideshow = function(_start) {

    var _slideshow_index = 0;

    return this.each(
      function() {
        var _slides = jQuery(this).children('li').length;

        var _from = _slideshow_index;
        _slideshow_index++;
        if (_slideshow_index > _slides) {
          _slideshow_index = 0;
        }
        var _to = _slideshow_index;

        jQuery(this).children('li').eq(_from).fadeToggle('slow');
        jQuery(this).children('li').eq(_to).fadeToggle('slow');
      }
    );
  }
*/

