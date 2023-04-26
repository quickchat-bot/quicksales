var _chatStatus = 1; // CHAT_INCOMING
var _isFirstTime = 1;
var _chatInitiated = false;
var _timerThreadRunning = 0;
var _currentSeconds = 0;
var _currentMinutes = 0;
var _currentMiliseconds = 0;
var _currentHour = 0;
var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
var _activeIntervalID = 0;
var _refreshInterval = 3000;
var _dateObject = new Date();
var _timeHolder = _dateObject.getTime();
var _chatEndedNotificationDisplayed = false;
var _isSoundEnabled = true;
var _isSoundPluginLoaded = false;
var _userClosedWindow = false;
var _globalNoLoop = false;
var _messageGUIDList = new Array();
var _transfer = 0;

$(document).unbind('keydown');

if (typeof _swiftFieldRequiredMessage !== "undefined") {
	    $.validator.messages.required = _swiftFieldRequiredMessage;
	}

$(document).keydown(function (e) {
	var _elementName = $(e.target).get(0).tagName;

	if ( _elementName.toUpperCase() != 'TEXTAREA' && _elementName.toUpperCase() != 'INPUT' ) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if ( code == 8 ) {
			if (e.stopPropagation) e.stopPropagation();

			return false;
		}
    }
});

/**
*
*  Base64 encode / decode
*  http://www.webtoolkit.info/
*
**/

var Base64 = {

	// private property
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

	// public method for encoding
	encode : function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;

		input = Base64._utf8_encode(input);

		while (i < input.length) {

			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);

			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;

			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}

			output = output +
			this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
			this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

		}

		return output;
	},

	// public method for decoding
	decode : function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;

		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

		while (i < input.length) {

			enc1 = this._keyStr.indexOf(input.charAt(i++));
			enc2 = this._keyStr.indexOf(input.charAt(i++));
			enc3 = this._keyStr.indexOf(input.charAt(i++));
			enc4 = this._keyStr.indexOf(input.charAt(i++));

			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;

			output = output + String.fromCharCode(chr1);

			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}

		}

		output = Base64._utf8_decode(output);

		return output;

	},

	// private method for UTF-8 encoding
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";

		for (var n = 0; n < string.length; n++) {

			var c = string.charCodeAt(n);

			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}

		}

		return utftext;
	},

	// private method for UTF-8 decoding
	_utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;

		while ( i < utftext.length ) {

			c = utftext.charCodeAt(i);

			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}

		}

		return string;
	}

}

function OnLoaded() {
	$('#chatfullname').focus();
}


function OnChatLoaded() {
	OnLoaded();

	_timerThreadRunning = 1;
	TimerLoopThread();

	ExecuteChatLoopURL();
	_activeIntervalID = setInterval("ExecuteChatLoopURL();", _refreshInterval);

	window.onerror = function() {
		return true;
	}
}

function LiveChatBeforeUnload() {
	_userClosedWindow = true;

	if (_chatInitiated) {
		var _oldChatStatus = _chatStatus;
		_chatStatus = 3;

		LoadXMLHTTPRequest(_swiftChatEndURL, '', true);
		if (_oldChatStatus == 2)
		{
			return _swiftLanguage['chatendsurvey'];
		}
	}

	return;
}

function ValidateSurveyForm() {
	var _emailExpression = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,6}$/;

	$('#surveyform').validate();
	if (!$('#surveyform').valid()) {

		return false;
	}

	if ($('#chatfullname').val() == "" || $('#chatemail').val() == "" || $('#chatsubject').val() == "") {
		// Alert!
		$('#chatemailerror').fadeOut('medium');
		$('#chaterror').fadeIn('medium');
		return false;
	}

	$('#chaterror').fadeOut('medium');

	var _emailValue = $('#chatemail').val();
	if (!_emailValue.match(_emailExpression))
	{
		// Alert
		$('#chatemailerror').fadeIn('medium');
		return false;
	}

	return true;
}

