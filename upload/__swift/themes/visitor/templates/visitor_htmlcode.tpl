//===============================
// QuickSupport LiveResponse
// Copyright (c) 2001-<{$_currentYear}>

// http://www.kayako.com
// License: http://www.kayako.com/license.txt
//===============================

<{if $_isBanned != 1}>
var sessionid_<{$_randomSuffix}> = "<{$_sessionID}>";
var geoip_<{$_randomSuffix}> = new Array();
<{foreach key=itemkey item=_item from=$_geoIP}>
geoip_<{$_randomSuffix}>[<{$itemkey}>] = "<{$_item}>";
<{/foreach}>
var hasnotes_<{$_randomSuffix}> = "<{$_hasNotes}>";
var isnewsession_<{$_randomSuffix}> = "<{$_isNewSession}>";
var repeatvisit_<{$_randomSuffix}> = "<{$_repeatVisit}>";
var lastvisittimeline_<{$_randomSuffix}> = "<{$_lastVisitTimeline}>";
var lastchattimeline_<{$_randomSuffix}> = "<{$_lastChatTimeline}>";
var isfirsttime_<{$_randomSuffix}> = 1;
var timer_<{$_randomSuffix}> = 0;
var imagefetch_<{$_randomSuffix}> = <{$_clientRefresh}>;
var imagefetchincr_<{$_randomSuffix}> = 10;
var imagefetchincrcount_<{$_randomSuffix}> = 0;
var updateurl_<{$_randomSuffix}> = "";
var screenHeight_<{$_randomSuffix}> = window.screen.availHeight;
var screenWidth_<{$_randomSuffix}> = window.screen.availWidth;
var colorDepth_<{$_randomSuffix}> = window.screen.colorDepth;
var timeNow = new Date();
var referrer = escape(document.referrer);
var windows_<{$_randomSuffix}>, mac_<{$_randomSuffix}>, linux_<{$_randomSuffix}>;
var ie_<{$_randomSuffix}>, op_<{$_randomSuffix}>, moz_<{$_randomSuffix}>, misc_<{$_randomSuffix}>, browsercode_<{$_randomSuffix}>, browsername_<{$_randomSuffix}>, browserversion_<{$_randomSuffix}>, operatingsys_<{$_randomSuffix}>;
var dom_<{$_randomSuffix}>, ienew, ie4_<{$_randomSuffix}>, ie5_<{$_randomSuffix}>, ie6_<{$_randomSuffix}>, ie7_<{$_randomSuffix}>, ie8_<{$_randomSuffix}>, moz_rv_<{$_randomSuffix}>, moz_rv_sub_<{$_randomSuffix}>, ie5mac, ie5xwin, opnu_<{$_randomSuffix}>, op4, op5_<{$_randomSuffix}>, op6_<{$_randomSuffix}>, op7_<{$_randomSuffix}>, op8_<{$_randomSuffix}>, op9_<{$_randomSuffix}>, op10_<{$_randomSuffix}>, saf_<{$_randomSuffix}>, konq_<{$_randomSuffix}>, chrome_<{$_randomSuffix}>, ch1_<{$_randomSuffix}>, ch2_<{$_randomSuffix}>, ch3_<{$_randomSuffix}>;
var appName_<{$_randomSuffix}>, appVersion_<{$_randomSuffix}>, userAgent_<{$_randomSuffix}>;
var appName_<{$_randomSuffix}> = navigator.appName;
var appVersion_<{$_randomSuffix}> = navigator.appVersion;
var userAgent_<{$_randomSuffix}> = navigator.userAgent;
var dombrowser = "default";
var isChatRunning_<{$_randomSuffix}> = 0;
var title = document.title;
var proactiveImageUse_<{$_randomSuffix}> = new Image();
windows_<{$_randomSuffix}> = (appVersion_<{$_randomSuffix}>.indexOf('Win') != -1);
mac_<{$_randomSuffix}> = (appVersion_<{$_randomSuffix}>.indexOf('Mac') != -1);
linux_<{$_randomSuffix}> = (appVersion_<{$_randomSuffix}>.indexOf('Linux') != -1);
if (!document.layers) {
	dom_<{$_randomSuffix}> = (document.getElementById ) ? document.getElementById : false;
} else {
	dom_<{$_randomSuffix}> = false;
}
var myWidth = 0, myHeight = 0;
if( typeof( window.innerWidth ) == 'number' ) {
	//Non-IE
	myWidth = window.innerWidth;
	myHeight = window.innerHeight;
} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
	//IE 6+ in 'standards compliant mode'
	myWidth = document.documentElement.clientWidth;
	myHeight = document.documentElement.clientHeight;
} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
	//IE 4 compatible
	myWidth = document.body.clientWidth;
	myHeight = document.body.clientHeight;
}
winH = myHeight;
winW = myWidth;
misc_<{$_randomSuffix}> = (appVersion_<{$_randomSuffix}>.substring(0,1) < 4);
op_<{$_randomSuffix}> = (userAgent_<{$_randomSuffix}>.indexOf('Opera') != -1);
moz_<{$_randomSuffix}> = (userAgent_<{$_randomSuffix}>.indexOf('Gecko') != -1);
chrome_<{$_randomSuffix}>=(userAgent_<{$_randomSuffix}>.indexOf('Chrome') != -1);
if (document.all) {
	ie_<{$_randomSuffix}> = (document.all && !op_<{$_randomSuffix}>);
}
saf_<{$_randomSuffix}>=(userAgent_<{$_randomSuffix}>.indexOf('Safari') != -1);
konq_<{$_randomSuffix}>=(userAgent_<{$_randomSuffix}>.indexOf('Konqueror') != -1);

