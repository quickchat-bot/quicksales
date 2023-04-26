<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><{$_productTitle}></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<script type="text/javascript">
	var _swiftPath = "<{$_swiftPath}>";
</script>
<link rel="stylesheet" type="text/css" media="all" href="<{$_baseName|default:index.php?}>/Core/Default/Compressor/css" />
<script type="text/javascript" src="<{$_baseName|default:index.php?}>/Core/Default/Compressor/js"></script>
<script language="Javascript" type="text/javascript">
	<{$_jsInitPayload}>

	// Workaround for the stupid 100% width bug in IE
	function ClearError() { return true; }
	window.onerror = ClearError;
</script>
</head>

<body>
	<div class="container">
		<img src="<{$_themePath}>images/kayako-logo-dark.svg" alt="<{$_product}>" width="200px"/>

		<div class="centerarea">

		<{if $_productInstalled == true}>
			<a href="index.php?/Core/Upgrade" class="upgrade button"><{$_language[upgrade]}></a>
			<hr />
		<{/if}>

			<a href="index.php?/Core/Setup" class="button"><{$_language[setup]}></a>
			<a href="index.php?/Core/Diagnostics" class="button"><{$_language[diagnostics]}></a>
		</div>
		<span class="smalltext"><{$_copyright}></span></div>
	</div><!--container-->
</body>
</html>
