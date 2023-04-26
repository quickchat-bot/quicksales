
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

	<title>GlobalExceptionHandler</title>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW" />

	<style type="text/css">
		html, body {
			background-color: #3a4865;
			text-align: center;
		}

		#content {
			margin: 10% auto 0px auto;
			font-family: verdana, Helvetica, sans-serif;
			font-size: 0.8em;
			color: white;
			height: 90%;
		}

		#content .secondary {
			color: #b8bac0;
			font-size: 0.8em;
		}

		#content a:link, #content a:visited, #content a:active
		{
			background: #4a5c81;
			color: white;
			text-decoration: none;
			padding: 5px;
		}

		#content a:hover
		{
			background: #2f3c55;
			color: white;
		}

	</style>

</head>

<body>

<div id="content">
	<p><img alt="Uncaught exception" src="<{$_themePath}>/images/exception.png" /></p>
	<p><{$_errorMessage}></p>
</div>

</body>

</html>