<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title><{$_defaultTitle}></title>
<meta http-equiv="Content-Type" content="text/html; charset=<{$_language[charset]}>" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8" />
<link rel="stylesheet" type="text/css" media="all" href="<{$_baseName}>/Core/Default/Compressor/css" />
</head>
<body style="background-image: none;">
	<div class="reheaderbar">
	<div>
	<img src="<{$_headerImageCP}>" border="0" />
	</div>
	</div>

	<div>
		<b><{$_language[r_title]}></b> <{$_reportTitle}><BR />
		<b><{$_language[r_date]}></b> <{$_reportDate}><BR />
		<HR class="chatprinthr" />
		<{$_reportContents}>
	</div>
</body>
</html>