if (op_<{$_randomSuffix}>) {
	op_pos = userAgent_<{$_randomSuffix}>.indexOf('Opera');
	opnu_<{$_randomSuffix}> = userAgent_<{$_randomSuffix}>.substr((op_pos+6),4);
	op5_<{$_randomSuffix}> = (opnu_<{$_randomSuffix}>.substring(0,1) == 5);
	op6_<{$_randomSuffix}> = (opnu_<{$_randomSuffix}>.substring(0,1) == 6);
	op7_<{$_randomSuffix}> = (opnu_<{$_randomSuffix}>.substring(0,1) == 7);
	op8_<{$_randomSuffix}> = (opnu_<{$_randomSuffix}>.substring(0,1) == 8);
	op9_<{$_randomSuffix}> = (opnu_<{$_randomSuffix}>.substring(0,1) == 9);
	op10_<{$_randomSuffix}> = (opnu_<{$_randomSuffix}>.substring(0,2) == 10);
} else if (chrome_<{$_randomSuffix}>) {
	chrome_pos = userAgent_<{$_randomSuffix}>.indexOf('Chrome');
	chnu = userAgent_<{$_randomSuffix}>.substr((chrome_pos+7),4);
	ch1_<{$_randomSuffix}> = (chnu.substring(0,1) == 1);
	ch2_<{$_randomSuffix}> = (chnu.substring(0,1) == 2);
	ch3_<{$_randomSuffix}> = (chnu.substring(0,1) == 3);
} else if (moz_<{$_randomSuffix}>){
	rv_pos = userAgent_<{$_randomSuffix}>.indexOf('rv');
	moz_rv_<{$_randomSuffix}> = userAgent_<{$_randomSuffix}>.substr((rv_pos+3),3);
	moz_rv_sub_<{$_randomSuffix}> = userAgent_<{$_randomSuffix}>.substr((rv_pos+7),1);
	if (moz_rv_sub_<{$_randomSuffix}> == ' ' || isNaN(moz_rv_sub_<{$_randomSuffix}>)) {
		moz_rv_sub_<{$_randomSuffix}>='';
	}
	moz_rv_<{$_randomSuffix}> = moz_rv_<{$_randomSuffix}> + moz_rv_sub_<{$_randomSuffix}>;
} else if (ie_<{$_randomSuffix}>){
	ie_pos = userAgent_<{$_randomSuffix}>.indexOf('MSIE');
	ienu = userAgent_<{$_randomSuffix}>.substr((ie_pos+5),3);
	ie4_<{$_randomSuffix}> = (!dom_<{$_randomSuffix}>);
	ie5_<{$_randomSuffix}> = (ienu.substring(0,1) == 5);
	ie6_<{$_randomSuffix}> = (ienu.substring(0,1) == 6);
	ie7_<{$_randomSuffix}> = (ienu.substring(0,1) == 7);
	ie8_<{$_randomSuffix}> = (ienu.substring(0,1) == 8);
}