function ValidateChatForm(isMessage) {
	var _emailExpression = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,256}$/;

	var _formName = 'chatform';
	if (isMessage) {
		_formName = 'messageform';
	}
	$("form").bind("submit", function(e){$('#' + _formName).find("input:submit").removeAttr('disabled');});

	$('#' + _formName).validate({rules: {captcha: 'required'}});
	if (!$('#' + _formName).valid()) {
		return false;
	}

	if (!isMessage && ($('#chatfullname').val() == "" || $('#chatemail').val() == "")) {
		// Alert!
		$('#chatemailerror').fadeOut('medium');
		$('#chaterror').fadeIn('medium');
		return false;
	} else if (isMessage && ($('#chatfullname').val() == "" || $('#chatemail').val() == "" || $('#chatsubject').val() == "" || $('#chatmessage').val() == "" || $('input[name="captcha"]').val() == "")) {
		// Alert!
		$('#chatemailerror').fadeOut('medium');
		$('#chaterror').fadeIn('medium');
		return false;
	}

	$('#chaterror').fadeOut('medium');

	var _emailValue = $('#chatemail').val();
	if (!_emailValue.match(_emailExpression))
	{
		// Alert
		$('#chatemailerror').fadeIn('medium');
		return false;
	}
	$("form").bind("submit", function(e){$('#' + _formName).find("input:submit").attr("disabled", "disabled");});

	return true;
}

function BackupThreadCheck() {
	_dateObject = new Date();
	_currentTime = _dateObject.getTime();
	_timeDifference = _currentTime - _timeHolder;

	// More than 8 seconds? probably the loop stopped somehow.. we push it out manually
	if (_timeDifference > 8000)
	{
		// When we trigger the backup.. we want to make sure timer gets reset (just in case the server dies or something)
		_dateObject = new Date();
		_currentTime = _dateObject.getTime();
	//	alert('Current Time: ' + _currentTime + ', Time Holder: ' + _timeHolder + ', Time Difference: ' + _timeDifference);

		ExecuteChatLoopURL();
	}
}

function nl2br (str, is_xhtml) {
    // Converts newlines to HTML line breaks
    //
    // version: 1006.1915
    // discuss at: http://phpjs.org/functions/nl2br
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Philip Peterson
    // +   improved by: Onno Marsman
    // +   improved by: Atli Þór
    // +   bugfixed by: Onno Marsman
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Maximusya
    // *     example 1: nl2br('Kevin\nvan\nZonneveld');
    // *     returns 1: 'Kevin\nvan\nZonneveld'
    // *     example 2: nl2br("\nOne\nTwo\n\nThree\n", false);
    // *     returns 2: '<br>\nOne<br>\nTwo<br>\n<br>\nThree<br>\n'
    // *     example 3: nl2br("\nOne\nTwo\n\nThree\n", true);
    // *     returns 3: '\nOne\nTwo\n\nThree\n'
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '' : '<br>';

    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
}

function TimerLoopThread() {
	_currentMiliseconds += 1;
	if (_currentMiliseconds == 10){
		_currentMiliseconds = 0;
		_currentSeconds += 1;
	}
	if (_currentSeconds == 60){
		_currentSeconds = 0;
		_currentMinutes += 1;
	}
	if (_currentMinutes == 60) {
		_currentHour += 1;
		_currentMinutes = 0;
		_currentSeconds = 0;
	}

	_stringSeconds = "" + _currentSeconds;
	_stringMinutes = "" + _currentMinutes;
	_stringMiliseconds = "" + _currentMiliseconds;
	if (_stringSeconds.length != 2){
		_stringSeconds = "0" + _currentSeconds;
	}
	if (_stringMinutes.length != 2){
		_stringMinutes = "0" + _currentMinutes;
	}

	var _finalValue = _currentHour + ":" + _stringMinutes +  ":" + _stringSeconds;

	if (_chatStatus != 3)
	{
		$('#chattoptoolbarrightclockticker').html(_finalValue);
	}

	// We implement a backup mechanism here
	BackupThreadCheck();

	if (_timerThreadRunning == 1 && !_globalNoLoop) {
		setTimeout("TimerLoopThread();", 100);
	}
}

function GenerateChatRandomNumber()
{
	_numberOne = 1;
	_numberTwo = 50000;
	var _generator = Math.random()*(_numberTwo-_numberOne);
	_generator = Math.round(_numberOne+_generator);
	return _generator;
}

