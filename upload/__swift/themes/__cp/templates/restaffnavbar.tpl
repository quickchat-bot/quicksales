<tr height="<{$_finalHeight}>" id="cpfinalheighttr">
<td valign="top" align="left">
<table border="0" cellspacing="0" cellpadding="0" width="100%" height="<{$_finalHeight}>" id="cpfinalheighttable" class="table--fixed">
<tr><td width="200" valign="top" align="left" id="staffnavbarcontainer" class="staffnavbar">

<div class="ufstaffnavbarcontainer"><input type="text" id="ufsearch" name="ufsearch" class="removeiecross" /></div>

<div style="width: 100%; background: #ebddca; margin: 8px 0 8px 0; display: none;">
<table cellspacing="0" border="0" width="100%">
<tr>
<td>

<style type="text/css">
.cardcontainer                   { width: 100%; }
.cardcontainer .inlinecard       { width: 100%; height:25px;  }
.activecardcontainer             { position:relative; width:100%; padding-bottom:25px; }
.activecardcontainer .inlinecard { position:absolute; left: 4px; right:-8px; height:25px; }

.cardcontainer+.cardcontainer,
.cardcontainer+.activecardcontainer,
.activecardcontainer+.cardcontainer {  }

</style>

  <div class="cardcontainer">
    <div class="inlinecard"><div style="width: 100%; margin-left: 10px; height: 25px; background-color: #f8f4eb; border-top: 1px solid #cabeac; border-left: 1px solid #cabeac; border-bottom: 1px solid #cabeac; PADDING: 4px 0 0px 10px;">I am a card</div></div>
  </div>

  <div class="activecardcontainer">
    <div class="inlinecard"><div style="border-top: 1px solid #cabeac; border-left: 1px solid #cabeac; border-bottom: 1px solid #cabeac;"><div style="width: 100%; height: 25px; background-color: white;"><div style="PADDING: 4px 0 0px 10px;">I am overlap card</div></div></div></div>
  </div>

  <div class="cardcontainer">
    <div class="inlinecard"><div style="width: 100%; margin-left: 10px; height: 25px; background-color: #f8f4eb; border-top: 1px solid #cabeac; border-left: 1px solid #cabeac; border-bottom: 1px solid #cabeac; PADDING: 4px 0 0px 10px;">I am a card</div></div>
  </div>

  <div class="cardcontainer">
    <div class="inlinecard"><div style="width: 100%; margin-left: 10px; height: 25px; background-color: #f8f4eb; border-top: 1px solid #cabeac; border-left: 1px solid #cabeac; border-bottom: 1px solid #cabeac; PADDING: 4px 0 0px 10px;">I am a card</div></div>
  </div>

</td>
</tr>
</table>
</div>
	<table width="200" border="0" cellspacing="0" cellpadding="0" class="table--fixed">
		<tr>
			<td>
				<div id="customnavcontainer"></div>
				<{if $_settings[g_onlusr] == '1'}>
					<div class="renavsection" id="itemoptionsnav">
						<div class="navsub">
							<div class="navtitle"><{$_language[onlineusers]}></div>
							<div id="onlinestaffcontainer">
								<{RenderOnlineStaff}>
							</div>
						</div>
					</div>
				<{/if}>
			</td>
		</tr>
	</table>

</td>
<td id="staffnavbarc_1" class="staffnavbarclickable cpuisplitter" width="10" valign="middle" align="center">
	<img src="<{$_themePath}>images/rebarsplitter.gif" height="17" width="2"/>
