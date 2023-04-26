<{if $_isAJAX != true}>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<title><{$_productTitle}> <{$_setupTypeString}></title>

	<script type="text/javascript">
		var _swiftPath = "<{$_swiftPath}>";
	</script>
	<link rel="stylesheet" type="text/css" media="all" href="<{$_baseName|default:index.php?}>/Core/Default/Compressor/css" />
	<script type="text/javascript" src="<{$_baseName|default:index.php?}>/Core/Default/Compressor/js"></script>
	<script language="Javascript" type="text/javascript">
	<{$_jsInitPayload}>
	</script>
	<link rel="icon" href="../favicon.ico" type="image/x-icon" />
	<script type="text/javascript">
	function getWindowHeight()
	{
		var windowHeight=0;
		if (typeof(window.innerHeight)=='number')
		{
			windowHeight = window.innerHeight;
		} else {
			if (document.documentElement && document.documentElement.clientHeight)
			{
				windowHeight = document.documentElement.clientHeight;
			} else {
				if (document.body && document.body.clientHeight)
				{
					windowHeight = document.body.clientHeight;
				}
			}
		}

		return windowHeight;
	}

	function resetContainerHeight()
	{
		$('#paddingcontainer').height((getWindowHeight()-22));
		$('#maincontentsub').height((getWindowHeight()-25));
		$('#contentholder').height((getWindowHeight()-210));
		$('#maincontainer').height((getWindowHeight()-310));
	}

	$(function(){
		resetContainerHeight();

		$('#maincontainer').fadeIn('medium');

		SetAJAXForm();
	});

	function ResetContainerDiv() {
		var objDiv = document.getElementById('maincontainer');
		if (!objDiv)
		{
			return false;
		}

		objDiv.scrollTop = objDiv.scrollHeight;

		return true;
	}

	function SetAJAXForm() {
		$('#hiddenisajax').val(1);
		$('#setupform').ajaxForm({target: '#formcontainer', success: function() { HideBlockUI(); }, beforeSubmit: function(formData) { DisplayBlockUI(); return true; } });
	}

	function DisplayBlockUI() {
		_formSubmitActive = false;
		$(".todisablebutton").attr("disabled","disabled").addClass('rebuttondisabled2').val('<{$_language[scpleasewait]}>');
	}

	function HandleClearDatabase() {
		var _x = confirm('<{$_language[scconfirmcleardb]}>');
		if (!_x) {
			return;
		}

		$('#docleardb').val('1');

		$('#setupform').submit();
	}

	var _formSubmitActive = false;
	function HideBlockUI() {
		SetAJAXForm();
		resetContainerHeight();
		$('#username').focus();

		$("#autosetupbutton").attr("disabled","disabled").addClass('rebuttondisabled2').val('<{$_language[scpleasewait]}>');
		$('#maincontainer').fadeIn('slow', function() {
			if ($('#autosetupbutton').length && !_formSubmitActive) {
				_formSubmitActive = true;
				$('#setupform').submit();
			}
		});

		if (!$('#licenseagreementcontainer').length)
		{
			ResetContainerDiv();
		}
	}

	function DisableButtons() {
		//$("input[type='submit']").attr("disabled","disabled").addClass('rebuttondisabled2');
	}

	$(window).resize( function() { resetContainerHeight(); } );
	</script>
</head>

<body>
<div class="paddingcontainer" id="paddingcontainer">
	<div class="maincontentcontainer">
		<div class="maincontentsub" id="maincontentsub">
			<div class="logoholder">
				<img src="../__swift/themes/setup/images/kayako-logo.svg" alt="" class="space" />
			</div>
			<div id="formcontainer">