if (konq_<{$_randomSuffix}>) {
	browsercode_<{$_randomSuffix}> = "KO";
	browserversion_<{$_randomSuffix}> = appVersion_<{$_randomSuffix}>;
	browsername_<{$_randomSuffix}> = "Konqueror";
} else if (chrome_<{$_randomSuffix}>) {
	browsercode_<{$_randomSuffix}> = "CH";
	if (ch1_<{$_randomSuffix}>) {
		browserversion_<{$_randomSuffix}> = "1";
	} else if (ch2_<{$_randomSuffix}>) {
		browserversion_<{$_randomSuffix}> = "2";
	} else if (ch3_<{$_randomSuffix}>) {
		browserversion_<{$_randomSuffix}> = "3";
	}

	browsername_<{$_randomSuffix}> = "Google Chrome";
} else if (saf_<{$_randomSuffix}>) {
	browsercode_<{$_randomSuffix}> = "SF";
	browserversion_<{$_randomSuffix}> = appVersion_<{$_randomSuffix}>;
	browsername_<{$_randomSuffix}> = "Safari";
} else if (op_<{$_randomSuffix}>) {
	browsercode_<{$_randomSuffix}> = "OP";
	if (op5_<{$_randomSuffix}>) {
		browserversion_<{$_randomSuffix}> = "5";
	} else if (op6_<{$_randomSuffix}>) {
		browserversion_<{$_randomSuffix}> = "6";
	} else if (op7_<{$_randomSuffix}>) {
		browserversion_<{$_randomSuffix}> = "7";
	} else if (op8_<{$_randomSuffix}>) {
		browserversion_<{$_randomSuffix}> = "8";
	} else if (op9_<{$_randomSuffix}>) {
		browserversion_<{$_randomSuffix}> = "9";
	} else if (op10_<{$_randomSuffix}>) {
		browserversion_<{$_randomSuffix}> = "10";
	} else {
		browserversion_<{$_randomSuffix}> = appVersion_<{$_randomSuffix}>;
	}
	browsername_<{$_randomSuffix}> = "Opera";
} else if (moz_<{$_randomSuffix}>) {
	browsercode_<{$_randomSuffix}> = "MO";
	browserversion_<{$_randomSuffix}> = appVersion_<{$_randomSuffix}>;
	browsername_<{$_randomSuffix}> = "Firefox";
} else if (ie_<{$_randomSuffix}>) {
	browsercode_<{$_randomSuffix}> = "IE";
	if (ie4_<{$_randomSuffix}>) {
		browserversion_<{$_randomSuffix}> = "4";
	} else if (ie5_<{$_randomSuffix}>) {
		browserversion_<{$_randomSuffix}> = "5";
	} else if (ie6_<{$_randomSuffix}>) {
		browserversion_<{$_randomSuffix}> = "6";
	} else if (ie7_<{$_randomSuffix}>) {
		browserversion_<{$_randomSuffix}> = "7";
	} else if (ie8_<{$_randomSuffix}>) {
		browserversion_<{$_randomSuffix}> = "8";
	} else {
		browserversion_<{$_randomSuffix}> = appVersion_<{$_randomSuffix}>;
	}
	browsername_<{$_randomSuffix}> = "Internet Explorer";
}

if (windows_<{$_randomSuffix}>) {
	operatingsys_<{$_randomSuffix}> = "Windows";
} else if (linux_<{$_randomSuffix}>) {
	operatingsys_<{$_randomSuffix}> = "Linux";
} else if (mac_<{$_randomSuffix}>) {
	operatingsys_<{$_randomSuffix}> = "Mac";
} else {
	operatingsys_<{$_randomSuffix}> = "Unkown";
}

if (document.getElementById)
{
	dombrowser = "default";
} else if (document.layers) {
	dombrowser = "NS4";
} else if (document.all) {
	dombrowser = "IE4";
}

var proactiveX = 20;
var proactiveXStep = 1;
var proactiveDelayTime = 100;

var proactiveY = 0;
var proactiveOffsetHeight=0;
var proactiveYStep = 0;
var proactiveAnimate = false;

