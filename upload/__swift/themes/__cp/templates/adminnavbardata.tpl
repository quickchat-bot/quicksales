<div class="renavsectionadmin">
	<div class="navsub">

		<div class="navtitle">
			<{$_language[options]}>
		</div>

		<div id='parent'>

			<{foreach key=key item=_item from=$_adminNavigationBar}>
			

				<{if $_item[3] == ""}>

					<div class='BarItem' onclick="LoadBarMenu('item<{$key}>', this, false);SetActiveBarItem(this);" id="Bar<{$key}>" title="<{$_item[0]}>">
						<img style='vertical-align: middle' src="<{$_themePath}>images/<{$_item[1]}>" border="0">&nbsp;<{$_item[0]}>
					</div>

				<{else}>

					<div class='BarItem' onclick="javascript:ResetTopMenuToHome(); CollapseBarMenu(); SetActiveBarItem(this); loadViewportData('<{$_baseName}><{$_item[3]}>');" title="<{$_item[0]}>">
						<img style='vertical-align: middle' src="<{$_themePath}>images/<{$_item[1]}>" border="0">&nbsp;<{$_item[0]}>
					</div>

				<{/if}>
				

				<div class='BarOptions' id='item<{$key}>'>

					<{foreach key=itemkey item=_baritem from=$_item[5]}>

						<a class="remoteload" resettopmenutohome="1" href="<{$_baseName}><{$_baritem[1]}>" viewport="1">
							<div class='BarOption' onclick="resetTopMenu(); SetActiveBarOption(this); ">
								<div class="BarOptionPad"><{$_baritem[0]}></div>
							</div>
						</a>

					<{/foreach}>

				</div>



			<{/foreach}>

		</div>

	</div>
</div>

