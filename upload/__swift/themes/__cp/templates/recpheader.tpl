<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title><{$_defaultTitle}></title>
<meta http-equiv="Content-Type" content="text/html; charset=<{$_language[charset]}>" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<script language="Javascript" type="text/javascript">
var themepath = "<{$_themePath}>";
var swiftpath = "<{$_swiftPath}>";
var _swiftPath = "<{$_swiftPath}>";
var _baseName = "<{$_baseName}>";
var _executeSegment = "<{$_executeSegment}>";
var _staffEmail = "<{$_staffEmail}>";
var swiftsessionid = "<{$_session[sessionid]}>";
var swiftiswinapp = "<{$_session[iswinapp]}>";
var cparea = "<{$_area}>";
var enTinyMCE = false;
var pagetype = 'content';
var finalDocHeight = "<{$_finalHeight}>";
var finalHeightDiff = <{$_finalHeightDifference}>;
var selectedMenu = '<{$_selectedMenu}>';
var isMainHeader = true;
var menuhiddenfieldval = '<{$_menuHiddenFieldValue}>';
var globalImageLoading = false;
var logoutText = '<{$_language[logout]}>: <{$_userName}>';
var strOpConstants = {'OP_CONTAINS':'<{replace $_language[opcontains] "'" "\'"}>', 'OP_NOTCONTAINS':'<{replace $_language[opnotcontains] "'" "\'"}>', 'OP_EQUAL':'<{replace $_language[opequal] "'" "\'"}>', 'OP_NOTEQUAL':'<{replace $_language[opnotequal] "'" "\'"}>', 'OP_GREATER':'<{replace $_language[opgreater] "'" "\'"}>', 'OP_LESS':'<{replace $_language[opless] "'" "\'"}>', 'OP_REGEXP':'<{replace $_language[opregexp] "'" "\'"}>', 'OP_CHANGED':'<{replace $_language[opchanged] "'" "\'"}>', 'OP_CHANGEDTO':'<{replace $_language[opchangedto] "'" "\'"}>', 'OP_CHANGEDFROM':'<{replace $_language[opchangedfrom] "'" "\'"}>', 'OP_NOTCHANGED':'<{replace $_language[opnotchanged] "'" "\'"}>', 'OP_NOTCHANGEDFROM':'<{replace $_language[opnotchangedfrom] "'" "\'"}>', 'OP_NOTCHANGEDTO':'<{replace $_language[opnotchangedto] "'" "\'"}>'};