function browserObject_<{$_randomSuffix}>(objid)
{
	if (dombrowser == "default")
	{
		return document.getElementById(objid);
	} else if (dombrowser == "NS4") {
		return document.layers[objid];
	} else if (dombrowser == "IE4") {
		return document.all[objid];
	}
}

function doRand_<{$_randomSuffix}>()
{
	var num;
	now=new Date();
	num=(now.getSeconds());
	num=num+1;
	return num;
}

function getCookie_<{$_randomSuffix}>(name) {
	var crumb = document.cookie;
	var index = crumb.indexOf(name + "=");
	if (index == -1) return null;
	index = crumb.indexOf("=", index) + 1;
	var endstr = crumb.indexOf(";", index);
	if (endstr == -1) endstr = crumb.length;
	return unescape(crumb.substring(index, endstr));
}

function deleteCookie_<{$_randomSuffix}>(name) {
	var expiry = new Date();
	document.cookie = name + "=" + "; expires=Thu, 01-Jan-70 00:00:01 GMT" +  "; path=/";
}

function elapsedTime_<{$_randomSuffix}>()
{
	if (typeof _elapsedTimeStatusIndicator == 'undefined') {
		_elapsedTimeStatusIndicator = '<{$_randomSuffix}>';
	} else if (typeof _elapsedTimeStatusIndicator == 'string' && _elapsedTimeStatusIndicator != '<{$_randomSuffix}>') {

		return;
	}


	if (timer_<{$_randomSuffix}> < 3600)
	{
		timer_<{$_randomSuffix}>++;
		imagefetch_<{$_randomSuffix}>++;

		if (imagefetch_<{$_randomSuffix}> > (<{$_clientRefresh}> + (imagefetchincr_<{$_randomSuffix}> * imagefetchincrcount_<{$_randomSuffix}>))) {
			imagefetch_<{$_randomSuffix}> = 0;
			imagefetchincrcount_<{$_randomSuffix}>++;
			doStatusLoop_<{$_randomSuffix}>();
		}

		<{if $_insertFootprintAndLeave == false}>
			setTimeout("elapsedTime_<{$_randomSuffix}>();", 1000);
		<{/if}>
	}
}


var Base64_<{$_randomSuffix}> = {
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
	encode : function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;

		input = Base64_<{$_randomSuffix}>._utf8_encode(input);

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
	}
}

function doStatusLoop_<{$_randomSuffix}>() {
	date1 = new Date();
	var _finalPageTitle=Base64_<{$_randomSuffix}>.encode(title);

	var _finalWindowLocation = encodeURIComponent(decodeURIComponent(window.location));
	var _referrerURL = encodeURIComponent(decodeURIComponent(document.referrer));
	updateurl_<{$_randomSuffix}> = "<{$_swiftPath}>visitor/index.php?<{$_templateGroupPrefix}>/LiveChat/VisitorUpdate/UpdateFootprint/_time="+date1.getTime()+"/_randomNumber="+doRand_<{$_randomSuffix}>()+"/_url="+_finalWindowLocation+"/_isFirstTime="+encodeURIComponent(isfirsttime_<{$_randomSuffix}>)+"/_sessionID="+encodeURIComponent(sessionid_<{$_randomSuffix}>)+"/_referrer="+_referrerURL+"/_resolution="+encodeURIComponent(screenWidth_<{$_randomSuffix}>+"x"+screenHeight_<{$_randomSuffix}>)+"/_colorDepth="+encodeURIComponent(colorDepth_<{$_randomSuffix}>)+"/_platform="+encodeURIComponent(navigator.platform)+"/_appVersion="+encodeURIComponent(navigator.appVersion)+"/_appName="+encodeURIComponent(navigator.appName)+"/_browserCode="+encodeURIComponent(browsercode_<{$_randomSuffix}>)+"/_browserVersion="+encodeURIComponent(browserversion_<{$_randomSuffix}>)+"/_browserName="+encodeURIComponent(browsername_<{$_randomSuffix}>)+"/_operatingSys="+encodeURIComponent(operatingsys_<{$_randomSuffix}>)+"/_pageTitle="+encodeURIComponent(_finalPageTitle)+"/_hasNotes="+encodeURIComponent(hasnotes_<{$_randomSuffix}>)+"/_repeatVisit="+encodeURIComponent(repeatvisit_<{$_randomSuffix}>)+"/_lastVisitTimeline="+encodeURIComponent(lastvisittimeline_<{$_randomSuffix}>)+"/_lastChatTimeline="+encodeURIComponent(lastchattimeline_<{$_randomSuffix}>)+"/_isNewSession="+encodeURIComponent(isnewsession_<{$_randomSuffix}>)<{$_geoIPURL}>;

	proactiveImageUse_<{$_randomSuffix}> = new Image();
	proactiveImageUse_<{$_randomSuffix}>.onload = imageLoaded_<{$_randomSuffix}>;
	proactiveImageUse_<{$_randomSuffix}>.src = updateurl_<{$_randomSuffix}>;

	isfirsttime_<{$_randomSuffix}> = 0;
}

