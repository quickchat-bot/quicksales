<tr height="<{$_finalHeight}>" id="cpfinalheighttr">
<td valign="top" align="left">
<table border="0" cellspacing="0" cellpadding="0" width="100%" height="<{$_finalHeight}>" id="cpfinalheighttable" class="table--fixed">
<tr><td width="200" valign="top" align="left" id="staffnavbarcontainer" class="staffnavbar">

<table width="200" border="0" cellspacing="0" cellpadding="2" class="table--fixed">
	<tr>
		<td>
			<div id="customnavcontainer"></div>

			<div class="renavsection" id="itemoptionsnav">
				<div class="navsub">
					<div class="navtitle"><{$_language[onlineusers]}></div>
					<div id="onlinestaffcontainer">
						<{RenderOnlineStaff}>
					</div>
				</div>
			</div>
		</td>
	</tr>
</table>
</td>
<td id="staffnavbarc_1" class="staffnavbarclickable cpuisplitter" width="10" 1align="top" align="left"><img src="<{$_themePath}>images/space.gif" width="10" height="1" style="z-index: 100;" /></td>
<td valign="top" align="left" width="100%" height="100%" class="<{if $_isDashboard == true}>cpuicontainer<{else}><{/if}>">
	<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td colspan="2" id="staffnavbar" height="100%">
				<div id="cpmenu" style="height: 100%; display: none;">