<{/if}>
			<!-- BEGIN TOP TOOLBAR CODE -->
			<div class="toolbarcontainer">
			<table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tbody>
			<tr id="toolbar" class="row2" title="">
			<td class="toolbarbg" valign="middle" align="left" colspan="2">
			<div id="toolbarsub">
			<ul>
			<{foreach key=_key item=_item from=$_setupSteps}>
			<{if $_item[type] == 'active'}>
			<li><a onmouseup="javascript: this.blur();" onmousedown="javascript: this.blur();" href="#"><span id="activebutton"><img alt="" src="../__swift/themes/setup/images/icon_blockactive.gif" />&nbsp;<strong><{$_item[name]}></strong></span></a></li>
			<{elseif $_item[type] == 'done'}>
			<li><a onmouseup="javascript: this.blur();" onmousedown="javascript: this.blur();" href="#"><span><img alt="" src="../__swift/themes/setup/images/icon_check.gif" />&nbsp;<{$_item[name]}></span></a></li>
			<{elseif $_item[type] == 'doneactive'}>
			<li><a onmouseup="javascript: this.blur();" onmousedown="javascript: this.blur();" href="#"><span id="activebutton"><img alt="" src="../__swift/themes/setup/images/icon_check.gif" />&nbsp;<{$_item[name]}></span></a></li>
			<{else}>
			<li><a onmouseup="javascript: this.blur();" onmousedown="javascript: this.blur();" href="#"><span><img alt="" src="../__swift/themes/setup/images/icon_block.gif" />&nbsp;<{$_item[name]}></span></a></li>
			<{/if}>
			<{/foreach}>
			</ul>
			</div>
			</td>
			</tr>
			</tbody>
			</table>
			</div>
			<!-- END TOP TOOLBAR CODE -->

			<form id="setupform" method="post" action="index.php?/Core/<{$_setupType}>/StepProcessor">
			<div class="contentholder" id="contentholder">
				<input type="hidden" name="isajax" value="0" id="hiddenisajax" />
				<table border="0" cellpadding="0" cellspacing="0" class="maintable">
				<tr>
				<td class="maintablenavbar" align="left" valign="top">

					<!-- BEGIN NAV BAR -->
					<div class="tabparent">
						<ul class="tab">
							<li>
								<a href="javascript:void(0);" class="currenttab">
								<span class="tabcontainer">
								<strong class="tablicensetext">License Details</strong>
								</span>
								</a>
							</li>
						</ul>
						<div class="tabcontent">
							<table width="100%" cellspacing="1" cellpadding="4" border="0">
							<tbody>
							<tr>
							<td class="row1 tablicenserow" valign="top" align="left">Product:</td>
							<td class="row2 tablicenserow" valign="top" align="left"><{$_productTitle}>
							</td>
							</tr>
							<tr>
							<td class="row1 tablicenserow" valign="top" align="left">Version:</td>
							<td class="row2 tablicenserow" valign="top" align="left"><{$_version}></td>
							</tr>
							<tr>
							<td class="row1 tablicenserow" valign="top" align="left">Build Type:</td>
							<td class="row2 tablicenserow" valign="top" align="left"><{$_buildType}></td>
							</tr>
							<tr>
							<td class="row1 tablicenserow" valign="top" align="left">Source Type:</td>
							<td class="row2 tablicenserow" valign="top" align="left"><{$_sourceType}></td>
							</tr>
							<tr>
							<td class="row1 tablicenserow" valign="top" align="left">Build Date:</td>
							<td class="row2 tablicenserow" valign="top" align="left"><{$_buildDate}></td>
							</tr>
							</tbody>
							</table>
						</div>
					</div>
					<!-- END NAV BAR -->
					<br /><br />
					<br />






					</td>
					<td align="left" valign="top" class="dividercolumn"><img src="../__swift/themes/setup/images/space.gif" height="1" width="8" alt="" /></td>
					<td align="left" valign="top"><div class="paddedcontainermedium" id="paddedcontainermedium">



					<!-- BEGIN NOTICE BOX -->
					<table border="0" cellpadding="0" cellspacing="0" class="maintable">
						<tbody>
							<tr>
								<td>
									<div class="noticeboxcontentcontainer"><{$_setupStep}></div>
									<!-- END NOTICE BOX CONTENTS -->
								</td>

							</tr>
						</tbody>
					</table>
					<!-- END NOTICE BOX -->

					<div id="maincontainer" class="maincontainer">