function startChat_<{$_randomSuffix}>(proactive)
{
	isChatRunning_<{$_randomSuffix}> = 1;

	docWidth = (winW-<{$_chatWidth}>)/2;
	docHeight = (winH-<{$_chatHeight}>)/2;

	<{if $_isInlineRequest == true}>
	_chatWindowURL = '<{$_swiftPath}>visitor/index.php?<{$_templateGroupPrefix}>/LiveChat/Chat/StartInline/_sessionID=<{$_sessionID}>/_proactive=0/_filterDepartmentID=<{$_filterDepartmentID}>/_fullName=/_email=/_inline=0/';
	<{else}>
	_chatWindowURL = '<{$_swiftPath}>visitor/index.php?<{$_templateGroupPrefix}>/LiveChat/Chat/Request/_sessionID=' + sessionid_<{$_randomSuffix}> + '/_proactive=' + proactive + '/_filterDepartmentID=<{$_filterDepartmentID}>/_randomNumber=' + doRand_<{$_randomSuffix}>() + '/_fullName=<{$_fullName}>/_email=<{$_email}>/_promptType=<{$_promptType}>';
	<{/if}>


	chatwindow = window.open(_chatWindowURL,"customerchat"+doRand_<{$_randomSuffix}>(), "toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=yes,resizable=1,width=<{$_chatWidth}>,height=<{$_chatHeight}>,left="+docWidth+",top="+docHeight);

	hideProactiveChatData_<{$_randomSuffix}>();
}

function imageLoaded_<{$_randomSuffix}>() {
	if (!proactiveImageUse_<{$_randomSuffix}>)
	{
		return;
	}
	proactiveAction = proactiveImageUse_<{$_randomSuffix}>.width;

	if (proactiveAction == 3)
	{
		doProactiveInline_<{$_randomSuffix}>();
	} else if (proactiveAction == 4) {
		displayProactiveChatData_<{$_randomSuffix}>();
	}
}

function writeInlineRequestData_<{$_randomSuffix}>()
{
	docWidth = (winW-<{$_settings[livesupport_chatwidth]}>)/2;
	docHeight = (winH-<{$_settings[livesupport_chatheight]}>)/2;

	var divData = '';
	<{$_inlineChatData}>

	var inlineChatElement = document.createElement("div");
	inlineChatElement.style.position = 'absolute';
	inlineChatElement.style.display = 'none';
	inlineChatElement.style.float = 'left';
	inlineChatElement.style.top = docHeight+'px';
	inlineChatElement.style.left = docWidth+'px';
	inlineChatElement.style.zIndex = 500;

	if (inlineChatElement.style.overflow) {
		inlineChatElement.style.overflow = 'none';
	}

	inlineChatElement.id = 'inlinechatdiv';
	inlineChatElement.innerHTML = divData;

	var proactiveChatContainer = document.getElementById('proactivechatcontainer' + swiftuniqueid);
	proactiveChatContainer.appendChild(inlineChatElement);
}