var swiftLanguage = {'matchand': '<{replace $_language[matchand] "'" "\'"}>', 'pfieldreveal': '<{replace $_language[pfieldreveal] "'" "\'"}>', 'pfieldhide': '<{replace $_language[pfieldhide] "'" "\'"}>', 'matchor': '<{replace $_language[matchor] "'" "\'"}>', 'strue':'<{replace $_language[strue] "'" "\'"}>', 'sfalse':'<{replace $_language[sfalse] "'" "\'"}>', 'name':'<{replace $_language[name] "'" "\'"}>', 'title':'<{replace $_language[title] "'" "\'"}>', 'value':'<{replace $_language[value] "'" "\'"}>', 'engagevisitor':'<{replace $_language[engagevisitor] "'" "\'"}>', 'customengagevisitor':'<{replace $_language[customengagevisitor] "'" "\'"}>', 'inlinechat':'<{replace $_language[inlinechat] "'" "\'"}>', 'url':'<{replace $_language[url] "'" "\'"}>', 'vactionvariables':'<{replace $_language[vactionvariables] "'" "\'"}>', 'vactionvexp':'<{replace $_language[vactionvexp] "'" "\'"}>', 'vactionsalerts':'<{replace $_language[vactionsalerts] "'" "\'"}>', 'open':'<{replace $_language[open] "'" "\'"}>', 'close':'<{replace $_language[close] "'" "\'"}>', 'geoipprocessrunning': '<{replace $_language[geoipprocessrunning] "'" "\'"}>', 'continueprocessquestion': '<{replace $_language[continueprocessquestion] "'" "\'"}>', 'vactionsetdepartment': '<{replace $_language[vactionsetdepartment] "'" "\'"}>', 'vactionsetskill': '<{replace $_language[vactionsetskill] "'" "\'"}>', 'vactionsetcolor': '<{replace $_language[vactionsetcolor] "'" "\'"}>', 'vactionbanvisitor': '<{replace $_language[vactionbanvisitor] "'" "\'"}>', 'vactionsetgroup': '<{replace $_language[vactionsetgroup] "'" "\'"}>', 'hexcode': '<{replace $_language[hexcode] "'" "\'"}>', 'type': '<{replace $_language[type] "'" "\'"}>', 'banip': '<{replace $_language[banip] "'" "\'"}>', 'banclassa': '<{replace $_language[banclassa] "'" "\'"}>', 'banclassb': '<{replace $_language[banclassb] "'" "\'"}>', 'banclassc': '<{replace $_language[banclassc] "'" "\'"}>', 'notificationsubject': '<{replace $_language[notificationsubject] "'" "\'"}>', 'notificationuser': '<{replace $_language[notificationuser] "'" "\'"}>', 'notificationuserorganization': '<{replace $_language[notificationuserorganization] "'" "\'"}>', 'notificationstaff': '<{replace $_language[notificationstaff] "'" "\'"}>', 'notificationteam': '<{replace $_language[notificationteam] "'" "\'"}>', 'notificationdepartment': '<{replace $_language[notificationdepartment] "'" "\'"}>', 'loading': '<{replace $_language[loading] "'" "\'"}>', 'pwtooshort': '<{replace $_language[pwtooshort] "'" "\'"}>', 'pwveryweak': '<{replace $_language[pwveryweak] "'" "\'"}>', 'pwunsafeword': '<{replace $_language[pwunsafeword] "'" "\'"}>', 'pwweak': '<{replace $_language[pwweak] "'" "\'"}>', 'pwmedium': '<{replace $_language[pwmedium] "'" "\'"}>', 'pwstrong': '<{replace $_language[pwstrong] "'" "\'"}>', 'pwverystrong': '<{replace $_language[pwverystrong] "'" "\'"}>', 'cyesterday': '<{replace $_language[cyesterday] "'" "\'"}>', 'ctoday': '<{replace $_language[ctoday] "'" "\'"}>', 'ccurrentwtd': '<{replace $_language[ccurrentwtd] "'" "\'"}>', 'ccurrentmtd': '<{replace $_language[ccurrentmtd] "'" "\'"}>', 'ccurrentytd': '<{replace $_language[ccurrentytd] "'" "\'"}>', 'cl7days': '<{replace $_language[cl7days] "'" "\'"}>', 'cl30days': '<{replace $_language[cl30days] "'" "\'"}>', 'cl90days': '<{replace $_language[cl90days] "'" "\'"}>', 'cl180days': '<{replace $_language[cl180days] "'" "\'"}>', 'cl365days': '<{replace $_language[cl365days] "'" "\'"}>', 'starttypingtags': '<{replace $_language[starttypingtags] "'" "\'"}>', 'edit': '<{replace $_language[edit] "'" "\'"}>', 'insert': '<{replace $_language[insert] "'" "\'"}>', 'ctomorrow': '<{replace $_language[ctomorrow] "'" "\'"}>', 'cnextwfd': '<{replace $_language[cnextwfd] "'" "\'"}>', 'cnextmfd': '<{replace $_language[cnextmfd] "'" "\'"}>', 'cnextyfd': '<{replace $_language[cnextyfd] "'" "\'"}>', 'cn7days': '<{replace $_language[cn7days] "'" "\'"}>', 'cn30days': '<{replace $_language[cn30days] "'" "\'"}>', 'cn90days': '<{replace $_language[cn90days] "'" "\'"}>', 'cn180days': '<{replace $_language[cn180days] "'" "\'"}>', 'cn365days': '<{replace $_language[cn365days] "'" "\'"}>', 'search': '<{replace $_language[search] "'" "\'"}>'};
</script>
<link rel="icon" href="<{$_swiftPath}>favicon.ico" type="image/x-icon">
<link rel="stylesheet" type="text/css" media="all" href="<{$_baseName}>/Core/Default/Compressor/css" />
<script type="text/javascript" src="<{$_baseName}>/Core/Default/Compressor/js"></script>
<script language="Javascript" type="text/javascript">
<{$_jsInitPayload}>
</script>