function Base64Decode(input) {
	return Base64.decode(input);
}

function Base64Encode(input) {
	return Base64.encode(input);
}

function FocusMessageBox() {
	$('#chatpostmsg').focus();

	return true;
}

function RetrieveTimestamp() {
	_timeStampText = '';
	if (_swiftDisplayTimestamps == '1')
	{
		var _currentTimeStamp = new Date();
		var _currentMinutes = _currentTimeStamp.getMinutes();
		var _currentHours = _currentTimeStamp.getHours();

		if (_currentMinutes < 10) {
			_currentMinutes = '0' + _currentMinutes;
		}

		if (_currentHours < 10) {
			_currentHours = '0' + _currentHours;
		}

		_timeStampText = '<span class="timestamp">' + _currentHours + ':' + _currentMinutes + '</span>';
	}

	return _timeStampText;
}

function DisplayStaffMessage(_staffName, _message) {
	if (!_staffName || !_message)
	{
		return false;
	}

	_timeStampText = RetrieveTimestamp();

	/**
	 * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
	 *
	 * SWIFT-896 Canned response formatting (i.e. line breaks) should be maintained.
	 */
	$('#chatcontentcontainer').append('<div class="bubblecover_staff"><div class="msgwrapper staffbubble">' + _timeStampText + '<span class="staffname">' + _staffName + ': </span><span class="staffmessage">' + nl2br(AutoLink(htmlspecialchars(Base64Decode(_message))), false) + '</span></div></div>');

	ScrollDiv();

	PlaySound();

	return true;
}

function DisplayClientMessage(_clientName, _message) {
	if (!_clientName || !_message)
	{
		return false;
	}

	_timeStampText = RetrieveTimestamp();

	$('#chatcontentcontainer').append('<div class="bubblecover_client"><div class="msgwrapper clientbubble">' + _timeStampText + '<span class="clientname">' + htmlspecialchars(_clientName) + ': </span><span class="clientmessage">' + nl2br(htmlspecialchars(_message), false) + '</span></div></div>');

	ScrollDiv();

	return true;
}

function DisplaySystemMessage(_message) {
	$('#chatcontentcontainer').append('<div class="chatsystemmessage">' + _message + '</div>');

	ScrollDiv();

	return true;
}

function PushURL(_url) {
	if (!SWIFT.Library.Browser.isIE) { //I need to prevent popup functionality in IE as allow popup blocker requires page refresh which interrupts chat
		var _openWindow = window.open(_url, 'pushed' + GenerateChatRandomNumber());
	}
	$('#chatcontentcontainer').append('<div class="chaturlmessage">' + '<a href="' + htmlspecialchars(_url) +  '" target="_blank" class="chatlink">' + htmlspecialchars(_url) + '</a></div>');

	ScrollDiv();

	PlaySound();

	return true;
}

function PushImage(_url) {
	/*
	 * BUG FIX - Varun Shoor
	 *
	 * SWIFT-SWIFT-1783 If we send an image using push image from KD, link is showing incorrect path at client end.
	 *
	 * Comments: When sending image from KD it will not make it link if URL is invalid
	 */
	var _urlRegExp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
	var _isValidURL = false;

    if(_urlRegExp.test(_url)){
		_isValidURL = true;
		if (!SWIFT.Library.Browser.isIE) { //I need to prevent popup functionality in IE as allow popup blocker requires page refresh which interrupts chat
			var _openWindow = window.open(_url, 'pushed' + GenerateChatRandomNumber());
		}
	}

	if(_isValidURL)
		$('#chatcontentcontainer').append('<div class="chatimagemessage">' + '<a href="' + htmlspecialchars(_url) +  '" target="_blank" class="chatlink">' + htmlspecialchars(_url) + '</a></div>');
	else
		$('#chatcontentcontainer').append('<div class="chatimagemessage">' + htmlspecialchars(_url) + '</div>');

	ScrollDiv();

	PlaySound();

	return true;
}