function writeProactiveRequestData_<{$_randomSuffix}>()
{
	docWidth = (winW-450)/2;
	docHeight = (winH-400)/2;

	var divData = '';
	<{$_proactiveChatData}>

	var proactiveElement = document.createElement("div");
	proactiveElement.style.position = 'absolute';
	proactiveElement.style.display = 'none';
	proactiveElement.style.float = 'left';
	proactiveElement.style.top = docHeight+'px';
	proactiveElement.style.left = docWidth+'px';
	proactiveElement.style.zIndex = 500;

	if (proactiveElement.style.overflow) {
		proactiveElement.style.overflow = 'none';
	}

	proactiveElement.id = 'proactivechatdiv';
	proactiveElement.innerHTML = divData;

	var proactiveChatContainer = document.getElementById('proactivechatcontainer' + swiftuniqueid);
	proactiveChatContainer.appendChild(proactiveElement);
}

function displayProactiveChatData_<{$_randomSuffix}>()
{
	if (proactiveAnimate == true) {
		return false;
	}

	writeObj = browserObject_<{$_randomSuffix}>("proactivechatdiv");
	if (writeObj)
	{
		docWidth = (winW-450)/2;
		docHeight = (winH-400)/2;
		proactiveY = docHeight;
		writeObj.top = docWidth;
		writeObj.left = docHeight;
		proactiveAnimate = true;
	}

	showDisplay_<{$_randomSuffix}>("proactivechatdiv");

	<{if $_settings[livechat_proactivescroll] == '1'}>
	animateProactiveDiv_<{$_randomSuffix}>();
	<{/if}>
}

function displayInlineChatData_<{$_randomSuffix}>()
{
	writeObj = browserObject_<{$_randomSuffix}>("inlinechatdiv");
	if (writeObj)
	{
		docWidth = (winW-<{$_settings[livesupport_chatwidth]}>)/2;
		docHeight = (winH-<{$_settings[livesupport_chatheight]}>)/2;
		proactiveY = docHeight;
		writeObj.top = docHeight;
		writeObj.left = docWidth;

		acceptProactive = new Image();
		acceptProactive.src = "<{$_swiftPath}>visitor/index.php?<{$_templateGroupPrefix}>/LiveChat/VisitorUpdate/AcceptProactive/_randomNumber="+doRand_<{$_randomSuffix}>()+"/_sessionID="+sessionid_<{$_randomSuffix}>;

		inlineChatFrameObj = browserObject_<{$_randomSuffix}>("inlinechatframe");
		_iframeURL = '<{$_swiftPath}>visitor/index.php?<{$_templateGroupPrefix}>/LiveChat/Chat/StartInline/_sessionID=<{$_sessionID}>/_proactive=1/_filterDepartmentID=<{$_filterDepartmentID}>/_fullName=/_email=/_inline=1/';
		if (inlineChatFrameObj && inlineChatFrameObj.src != _iframeURL && writeObj.style.display == 'none') {
			inlineChatFrameObj.src = _iframeURL;
		}
	}

	showDisplay_<{$_randomSuffix}>("inlinechatdiv");
}

function hideProactiveChatData_<{$_randomSuffix}>()
{
	hideDisplay_<{$_randomSuffix}>("proactivechatdiv");
	hideDisplay_<{$_randomSuffix}>("inlinechatdiv");
}

function doProactiveInline_<{$_randomSuffix}>()
{
	displayInlineChatData_<{$_randomSuffix}>();
}

function doProactiveRequest_<{$_randomSuffix}>()
{
	acceptProactive = new Image();
	acceptProactive.src = "<{$_swiftPath}>visitor/index.php?<{$_templateGroupPrefix}>/LiveChat/VisitorUpdate/AcceptProactive/_randomNumber="+doRand_<{$_randomSuffix}>()+"/_sessionID="+sessionid_<{$_randomSuffix}>;

	startChat_<{$_randomSuffix}>("<{$_visitorEngage}>");
}

function closeProactiveRequest_<{$_randomSuffix}>()
{
	rejectProactive = new Image();
	date1 = new Date();
	proactiveAnimate = false;
	rejectProactive.src = "<{$_swiftPath}>visitor/index.php?<{$_templateGroupPrefix}>/LiveChat/VisitorUpdate/ResetProactive/_time="+date1.getTime()+"/_randomNumber="+doRand_<{$_randomSuffix}>()+"/_sessionID="+sessionid_<{$_randomSuffix}>;

	hideProactiveChatData_<{$_randomSuffix}>();
}

