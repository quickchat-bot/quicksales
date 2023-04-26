<?php
/// GFI HelpDesk Call home usage telemetry
/// v1.3
/// www.opencart.com.vn

function getLicenseKey()
{
	if (file_exists(dirname(__FILE__)."/../key.php"))
	{
		$fs = fopen(dirname(__FILE__)."/../key.php", "r");
		$licenseKey = fgets($fs);
		fclose($fs);

		$lcPos = strpos($licenseKey, "?>", 50);

		if ($lcPos > 0) 
		{
			$licenseKey = substr($licenseKey, $lcPos+2);
			$licenseKey = trim($licenseKey);
		
			return $licenseKey;
		}
		else
			return "INVALID";
	}
	
	return "NOKEY";
}

function getInstallID()
{
	$installID = "";
	
	if (file_exists(dirname(__FILE__)."/library/iid.php"))
	{
		$fs = fopen(dirname(__FILE__)."/library/iid.php", "r");
		$installID = fgets($fs);
		fclose($fs);
	}
	else
	{
		$fs = fopen(dirname(__FILE__)."/library/iid.php", "w");
		$installID = uniqid("HD");
		fwrite($fs, $installID);
		fclose($fs);
	}
	
	return $installID;
}

function getEdition()
{
	if (file_exists(dirname(__FILE__)."/../__apps/livechat/config/settings.xml"))
	{
		return "fusion";
	}
	else
	{
		return "case";
	}
}

function getLastSent()
{
	$lastSent = "";
	
	if (file_exists(dirname(__FILE__)."/library/lsnt.php"))
	{
		$fs = fopen(dirname(__FILE__)."/library/lsnt.php", "r");
		$lastSent = fgets($fs);
		fclose($fs);
	}
		
	return $lastSent;
}

function setLastSent()
{
	$dt = date("U");
	
	$fs = fopen(dirname(__FILE__)."/library/lsnt.php", "w");
	$lastSent = fwrite($fs, $dt);
	fclose($fs);
}

function sendData($lKey, $iID, $edition)
{
	$dt = date("m-d-Y h:i a"); //we send date in this format
	$platform = php_uname();
	
	$url = 'https://telemetry.opencart.com.vn/license/gfiky.php';
		
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	
	curl_setopt($ch, CURLOPT_POSTFIELDS, 
          http_build_query(array(
		'code' => 'HDK',
		'license' => $lKey,
		'installid' => $iID,
		'edition' => $edition,
		'domain' => $_SERVER['HTTP_HOST'],
		'browser' => $_SERVER['HTTP_USER_AGENT'],
		'platform' => $platform,
		'date' => $dt
	)));
	
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($ch);
	
	curl_close ($ch);
	
	if (($response != null) && (trim($response) == "OK"))
	{
		setLastSent();
	}
}
 
function doWork()
{
	//Telemetry Enabled or Not
	$enabled = true;
	
	try {
		if (!$enabled) 
			return;
		
		if (!headers_sent()) {
			session_start();
		}
		
		$hour = 0;
		if (isset($_SESSION["gfih"]))
		{
			$ts1 = $_SESSION["gfih"];
			$now = date("U");
			$hour = abs($now - $ts1)/(60*60);
		}
		
		if ($hour >= 0) //we only check every hour
		{ 
			$lastSent = getLastSent();
			if ($lastSent != "") {
				$ts2 = $lastSent;
				$now2 = date("U");
				$timeDiff = abs($now2 - $ts2)/(60*60);
			}
			else {
				$timeDiff = 100;
			}
			
			if (($enabled) && ($timeDiff >= 24))
			{
				$_SESSION["gfih"] = date("U");
				
				$lKey = getLicenseKey();
				$iID = getInstallID(); 
				$edition = getEdition();
			
				sendData($lKey, $iID, $edition); //we send every 24 hours;
			}
		}
	}
	catch (Exception $e) {
		
	}
}

@doWork();

?>