<{RenderControlPanelMenu area=$_area}>
<script type="text/javascript">
var datePickerDefaults = {showOn: "both", buttonImage: "<{$_themePath}>images/icon_calendar.svg", changeMonth: true, changeYear: true, buttonImageOnly: true, dateFormat: '<{if $_settings[dt_caltype] == 'us'}>mm/dd/yy<{else}>dd/mm/yy<{/if}>'};
</script>
<script type="text/javascript">
if (_executeSegment == true) {
	!function(){var analytics=window.analytics=window.analytics||[];if(!analytics.initialize)if(analytics.invoked)window.console&&console.error&&console.error("Segment snippet included twice.");else{analytics.invoked=!0;analytics.methods=["trackSubmit","trackClick","trackLink","trackForm","pageview","identify","reset","group","track","ready","alias","page","once","off","on"];analytics.factory=function(t){return function(){var e=Array.prototype.slice.call(arguments);e.unshift(t);analytics.push(e);return analytics}};for(var t=0;t<analytics.methods.length;t++){var e=analytics.methods[t];analytics[e]=analytics.factory(e)}analytics.load=function(t){var e=document.createElement("script");e.type="text/javascript";e.async=!0;e.src=("https:"===document.location.protocol?"https://":"http://")+"cdn.segment.com/analytics.js/v1/"+t+"/analytics.min.js";var n=document.getElementsByTagName("script")[0];n.parentNode.insertBefore(e,n)};analytics.SNIPPET_VERSION="3.1.0";
analytics.load("Uqk80GfdovDx5XQSmGVe9JCHWE46rNEa");
analytics.page()
}}();
}
</script>
</head>
<body>
	<{if $_executeGTM == true}>
	<!-- Google Tag Manager -->
	<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-WDWQJT"
					  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-WDWQJT');</script>
	<!-- End Google Tag Manager -->
	<{/if}>

<div class="outermostcontainer">
<div class="mainnav">
	<div class="cptopmenulink">
		<a href="<{$_swiftPath}>" class="menulink" target="_blank"><{$_language[menusupportcenter]}></a><{if $_area != 'staff'}> | <a class="menulink" href="<{$_swiftPath}>staff/index.php" target="_blank"><{$_language[menustaffcp]}></a><{/if}><{if $_area == 'staff' && $_staffIsAdmin == true}> | <a class="menulink" href="<{$_swiftPath}>admin/index.php" target="_blank"><{$_language[menuadmincp]}></a><{/if}> <div id="menulinkwindow">| <a href='#' id='menulinkholder' target='_blank'><i title="Open this page in a new window" title="Open this page in a new window" class="fa fa-expand" aria-hidden="true" ></i></a></div>
	</div>

	<div class="rebarlogo">
		<a href="<{$_baseName}>/Base/Home/Index"><img src="<{$_headerImageCP}>" border="0" /></a>
	</div>

<script language="Javascript" type="text/javascript">
var swmenubg1 = "menudefbg";
var swmenubg2 = "remenusectiondefault";
var swtabmenutype = "<{if $_controlPanelMenu == "hover"}>onMouseOver<{else}>onClick<{/if}>";
var swtabmenu = []; var swtabmenucolspan = '<{$_menuColumnSpan}>'; var swtabselmenu = '<{$_selectedMenu}>'; var swtabselmenuclass = '<{$_selectedMenuClass}>';
swtabmenu = [<{foreach key=key item=_item from=$_menu}>['<{$key}>', '<{$_item[1]}>', '<{$_item[4]}>', '<{$_item[0]}>'],<{/foreach}>];
buildTopTabMenu();
</script>

</div>

<script language="Javascript" type="text/javascript">
switchTab(<{$_selectedMenu}>, <{$_selectedMenuClass}>);
</script>
<{$_extendedRefreshScript}>
<table border="0" cellspacing="0" cellpadding="0" width="100%" class="maincontent">