function PushUploadedImage(_originalImageURL, _thumbnailImageURL) {
	$('#chatcontentcontainer').append('<div class="chatimagemessage">' + '<div id="imagezoomcontainer"><a target="_blank" class="chatlink" href="' + htmlspecialchars(_originalImageURL) + '"><span></span><img src="' + _thumbnailImageURL + '" width="100" height="100" border="0" align="center" /></a></div></div>');

	//<BR /><a href="' + htmlspecialchars(_originalImageURL) +  '" target="_blank" class="chatlink">' + htmlspecialchars(_originalImageURL) + '</a>

	ScrollDiv();

	PlaySound();

	return true;
}

function PushFile(_fileName, _fileID, _fileHash) {
	$('#chatcontentcontainer').append('<div class="chaturlmessage">' + '<a href="' + swiftpath + 'visitor/index.php?/LiveChat/Chat/GetFile/' + htmlspecialchars(_fileID) + '/' + htmlspecialchars(_fileHash) + '" target="_blank" class="chatlink">' + htmlspecialchars(_fileName) + '</a></div>');

	ScrollDiv();

	PlaySound();

	return true;
}

function PushCode(_codeContents) {
	$('#chatcontentcontainer').append('<div class="chatcodemessage">' + _codeContents + '</div>');

	ScrollDiv();

	PlaySound();

	return true;
}

function ResetAvatar(_staffID) {
	$('#topbanneravatar').attr('src', swiftpath + 'index.php?/Base/Staff/GetProfileImage/' + _staffID);

	ScrollDiv();

	return true;
}

function DisplayUserIsTyping(_name) {
	if (!_name)
	{
		return false;
	}

	if (!_swiftLanguage['istyping'])
	{
		_isTypingContainer = '%s is typing...';
	} else {
		_isTypingContainer = _swiftLanguage['istyping'];
	}

	$('#chatstatusbar').removeClass('chatstatusbarhidden').addClass('chatstatusbar').html(_isTypingContainer.replace('%s', _name));

	return true;
}

function DisplayResetUserIsTyping() {
	$('#chatstatusbar').removeClass('chatstatusbar').addClass('chatstatusbarhidden').html('');

	return true;
}

function ProcessChatStatus(_incomingChatStatus) {
	if (!_incomingChatStatus)
	{
		return false;
	}

	_chatStatus = _incomingChatStatus;

	return true;
}

var _clientIsTyping = false;

function HandlePostEnter(_keyID)
{
	_keyCode = document.layers ? _keyID.which : _keyID.keyCode;

	if (_keyCode != 13)
	{
		_clientIsTyping = true;
	}

	if (_chatStatus != 2 && _keyCode == 13 && document.chatpostform && document.chatpostform.msg)
	{
		// Staff has not accepted chat yet
		if (_swiftLanguage['staffnotacceptedchat'])
		{
			alert(_swiftLanguage['staffnotacceptedchat']);
		}

		document.chatpostform.msg.value = '';
		FocusMessageBox();

		return false;
	} else if (_chatStatus == 2 && _keyCode == 13 && document.chatpostform && document.chatpostform.msg && document.chatpostform.msg.value.replace(/^\s+|\s+$/g,'') != '') {
		ProcessMessage();
		document.chatpostform.msg.value = '';

		return false;
	}

	return true;
}

function HandlePostKeyUp(_keyID)
{
	_keyCode = document.layers ? _keyID.which : _keyID.keyCode;

	if (_keyCode != 13)
	{
		_clientIsTyping = true;
	}

	if (_chatStatus == 2 && _keyCode == 13 && _clientIsTyping == false && document.chatpostform && document.chatpostform.msg && document.chatpostform.msg.value.replace(/^\s+|\s+$/g,"") != "") {
		ProcessMessage();
		document.chatpostform.msg.value = '';

		return false;
	}

	return true;
}

function ScrollDiv() {
	var objDiv = document.getElementById("chatcontentcontainer");
	if (!objDiv)
	{
		return false;
	}

	objDiv.scrollTop = objDiv.scrollHeight;

	return true;
}

function ProcessMessage() {
	if (!document.chatpostform || !document.chatpostform.msg || document.chatpostform.msg.value == '') {
		return false;
	}

	_message = document.chatpostform.msg.value;

	/*
	 * BUG FIX - Varun Shoor
	 *
	 * SWIFT-166 Displaying of Polish characters in QuickSupport Desktop.
	 *
	 * Comments: None
	 */
	LoadXMLHTTPRequest(_swiftMessageURL, 'message=' + encodeURIComponent((_message.replace(/%/g,'%25')).replace(/\+/g,'%2b')), true);

	DisplayClientMessage(_userFullName, _message);

	return true
}

