<{include file="header.tpl"}>
  <body id="printticket" style="background-image: none;" onload="javascript: window.print();">
	<div class="reheaderbar">
		<div class="rebarlogo">
			<img src="<{$_headerImageCP}>" border="0" width="200" />
		</div>
	</div>

	<div id="printticketcontent">
		<div class="headingtext"><{$_companyName}></div>
		<div class="titletext"><span class="ticketid"><{$_ticketID}>:</span> <{$_ticketSubject}></div><BR />		
		<strong><{$_language[f_department]}></strong>: <{$_departmentTitle}><BR />
		<strong><{$_language[f_owner]}></strong>: <{$_ticketOwnerTitle}><BR />
		<strong><{$_language[f_type]}></strong>: <{$_ticketTypeTitle}><BR />
		<strong><{$_language[f_ticketstatus]}></strong>: <{$_ticketStatusTitle}><BR />
		<strong><{$_language[f_priority]}></strong>: <{$_ticketPriorityTitle}><BR />
		<strong><{$_language[tinfocreated]}></strong> <{$_ticketDate}>&nbsp;&nbsp;&nbsp;&nbsp;
		<strong><{$_language[tinfoupdated]}></strong> <{$_ticketUpdated}>
		<BR />
		
		<{if $_billingEntries ne '' }>
			<br/>
			<{$_billingEntries}>
			<HR class="ticketprinthr" />
		<{/if}>
		
		<{if $_customFields ne '' }>
			<br/>
			<{$_customFields}>
			<HR class="ticketprinthr" />
		<{/if}>
		
		<{if $_ticketNotes ne '' }>
			<br/>
			<{$_ticketNotes}>
			<HR class="ticketprinthr" />
		<{/if}>

		<h3>Posts</h3>
		<{foreach key=key item=_item from=$_ticketPost}>		
			<div class="ticketpostleft">
				<strong><{$_item[fullname]}></strong>
				<{if $_item[designation] ne '' }>
					<br><{$_item[designation]}>
				<{/if}>
				<br><br>(<{$_item[creator]}>)
				<br>
			</div>
			<div class="ticketpostright">
				<{$_item[contents]}><br><br>
				<{$_item[date]}>
			</div>			
			<div class="clearer"></div>
			<hr class="ticketprinthr" />
		<{/foreach}>
		
	</div>
</body>
</html>