function closeInlineProactiveRequest_<{$_randomSuffix}>()
{
	rejectProactive = new Image();
	date1 = new Date();
	rejectProactive.src = "<{$_swiftPath}>visitor/index.php?<{$_templateGroupPrefix}>/LiveChat/VisitorUpdate/ResetProactive/_time="+date1.getTime()+"/_randomNumber="+doRand_<{$_randomSuffix}>()+"/_sessionID="+sessionid_<{$_randomSuffix}>;

	document.getElementById('inlinechatframe').contentWindow.postMessage('CloseProactiveChat', '*');
	//	window.frames.inlinechatframe.CloseProactiveChat();
}

function closeInlineProactiveRequest2_<{$_randomSuffix}>()
{
	var bodyElement = document.getElementsByTagName('body');
	if (bodyElement[0])
	{
		var inlineDivElement = browserObject_<{$_randomSuffix}>('inlinechatdiv');
		if (inlineDivElement) {
			var _parentNode = inlineDivElement.parentNode;
			_parentNode.removeChild(inlineDivElement);
		}
	}
}

	window.onmessage = function(e){
	if (e.data == 'CloseProactiveChatInline') {
	closeInlineProactiveRequest2_<{$_randomSuffix}>();
	}
	};

function switchDisplay_<{$_randomSuffix}>(objid)
{
	result = browserObject_<{$_randomSuffix}>(objid);
	if (!result)
	{
		return;
	}

	if (result.style.display == "none")
	{
		result.style.display = "block";
	} else {
		result.style.display = "none";
	}
}

function hideDisplay_<{$_randomSuffix}>(objid)
{
	result = browserObject_<{$_randomSuffix}>(objid);
	if (!result)
	{
		return;
	}

	result.style.display = "none";
}

function showDisplay_<{$_randomSuffix}>(objid)
{
	result = browserObject_<{$_randomSuffix}>(objid);
	if (!result)
	{
		return;
	}

	result.style.display = "block";
}

function updateProactivePosition_<{$_randomSuffix}>()
{
	writeObj = browserObject_<{$_randomSuffix}>("proactivechatdiv");
	writeObjInline = browserObject_<{$_randomSuffix}>("inlinechatdiv");

	docHeight = (winH-412)/2;
	docHeightInline = (winH-<{$_settings[livesupport_chatheight]}>)/2;

	finalTopValue = docHeight + document.body.scrollTop;
	if (finalTopValue < 0) {
		finalTopValue = 10;
	}

	finalTopValueInline = docHeightInline + document.body.scrollTop;
	if (finalTopValueInline < 0) {
		finalTopValueInline = 10;
	}

	if (writeObj) {
		writeObj.style.top = finalTopValue + "px";
	}

	if (writeObjInline) {
		writeObjInline.style.top = finalTopValueInline + "px";
	}
}

function animateProactiveDiv_<{$_randomSuffix}>()
{
	writeObj = browserObject_<{$_randomSuffix}>("proactivechatdiv");

	if (!writeObj) {
		return false;
	}

	if(proactiveYStep == 0){proactiveY = proactiveY-proactiveXStep;} else {proactiveY = proactiveY+proactiveXStep;}

	proactiveOffsetHeight = writeObj.offsetHeight;
	if(proactiveY < 0){proactiveYStep = 1; proactiveY=0; }
	if(proactiveY >= (myHeight - proactiveOffsetHeight)){proactiveYStep=0; proactiveY=(myHeight-proactiveOffsetHeight);}

	finalTopValue = proactiveY+document.body.scrollTop;
	if (finalTopValue < 0) {
		finalTopValue = 10;
	}

	writeObj.style.top = finalTopValue+"px";

	if (proactiveAnimate) {
		setTimeout('animateProactiveDiv_<{$_randomSuffix}>()', proactiveDelayTime);
	}
}

<{if $_insertFootprintAndLeave == false}>
	writeProactiveRequestData_<{$_randomSuffix}>(); writeInlineRequestData_<{$_randomSuffix}>();
<{/if}>

<{if $_staffStatus == 'online'}>
	elapsedTime_<{$_randomSuffix}>();
<{/if}>

var oldEvtScroll = window.onscroll; window.onscroll = function() { if (oldEvtScroll) { updateProactivePosition_<{$_randomSuffix}>(); } }

<{/if}>