function ProcessLiveChatSubmit() {
	ProcessMessage();
	document.chatpostform.msg.value = '';

	FocusMessageBox();

	return false;
}

var _xmlHTTPObject;
var xmlaction = "";
var xmlsubset = "";

function AjaxErrorAlert(_noAjaxSupport) {
	if (!_noAjaxSupport) {
		alert("XMLHttpRequest::open failed!  This usually occurs due to the URL of your QuickSupport installation being different from the one specified under Admin CP >> Settings >> General.  Due to limitations inherent in AJAX, the product URL needs to be exactly the same as the one specified in the settings; this includes \"www.\" and trailing slashes.");
	} else {
		alert("Your browser does not have the JavaScript support required to use this application. Please make sure that full JavaScript support is enabled in your browser and that your browser is recent and up to date.");
	}
}

function LoadXMLHTTPRequest(url, _parameterContainer, _dontProcessStatusChange, async) {
	var isAsync = async || true;
	if (window.XMLHttpRequest) {
		// Not Internet Explorer
		try {
			_xmlHTTPObject = new XMLHttpRequest();
		} catch (e) {
			// Appears as if Ajax is not even supported.
			AjaxErrorAlert(true);
		}

		if (_xmlHTTPObject) {
			if (!_dontProcessStatusChange)
			{
				_xmlHTTPObject.onreadystatechange = ProcessStatusChange;
			}

			try {
				_xmlHTTPObject.open("POST", url, isAsync);
				_xmlHTTPObject.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				_xmlHTTPObject.send(_parameterContainer);
			} catch (e) {
				AjaxErrorAlert(false);
			}
		}
	} else if (window.ActiveXObject) {
		// Internet Explorer
		try {
			_xmlHTTPObject = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				_xmlHTTPObject = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {
				// Appears as if Ajax is not even supported.
				AjaxErrorAlert(true);
			}
		}

		if (_xmlHTTPObject) {
			if (!_dontProcessStatusChange)
			{
				_xmlHTTPObject.onreadystatechange = ProcessStatusChange;
			}

			try {
				_xmlHTTPObject.open("POST", url, isAsync);
				_xmlHTTPObject.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				_xmlHTTPObject.send(_parameterContainer);
			} catch (e) {
				AjaxErrorAlert(false);
			}
		}
	}
}

var _doClientLoop = true;
function ProcessStatusChange() {
	if (!_swiftRefreshInterval) {
		_refreshInterval = 3000;
	} else {
		_refreshInterval = _swiftRefreshInterval;
	}
	// If the ajax object is in the "completed" state
	if (_xmlHTTPObject.readyState == 4) {
		// Try, because .status can throw an exception
		try {
			// Try to read the status response.
			// Note that this can throw an exception when the connection has been dropped.
			if (_xmlHTTPObject.status == 200) {
				var _xmlResult;
				_xmlResult = _xmlHTTPObject.responseXML;

				if (_xmlHTTPObject.responseText == '1') {
					// Do nothing
				} else if (!_xmlResult) {
					alert("Invalid response received from server: " + _xmlHTTPObject.responseText);
				} else {
					var _chunks = _xmlResult.getElementsByTagName('chunk');
					_isUserTyping = false;

					if (_chunks.length) {
						for (var i = 0; i < _chunks.length; i++) {
							var _chunk = _chunks[i];
							var _chunktype = _chunk.getElementsByTagName('type')[0].firstChild.nodeValue;
							var _chunkGUID = _chunk.attributes.getNamedItem('guid').value;

							if (typeof _chunkGUID != 'undefined' && _chunkGUID != '' && _chunkGUID != '0') {
								_messageGUIDList.push('guid[]=' + escape(_chunkGUID));
							}

							if (_chunktype == 'usertyping') {
								_isUserTyping = true;
							}

							ProcessChatChunk(_chunk);
						}
					}

					if (!_isUserTyping) {
						DisplayResetUserIsTyping();
					}
				}

			} else if (_xmlHTTPObject.status == 12029) {
				// 12029 is unable to establish connection.
				// Reset the timer and bail.
				//setTimeout("ExecuteChatLoopURL();", _refreshInterval);
			}
		} catch (e) {
			// If .status throws, just reset the timer and bail.
			//setTimeout("ExecuteChatLoopURL();", _refreshInterval);
		}
	}
}

