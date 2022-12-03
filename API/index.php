<?php
error_reporting(0);
ini_set('max_execution_time', 0);
$useragent = "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.96 Safari/537.36";
function DeHashTheApi($Api)
{
    $default = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    $custom  = "ZYXWVUTSRQPONMLKJIHGFEDCBAzyxwvutsrqponmlkjihgfedcba9876543210+/";
    return base64_decode(strtr($Api, $custom, $default));
}
if(empty($_GET['url'])){
	$exAPI = explode('KJGORHJGO', DeHashTheApi($_GET['api']));
	$v = $exAPI[0];
	$fileSize = $exAPI[1];
} else {
	$v = $_GET['url'];
	$fileSize = $_GET['size'];
}

if(empty($fileSize)){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 222222);
	curl_setopt($ch, CURLOPT_URL, $v);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$info  = curl_exec($ch);
	$fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
	//echo 'Nofucking';
}
$size     = $fileSize;
$length   = $size;
$start    = 0;
$end      = $size - 1;
header('Content-type: video/mp4');
//header("Accept-Ranges: 0-$length");
header("Accept-Ranges: bytes");
if (isset($_SERVER['HTTP_RANGE'])) {
    $c_start = $start;
    $c_end   = $end;
    list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
    
    if (strpos($range, ',') !== false) {
        header('HTTP/1.1 416 Requested Range Not Satisfiable');
        header("Content-Range: bytes $start-$end/$size");
        exit;
    }
    
    if ($range == '-') {
        $c_start = $size - substr($range, 1);
    } else {
        $range   = explode('-', $range);
        $c_start = $range[0];
        $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
    }
    
    $c_end = ($c_end > $end) ? $end : $c_end;
    
    if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
        header('HTTP/1.1 416 Requested Range Not Satisfiable');
        header("Content-Range: bytes $start-$end/$size");
        exit;
    }
    
    $start  = $c_start;
    $end    = $c_end;
    $length = $end - $start +1;
    //fseek($fp, $start);
    header('HTTP/1.1 206 Partial Content');
}

header("Content-Range: bytes $start-".$end."/$fileSize");
header("Content-Length: " . $length);

$ch = curl_init();
if (isset($_SERVER['HTTP_RANGE'])) {
    // if the HTTP_RANGE header is set we're dealing with partial content
    $partialContent = true;
    // find the requested range
    // this might be too simplistic, apparently the client can request
    // multiple ranges, which can become pretty complex, so ignore it for now
    /*preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
    $offset  = intval($matches[1]);
    $length  = $fileSize - $offset - 1;
    $headers = array(
        'Range: bytes=' . $offset . '-' . ($offset + $length) . ''
    );*/
    $headers = array(
        'Range: bytes=' . $start . '-' . $size . ''
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
}
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 222222);
curl_setopt($ch, CURLOPT_URL, $v);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
curl_setopt($ch, CURLOPT_NOBODY, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch, CURLOPT_REFERER, $v);
curl_exec($ch);
?>
