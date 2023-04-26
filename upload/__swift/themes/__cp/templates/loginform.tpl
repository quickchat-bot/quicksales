<{include file="header.tpl"}>
<body>
<div class="loginformcontainer">
	<!-- BEGIN DIALOG PROCESSING -->
	<div id="" class="dialogcontainer">
	<{foreach key=key item=_item from=$_errorContainer}>
		<div class="dialogerrorcontainer">
			<div class="dialogtitle"><{$_item[title]}></div>
			<div class="dialogtext"><{$_item[message]}></div>
		</div>
	<{/foreach}>
	<{foreach key=key item=_item from=$_infoContainer}>
		<div class="dialogokcontainer">
			<div class="dialogtitle"><{$_item[title]}></div>
			<div class="dialogtext"><{$_item[message]}></div>
		</div>
	<{/foreach}>
	</div>
<center>
<script language="Javascript" type="text/javascript">
$(function(){
	$('#username').focus();
	$('#newpassword').pstrength();
	$('#newpasswordagain').pstrength();
});
</script>
<form name="loginform" action="<{$_baseName}>/Core/Default/Login" method="post">
<table width="400" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td colspan="2" align="center" valign="top"><img class="loginlogo" src="<{$_themePath}>images/kayako-logo-dark.svg" /></td>
  </tr>
  <tr>
    <td colspan="2">
	<div class="loginformparent">
		<div class="loginformsub">
		
			<table width="100%"  border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td>
						<table width="100%"  border="0" cellspacing="0" cellpadding="0">
							<tr class="gridrow1">
			
								<td align="left">
								
								<p><{$_language[username]}><br />
								<input type="text" name="username" id="username" class="logintext" value="<{$_userName}>" size="25"  /></p>
								
								<p><{$_language[password]}><br />
								<input type="password" name="password" id="password" class="loginpassword" value="<{$_password}>"  size="25" /></p>
								
								</td>
							</tr>
							
							<{if $_errorString != ""}>
							<tr class="rowerror" title="" onmouseover="" onmouseout="" onclick="">
								<td align="center" colspan="2"><{$_errorString}></td>
							</tr>
							<{/if}>
							
							<{if $_passwordExpired == true}>
							<tr class="gridrow1">
								<td align="left">
									<p><{$_language[newpassword]}>:<br />
									<input type="password" name="newpassword" id="newpassword" class="loginpassword" size="25" /></p>
									
									<p><{$_language[passwordagain]}><br />
									<input type="password" name="newpasswordagain" id="newpasswordagain" class="loginpassword" size="25" /></p>
								</td>
							</tr>
							<tr class="gridrow1">
								<td align="left" valign="top" colspan="2"><{$_passwordExpiredMessage}></td>
							</tr>
							<{/if}>
							
							
							<tr class="loginsubmit">
								<td>
									<input type="submit" name="submitbutton" class="rebutton" value="<{$_language[login]}>" onfocus="blur();" />
									<a href="#" class="options" onclick="javascript:toggleLoginOptions();" onfocus="blur();" />View <{$_language[options]}> &darr;</a>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<div class="loginoptions" style=" DISPLAY: <{if $displayloginoptions == '1'}>block<{else}>none<{/if}>;" id="loginoptions">

		<table width="100%" border="0" cellspacing="0" cellpadding="3">
			<tr>
				<td align='left' valign='top'>
					<table width="100%" border="0" cellspacing="0" cellpadding="3" class="smalltext">
			              <tr class="gridrow1">
			              		<td>
									<{if $_isStaffUser == true}><p><a class="forgotlink" href="<{$_baseName}><{$_templateGroupPrefix}>/Base/StaffLostPassword/Index" title="<{$_language[lostpassword]}>"><{$_language[lostpassword]}></a></p><{/if}>
					                <p><{$_language[rememberme]}>:<br>
					                <label for="rememberyes"><input type="radio" name="remember" class="swiftradio" id="rememberyes" value="1"<{if $_rememberMeCheckbox == true}> checked<{/if}> /> <{$_language[yes]}></label><label for="rememberno"><input type="radio" name="remember" id="rememberno" value="0"<{if $_rememberMeCheckbox == false}> checked<{/if}> /> <{$_language[no]}></label></p>
					                <p><{$_language[language]}>:<br>
					                <select name="languagecode" class="swiftselect">
					                	<{foreach key=_key item=_item from=$_languageList}>
					                	<option value="<{$_item[0]}>" <{if $_item[2] == true}> selected<{/if}>><{$_item[1]}></option>
					                	<{/foreach}>
					                	</select></p>
					            </td>
			              </tr>
					</table>
				</td>
			</tr>
		</table>

	</div>

	</td>
  </tr>
</table>














<input type="hidden" name="_ca" value="login"/>
<input type="hidden" name="_redirectAction" value="<{$_redirectAction}>"/>
</form>

<br /><div class="smalltext"><{$_poweredByNotice}><br /><{$_copyright}></div><br />
</center>
</div>


</body>
</html>