function ExecuteChatLoopURL() {
	if (!_swiftChatURL) {
		return false;
	}

	var _guidParameters = _messageGUIDList.join('&');
	_messageGUIDList = new Array();

	_timeHolder = _dateObject.getTime();
	LoadXMLHTTPRequest(_swiftChatURL + "/_chatStatus=" + _chatStatus + "/_isFirstTime=" + _isFirstTime + "/_RandomNumber=" + GenerateChatRandomNumber() + '/_isTyping=' + _clientIsTyping + '/_transfer=' + _transfer , _guidParameters, false);

	_clientIsTyping = false;

	_isFirstTime = 0;
}

var _isUserTyping = false;
function ProcessChatChunk(_chunk) {
	_dateObject = new Date();
	_timeHolder = _dateObject.getTime();

	_chunkType = _chunk.getElementsByTagName('type')[0].firstChild.nodeValue;

	var _shouldResetTyping = false;

	if (_chunkType == 'message')
	{
		_chunkMessage = _chunk.getElementsByTagName('message')[0].firstChild.nodeValue;
		if (_chunkMessage)
		{
			DisplaySystemMessage(_chunkMessage);
		}
	} else if (_chunkType == 'staffmessage') {
		_chunkMessage = _chunk.getElementsByTagName('message')[0].firstChild.nodeValue;
		_chunkName = _chunk.getElementsByTagName('staffname')[0].firstChild.nodeValue;
		if (_chunkMessage && _chunkName)
		{
			DisplayStaffMessage(_chunkName, _chunkMessage);
			_shouldResetTyping = _isUserTyping;
		}
	} else if (_chunkType == 'pushurl') {
		_chunkURL = _chunk.getElementsByTagName('url')[0].firstChild.nodeValue;
		if (_chunkURL)
		{
			PushURL(_chunkURL);
			_shouldResetTyping = _isUserTyping;
		}
	} else if (_chunkType == 'pushimage') {
		_chunkURL = _chunk.getElementsByTagName('url')[0].firstChild.nodeValue;
		if (_chunkURL)
		{
			PushImage(_chunkURL);
			_shouldResetTyping = isUserTyping;
		}
	} else if (_chunkType == 'pushfile') {
		_chunkFileName = _chunk.getElementsByTagName('filename')[0].firstChild.nodeValue;
		_chunkFileID = _chunk.getElementsByTagName('fileid')[0].firstChild.nodeValue;
		_chunkFileHash = _chunk.getElementsByTagName('filehash')[0].firstChild.nodeValue;
		if (_chunkFileName && _chunkFileID && _chunkFileHash)
		{
			PushFile(_chunkFileName, _chunkFileID, _chunkFileHash);
			_shouldResetTyping = isUserTyping;
		}
	} else if (_chunkType == 'uploadedimage' && _chunk.getElementsByTagName('original') && _chunk.getElementsByTagName('thumbnail')) {
		_chunkOriginalImage = _chunk.getElementsByTagName('original')[0].firstChild.nodeValue;
		_chunkThumbnailImage = _chunk.getElementsByTagName('thumbnail')[0].firstChild.nodeValue;
		if (_chunkOriginalImage && _chunkThumbnailImage)
		{
			PushUploadedImage(_chunkOriginalImage, _chunkThumbnailImage);
			_shouldResetTyping = isUserTyping;
		}
	} else if (_chunkType == 'pushcode') {
		_chunkCode = _chunk.getElementsByTagName('code')[0].firstChild.nodeValue;
		if (_chunkCode)
		{
			PushCode(_chunkCode);
			_shouldResetTyping = _isUserTyping;
		}
	} else if (_chunkType == 'usertyping') {
		_chunkName = _chunk.getElementsByTagName('name')[0].firstChild.nodeValue;
		if (_chunkName)
		{
			_isUserTyping = true;
			DisplayUserIsTyping(_chunkName);
		}
	} else if (_chunkType == 'staffaccept') {
		_chunkStaffID = _chunk.getElementsByTagName('staffid')[0].firstChild.nodeValue;
		if (_chunkStaffID)
		{
			ResetAvatar(_chunkStaffID);
		}
	} else if (_chunkType == 'transfer') {
		_transfer = _chunk.getElementsByTagName('status')[0].firstChild.nodeValue;
		if (_transfer == '2') { // Staff has accepted the transfered chat. Let's reset avatar.
			_transferStaffID =  _chunk.getElementsByTagName('staffid')[0].firstChild.nodeValue;
			ResetAvatar(_transferStaffID);
		}
	} else if (_chunkType == 'redirect') {
		_chunkURL = _chunk.getElementsByTagName('url')[0].firstChild.nodeValue;
		if (_chunkURL)
		{
			window.location.href = _chunkURL;
		}
	} else if (_chunkType == 'chatstatus') {
		_chunkStatus = _chunk.getElementsByTagName('status')[0].firstChild.nodeValue;
		if (_chunkStatus)
		{
			_chatInitiated = true;
			// If set to Incoming and new status is In chat
			if (_chatStatus == '1' && _chunkStatus == '2')
			{
				$('#chatpostmsg').removeAttr('disabled').focus().val(' ').val('');
				$('#chattoptoolbarprint').show();
				$('#chattoptoolbaremail').show();
				$('#chattoptoolbarsoundon').show();

			// Chat just ended
			} else if (_chunkStatus == '3') {
				_chatStatus = 3;

				$('#chatpostmsg').attr('disabled', true);

				if (_userClosedWindow)
				{
					window.location.href = _swiftChatSurvey;
				}

				clearInterval(_activeIntervalID);
				_doClientLoop = false;

				// Display chat ended notification
				if (!_chatEndedNotificationDisplayed)
				{
					DisplaySystemMessage(_swiftLanguage['chatendednotification'] + '<BR /><a href="' + _swiftChatSurvey + '" class="chatlink">' + _swiftLanguage['chatendednotificationsub'] + '</a>');

					_chatEndedNotificationDisplayed = true;
				}
			}

			ProcessChatStatus(_chunkStatus);
		}
	}

	if (_shouldResetTyping) {
		_isUserTyping = false;
		DisplayResetUserIsTyping();
	}
}

