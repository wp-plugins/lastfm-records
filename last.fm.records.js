// Last.Fm Records 3.1
// released 2009-07-11

// Copyright 2008-2009 Jeroen Smeets
// http://jeroensmeets.net/

// Released under GPL license, with an additional remark:
// If you release this code as part of your own package,
// you have to change the API key. For more info, see
// http://www.last.fm/api/account

// TODO

// 3. fix highslide/lightbox option
// 2. Using [lastfmrecords|x|y] in a page or post doesn't work at the moment.
// 1. Get more images if necessary
//    not working ok, trying to fix this ASAP

var lastFmRecords = (function() {

  // private, reachable through public setters
  var _user;
  var _period        = 'recenttracks';
  var _count         = 6;
  var _styletype     = ''; // can be highslide, lightbox
  var _refreshmin    = 3;
  var _placeholder   = 'lastfmrecords';
  var _defaultthumb  = 'http://cdn.last.fm/depth/catalogue/noimage/cover_85px.gif';
  var _debug         = false;
  var _gmt_offset    = +1;

	/////////////
	// private //
	/////////////

	var _imgs_found    = [];

	// capitals to pretend these are constant
	var _LASTFM_APIKEY = 'fbfa856cc3af93c43359b57921b1e64e';
	var _LASTFM_WS_URL = 'http://ws.audioscrobbler.com/2.0/';

  function _logStatus(text) {
    if (_debug)
      if ('undefined' != typeof console)
        if ('function' == typeof console.log)
          console.log('last.fm.records: ' + text);
  };

  function _getLastFMData() {
    jQuery.getJSON(
    	_LASTFM_WS_URL + '?method=user.getrecenttracks&user=' + _user + '&api_key=' + _LASTFM_APIKEY + '&limit=50&format=json&callback=?',
    	lastFmRecords.processLastFmData
    );
  };

  function _getArtistData(_artistmbid) {
    // alert(_LASTFM_WS_URL + '?method=artist.getinfo&mbid=' + _artistmbid + '&api_key=' + _LASTFM_APIKEY + '&format=json');
    jQuery.getJSON(
    	_LASTFM_WS_URL + '?method=artist.getinfo&mbid=' + _artistmbid + '&api_key=' + _LASTFM_APIKEY + '&format=json&callback=?',
    	lastFmRecords.processArtistData
    );
  };

	function _errorInLastFmResponse(data) {
		var _errorfound = false;
		var _errormsg;
		jQuery.each(data, function(tag, val) {
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

	function _findLargestImage(_imgarray) {
		// _imgarray is an array returned by last.fm that looks like

		// "image":[{"#text":"http:\/\/images.amazon.com\/images\/P\/B00004YYTW.02._SCMZZZZZZZ_.jpg","size":"small"},
		// 	 			 {"#text":"http:\/\/images.amazon.com\/images\/P\/B00004YYTW.02._SCMZZZZZZZ_.jpg","size":"medium"},
		// 	 			 {"#text":"http:\/\/images.amazon.com\/images\/P\/B00004YYTW.02._SCMZZZZZZZ_.jpg","size":"large"}
		// 	 			]

   	_biggestYet = false;

    jQuery.each(_imgarray, function(j, _img) {
     	if ('large' == _img.size) {
     		_biggestYet = _img['#text'];
     		// biggest found, get out of this loop
     		return false;
     	} else if ('medium' == _img.size) {
     		_biggestYet = _img['#text'];
     	} else if (('small' == _img.size) && ('' == _biggestYet)) {
     		_biggestYet = _img['#text'];
     	}
    });
		return _biggestYet;    
	}

  function _processLastFmData(data) {
  	// error in response?
		if (_errorInLastFmResponse(data)) {
			return false;
		}

		// no error, so we expect recent tracks info
  	data = data.recenttracks.track;

    // loop through tracks
    // jQuery('track', data).each( function(key) {
		jQuery.each(data, function(i, _json) {  
      if (i > _count) {
        return false;
      }

      var track = [];
      track.cdcover    = _findLargestImage(_json.image);
      track.artistname = _json.artist['#text'];
      track.artistmbid = _json.artist['mbid'];
      track.name       = _json.name;
      track.mbid       = _json.mbid;
      track.url        = _json.url;
			if ('true' == _json.nowplaying) {
      	track.time     = 'listening now';
      } else {
      	track.time     = _getTimeAgo(_json.date['#text'], _gmt_offset);
      }

      _showCover(i, track);
    });

    if (_refreshmin > 0) {
      setTimeout('lastFmRecords.refreshCovers();', _refreshmin * 60000);
    }
  };

  function _showCover(_id, _track) {
  	// store last.fm data about this track in (well, near, thanks to jQuery) the image
  	jQuery.each(_track, function(tag, val) {
  		jQuery('#lastfmcover' + _id).data(tag, val);
  	});
  	
    // always set title of image
    jQuery('#lastfmcover' + _id).attr('title', _track.name + ' by ' + _track.artistname + ' (' + _track.time + ')');
    if ('' == _track.cdcover) {
			// no cover for cd, do we have an image for the artist?
			if (_imgs_found[_track.artistmbid] && ('*' != _imgs_found[_track.artistmbid])) {
				// yes, use that url
				jQuery('#lastfmcover' + _id).attr('src', _imgs_found[_track.artistmbid]);
			} else {
				// nope, let's ask last.fm.
				if ('*' != _imgs_found[_track.artistmbid]) {
				 	_logStatus('cover for ' + _track.name + ' not found, trying to find image of artist ' + _track.artistname);
				 	// Setting a star to know we're already looking for this one
					_imgs_found[_track.artistmbid] = '*';
					_getArtistData(_track.artistmbid);
				}

     		jQuery('#lastfmcover' + _id).attr('src', _defaultthumb);
     		jQuery('#lastfmcover' + _id).addClass(_track.artistmbid);
     		
     	}
    } else {
      // point src and href of parent a to the image
      // and make link clickable
      jQuery('#lastfmcover' + _id).attr('src', _track.cdcover).parent('a').attr('href', _track.url).unbind('click', lastFmRecords.dontFollowLink);
    }
  };

  function _processArtistData(data) {
  	// error in response?
		if (_errorInLastFmResponse(data)) {
			return false;
		}

    // data = data.artist;
    jQuery.each(data, function(i, _json) {
    	_imgurl = _findLargestImage(_json.image);
    	_mbid   = _json.mbid;
    	// find images that need to be changed
    	jQuery('.' + _mbid).each( function() {
    	  // point src and href of parent a to the image
    	  // and make link clickable
    	  jQuery(this).attr('src', _imgurl).removeClass(_mbid).parent('a').attr('href', _json.url).unbind('click', lastFmRecords.dontFollowLink);
    	});

			// remember we have an url for this artist
			_imgs_found[_mbid] = _imgurl;

    	// stop looping
    	return false;
    });
  };

  function _getTimeAgo(_t, gmt_offset) {
    // difference between then and now
    var _diff = new Date() - new Date(_t);
    // take into account the timezone difference
    _diff = _diff - (gmt_offset * 60000 * 60);

    var _d = [];
    // how may years in the difference? not many, I hope ;-)
    _d.ye = parseInt(_diff / (1000 * 60 * 60 * 24 * 365));
    _d.da = parseInt(_diff / (1000 * 60 * 60 * 24)) - (_d.ye * 365);
    _d.ho = parseInt(_diff / (1000 * 60 * 60)) - (_d.ye * 365 * 24) - (_d.da * 24);
    _d.mi = parseInt(_diff / (1000 * 60)) - (_d.ye * 365 * 24 * 60) - (_d.da * 24 * 60) - (_d.ho * 60);

    var _meantime = [];
    if (_d.ye > 0) { _meantime.push(_d.ye + ' year' + _getPluralS(_d.ye)); }
    if (_d.da > 0) { _meantime.push(_d.da + ' day' + _getPluralS(_d.da)); }
    if (_d.ho > 0) { _meantime.push(_d.ho + ' hour' + _getPluralS(_d.ho)); }
    if (_d.mi > 0) { _meantime.push(_d.mi + ' minute' + _getPluralS(_d.mi)) };

    // TODO: replace last comma with 'and'
    return _meantime.join(', ') + ' ago';
  };

  function _getPluralS(_c) {
    return (1 == _c) ? '' : 's';
  }

	////////////
	// public //
	////////////

  return {
    
    addStyle: function(styletype) {
      _logStatus('function addStyle not supported yet');
    },

    setUser: function(orUsername) {
      // TODO: validation
      _user = orUsername;
    },

    setPeriod: function(orPeriod) {
      // TODO: just todo ;-)
      _period = 'recenttracks';
    },

    setCount: function(orCount) {
      var _pI = parseInt(orCount);
      if (_pI > 0) {
        _count = _pI;
      }
    },

    setStyle: function(orStyle) {
      // TODO: validation
      _styletype = orStyle;
    },

    setPlaceholder: function(orPlaceholder) {
      // TODO: validate
      _placeholder = orPlaceholder;
    },

    setDefaultThumb: function(orDefaultThumb) {
    	// TODO: validate
    	_defaultthumb = orDefaultThumb;
    },

    setRefreshMinutes: function(orRefresh) {
      var _pI = parseInt(orRefresh);
      if (_pI > 0) {
        _refreshmin = _pI;
      }
    },

    setTimeOffset: function(orOffset) {
      _gmt_offset = parseInt(orOffset);
    },

		debug: function() {
			_debug = true;
		},

		dontFollowLink: function() {
			// made it a function so I can unbind it
			return false;
		},

    init: function(_settings) {
      _logStatus('initializing');

			if (_settings.placeholder)  { this.setPlaceholder(_settings.placeholder); }
      if (jQuery("div#" + _placeholder).length < 1) {
        _logStatus('error: placeholder for cd covers not found');
        return false;
      }

			if (_settings.username)     { this.setUser(_settings.username) };
			if (_settings.defaultthumb) { this.setDefaultThumb(_settings.defaultthumb); }
			if (_settings.count)        { this.setCount(_settings.count); }
			if (_settings.refresh)      { this.setRefreshMinutes(_settings.refresh); }
			if (_settings.offset)       { this.setTimeOffset(_settings.offset); }
			if (_settings.styletype)    { this.setStyle(_settings.styletype); }

      // add an ul to placeholder div
      var _ul = jQuery("<ul></ul>").appendTo("div#" + _placeholder);
      if (!_ul) {
        _logStatus('error: placeholder for cd covers not found');
      }

      // add temporary cd covers
      _logStatus('adding temporary cd covers');
      var _img, _li;
      for (var i = 0; i < _count; i++) {
        _li  = jQuery('<li></li>').attr('style', 'display: inline;');

        _a   = jQuery('<a></a>').bind('click', lastFmRecords.dontFollowLink).attr('href', '').appendTo(_li);
        // highslide?
        if ('highslide' == _styletype)  {
          _a.click( function() { return hs.expand(this); });
        }

        if ('lightbox' == _styletype) {
          _a.attr('rel', 'lightbox');
        }

        _img = jQuery('<img></img>').attr('src', _defaultthumb).attr('id', 'lastfmcover' + i).appendTo(_a);

        _li.appendTo(_ul);
      }

			_logStatus('retreiving last.fm data, will refresh every ' + _refreshmin + ' minute' + _getPluralS(_refreshmin) + '.');
      _getLastFMData();
    },

    refreshCovers: function() {
      _logStatus('Checking to see if there\'s anything new in your list.');
      _getLastFMData();
    },

    processLastFmData: function(data) {
      // handle it internally
      _processLastFmData(data);
    },

    processArtistData: function(data) {
      // handle it internally
      _processArtistData(data);
    }

  };

})();
