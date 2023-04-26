var menulinks = new Array();

<{foreach key=key item=_item from=$_menuLinks}>
menulinks[<{$key}>] = new Array();

	<{foreach key=linkkey item=_linkitem from=$_item}>
	menulinks[<{$key}>][<{$linkkey}>] = "<a <{if $_linkitem[20] != ''}>id=\"<{$_linkitem[20]}>\" <{/if}>href=\"<{if $_linkitem[14] neq ""}>javascript: void(0);<{else}><{$_baseName}><{$_linkitem[1]}><{/if}>\" <{if $_linkitem[14] neq ""}>onclick=\"<{$_linkitem[14]}>\"<{else}>collapsebarmenu=\"1\" viewport=\"1\"<{/if}> class=\"remoteloadmenu\"><div onclick=\"ActivateMenuItem(this, event);\" class=\"topnavmenuitem<{if $_linkitem[25] == '1'}> topnavmenuitemdynamic<{/if}>\" alt='<{$_linkitem[0]}>' id='linkmenu<{$key}>_<{$linkkey}>'><{$_linkitem[0]}><{if $_linkitem[15] == true}><img src='<{$_themePath}>images/menudrop_grey.svg' border='0' /><{/if}></div></a>";
	<{/foreach}>
<{/foreach}>