function htmlspecialchars(p_string) {
	p_string = p_string.replace(/&/g, '&amp;');
	p_string = p_string.replace(/</g, '&lt;');
	p_string = p_string.replace(/>/g, '&gt;');
	p_string = p_string.replace(/"/g, '&quot;');
//	p_string = p_string.replace(/'/g, '&#039;');
	return p_string;
};

function AutoLink(_text) {
	if( !_text ) return _text;

	_text = _text.replace(/((https?\:\/\/|ftp\:\/\/)|(www\.))(\S+)(\w{2,4})(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/gi, function(url){
		nice = url;

		if (url.match('^www')) {
			url = 'http://' + url;
		}

		return '<a target="_blank" rel="nofollow noopener noreferrer" class="chatlink" href="' + url + '">' + nice + '</a>';
	});

	return _text;
}

function EmailChat() {
	$.blockUI({message: $('#sendemailcontainer'), overlayCSS: {
		background: '#d0d0d0 url(' + _themePath + 'images/uigeneralbg.png) 50% 50% repeat',
		filter: 'Alpha(Opacity=50);',
		opacity: .5,
		'z-index': '100000',
		cursor: 'default'
	}, css: {
		padding: '15px',
		'-webkit-border-radius': '10px',
		'-moz-border-radius': '10px',
		border: '1px solid #d0d0d0',
		font: '22px Calibri, Trebuchet MS, Verdana, Arial, Helvetica',
		color: '#666666',
		width: '400px',
		'z-index': '100001',
		'top': ($(window).height() - 162) /2 + 'px',
		'left': ($(window).width() - 420) /2 + 'px',
		cursor: 'default'
	}, forceIframe: false});

	return true;
}

function ValidateChatSendEmailForm() {
	var _emailExpression = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,6}$/;

	if ($('#chatsendemail').val() == '') {
		// Alert!
		$('#chatsendemailinvaliderror').fadeOut('medium');
		$('#chatsendemailerror').fadeIn('medium');

		return false;
	}

	$('#chatsendemailerror').fadeOut('medium');

	var _emailValue = $('#chatsendemail').val();
	if (!_emailValue.match(_emailExpression))
	{
		// Alert
		$('#chatsendemailinvaliderror').fadeIn('medium');
		return false;
	}

	LoadXMLHTTPRequest(_swiftChatEmailURL, 'email=' + escape((_emailValue.replace(/%/g,'%25')).replace(/\+/g,'%2b')), true);

	$.unblockUI();

	return false;
}

function CloseEmailDialog() {
	$.unblockUI();
}

function PlaySound() {
	if (!_isSoundEnabled || !_isSoundPluginLoaded)
	{
		return true;
	}

	$("#soundcontainer").jPlayer('play');

	return true;
}

function SwitchSoundOff() {
	_isSoundEnabled = false;

	$('#chattoptoolbarsoundon').hide();
	$('#chattoptoolbarsoundoff').show();
}

function SwitchSoundOn() {
	_isSoundEnabled = true;

	$('#chattoptoolbarsoundon').show();
	$('#chattoptoolbarsoundoff').hide();
}

function CloseChat() {
	// Disable default 'Changes you made may not be saved' message
	window.onbeforeunload = null;

	if (confirm('Do you really want to end the chat?')) {
		if (_chatStatus === 1) {
			self.close();
		} else {
			LiveChatBeforeUnload();
		}
	}
}

function CloseProactiveChat() {
	_chatStatus = 3;
	LoadXMLHTTPRequest(_swiftChatEndURL, '', true, false);
	clearInterval(_activeIntervalID);
	_globalNoLoop = true;
}

function PrintChat() {
	screen_width = screen.width;
	screen_height = screen.height;
	widthm = (screen_width-400)/2;
	heightm = (screen_height-500)/2;
	window.open(_swiftChatPrintURL, "printwindow", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=500,height=600,left=" + widthm + ",top=" + heightm);
}

$(function() {
	$("#soundcontainer").jPlayer( {
	ready: function () {
		$(this).jPlayer("setMedia", {
			mp3: SWIFT.get('swiftpath') + '__swift/themes/visitor/resources/message.mp3'
		});
                _isSoundPluginLoaded = true;
	},
	swfPath: SWIFT.get('swiftpath') + '__swift/javascript/__global/plugins/jplayer/resources'
	});
});

function SwiftInIframe () {
	try {
		return window.self !== window.top;
	} catch (e) {
		return true;
	}
}

/**
* ###############################################
* BEGIN ON READY FUNCTIONS
* ###############################################
*/
$(function(){
	$('#chatsubject').keyup(function(){
		// Get the limit from maxlength attribute
		var _limit = parseInt($(this).attr('maxlength'));
		// Get the current text inside the textarea
		var _text = $(this).val();
		// Count the number of characters in the text
		var _chars = _text.length;

		// Check if there are more characters then allowed
		if (_limit > 0 &&  _chars > _limit) {
			// and if there are use substr to get the text before the limit
			var _newText = _text.substr(0, _limit);
			// and change the current text with the new text
			$(this).val(_newText);
		}
	});

	/*
	 * Author: Rajat Garg
	 *
	 * SWIFT-1800 : Unsafe JavaScript attempt to access frame with URL (Issue with generated tag for chat if we use it on different domain any other than where our support center is installed)"
	 *
	 * Comments: Cross domain iFrame communication should happen via postMessage.
	 */
	window.onmessage = function(e) {
		if (e.data == 'CloseProactiveChat') {
			CloseProactiveChat();
			e.source.postMessage('CloseProactiveChatInline', '*');
		}
		    };

	$('#chatpostmsg').blur(function() {
		_clientIsTyping = false;
	});

	// Hide close window button if inline chat dialog
	if (SwiftInIframe()) {
		$('#chattoptoolbarcloselink').hide();
	}
});