</td>
<td valign="top" align="left" width="100%" class="cpuicontainer">
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td colspan="2" id="staffnavbar" height="100%">
<ul class="swiftdropdown" id="usersearchmenu" style="display: none;">
<li class="swiftdropdownitemparent" onclick="javascript: loadViewportData('/Base/UserSearch/Advanced');"><div class="swiftdropdownitem"><div class="swiftdropdownitemimage"><img src="<{$_themePath}>images/menu_advancedsearch.png" align="absmiddle" border="0" /></div><div class="swiftdropdownitemtext" onclick="javascript: void(0);"><{$_language[sadvancedsearch]}></div></div></li>
<li class="swiftdropdownitemparent" onclick=""><div class="swiftdropdowninput"><div style="padding: 4px;" class="swiftdropdownitemtext" onclick="javascript: void(0);"><form method="post" action="<{$_baseName}>/Base/UserSearch/User" id="searchusmenuform" name="searchusmenuform"><{$_language[seuser]}><br /><input type="text" class="swifttextsearch" name="query" value="" /></form></div></div></li>
<li class="swiftdropdownitemparent" onclick=""><div class="swiftdropdowninput"><div style="padding: 4px;" class="swiftdropdownitemtext" onclick="javascript: void(0);"><form method="post" action="<{$_baseName}>/Base/UserSearch/UserOrganization" id="searchuorgmenuform" name="searchuorgmenuform"><{$_language[seuserorg]}><br /><input type="text" class="swifttextsearch" name="query" value="" /></form></div></div></li>
</ul>
<ul class="swiftdropdown" id="chatsearchmenu" style="display: none;">
<li class="swiftdropdownitemparent" onclick="javascript: loadViewportData('/LiveChat/Search/Advanced');"><div class="swiftdropdownitem"><div class="swiftdropdownitemimage"><img src="<{$_themePath}>images/menu_advancedsearch.png" align="absmiddle" border="0" /></div><div class="swiftdropdownitemtext" onclick="javascript: void(0);"><{$_language[sadvancedsearch]}></div></div></li>
<li class="swiftdropdownitemparent" onclick=""><div class="swiftdropdowninput"><div style="padding: 4px;" class="swiftdropdownitemtext" onclick="javascript: void(0);"><form method="post" action="<{$_baseName}>/LiveChat/Search/QuickSearch" id="searchchatform" name="searchchatform"><{$_language[squicksearch]}><br /><input type="text" class="swifttextsearch" name="query" value="" /></form></div></div></li>
<li class="swiftdropdownitemparent" onclick=""><div class="swiftdropdowninput"><div style="padding: 4px;" class="swiftdropdownitemtext" onclick="javascript: void(0);"><form method="post" action="<{$_baseName}>/LiveChat/Search/ChatID" id="searchchatidform" name="searchchatidform"><{$_language[schatid]}><br /><input type="text" class="swifttextsearch" name="query" value="" /></form></div></div></li>
<li class="swiftdropdownitemparent" onclick=""><div class="swiftdropdowninput"><div style="padding: 4px;" class="swiftdropdownitemtext" onclick="javascript: void(0);"><form method="post" action="<{$_baseName}>/LiveChat/Search/Messages" id="searchchatmessform" name="searchchatmessform"><{$_language[smessagesurvey]}><br /><input type="text" class="swifttextsearch" name="query" value="" /></form></div></div></li>
</ul>
<ul class="swiftdropdown" id="ticketsearchmenu" style="display: none;">
<li class="swiftdropdownitemparent" onclick="javascript: loadViewportData('/Tickets/Search/NewTickets');"><div class="swiftdropdownitem"><div class="swiftdropdownitemimage"><img src="<{$_themePath}>images/menu_newtickets.png" align="absmiddle" border="0" /></div><div class="swiftdropdownitemtext" onclick="javascript: void(0);"><{$_language[snewtickets]}></div></div></li>
<li class="swiftdropdownitemparent" onclick="javascript: loadViewportData('/Tickets/Search/Advanced');"><div class="swiftdropdownitem"><div class="swiftdropdownitemimage"><img src="<{$_themePath}>images/menu_advancedsearch.png" align="absmiddle" border="0" /></div><div class="swiftdropdownitemtext" onclick="javascript: void(0);"><{$_language[sadvancedsearch]}></div></div></li>
<li class="swiftdropdownitemparent" onclick=""><div class="swiftdropdowninput"><div style="padding: 4px;" class="swiftdropdownitemtext" onclick="javascript: void(0);"><form method="post" action="<{$_baseName}>/Tickets/Search/QuickSearch" id="searchmenuform" name="searchmenuform"><input id="isajax" type="hidden" value="1" name="isajax" autocomplete="OFF"><{$_language[squicksearch]}><br /><input type="text" class="swifttextsearch" name="query" value="" /></form></div></div></li>
<li class="swiftdropdownitemparent" onclick=""><div class="swiftdropdowninput"><div style="padding: 4px;" class="swiftdropdownitemtext" onclick="javascript: void(0);"><form method="post" action="<{$_baseName}>/Tickets/Search/TicketID" id="searchtimenuform" name="searchtimenuform"><input id="isajax" type="hidden" value="1" name="isajax" autocomplete="OFF"><{$_language[sticketidlookup]}><br /><input type="text" class="swifttextsearch" name="query" value="" /></form></div></div></li>
<li class="swiftdropdownitemparent" onclick=""><div class="swiftdropdowninput"><div style="padding: 4px;" class="swiftdropdownitemtext" onclick="javascript: void(0);"><form method="post" action="<{$_baseName}>/Tickets/Search/Creator" id="searchcrmenuform" name="searchcrmenuform"><input id="isajax" type="hidden" value="1" name="isajax" autocomplete="OFF"><{$_language[screatorreplier]}><br /><input type="text" class="swifttextsearch" name="query" value="" /></form></div></div></li>
</ul>
<ul class="swiftdropdown" id="ticketfiltermenu" style="display: none;">
<li class="swiftdropdownitemparent" onclick="javascript: loadViewportData('/Tickets/Filter/Manage');"><div class="swiftdropdownitem"><div class="swiftdropdownitemimage"><img src="<{$_themePath}>images/menu_ticketfilters.png" align="absmiddle" border="0" /></div><div class="swiftdropdownitemtext" onclick="javascript: void(0);"><{$_language[smanage]}></div></div></li>
<li class="seperator"></li>
<{foreach key=key item=_item from=$_ticketFilterContainer}>
<li class="swiftdropdownitemparent" onclick="javascript: loadViewportData('/Tickets/Search/Filter/<{$_item[ticketfilterid]}>');"><div class="swiftdropdownitem"><div class="swiftdropdownitemtext" onclick="javascript: void(0);"><{$_item[title]}></div></div></li>
<{/foreach}>
</ul>
<div id="cpmenu" style="height: 100%; display: none;">
