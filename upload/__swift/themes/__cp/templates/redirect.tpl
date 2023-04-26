<{include file="header.tpl"}>
<body onLoad="javascript: window.location.href = '<{$_redirectURL}>';" class="redirect">

<script language="Javascript" type="text/javascript">
$(function(){
setTimeout(function() { window.location = '<{$_redirectURL}>'; }, 2000);
});
</script>

<div class="uiredirectwrapper">
	<img src="<{$_themePath}>images/kayako-loader.gif" border="0" align="absmiddle" />
	<p><{$_language[redirectloading]}></p>
</div>

</body></html>