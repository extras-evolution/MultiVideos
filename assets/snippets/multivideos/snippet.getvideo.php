<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
$output = '';
if (isset($link)) {
$thumbsUrl = isset($thumbsUrl) ? $thumbsUrl : 'assets/images/video/'; //thumbs folder
$emptyImage = isset($emptyImage) ? $emptyImage : 'assets/snippets/phpthumb/noimage.png'; //return this image if there's no image to return
$action = isset($action) ? $action : 'embed';
$forceDownload = isset($forceDownload) ? $forceDownload : 'false';

if (!class_exists('videoThumb'))include_once(MODX_BASE_PATH.'assets/plugins/multi/videothumb.class.php');
$video = new videoThumb(array(
	'imagesPath' => MODX_BASE_PATH . '/' . $thumbsUrl
	,'imagesUrl' => $thumbsUrl
	,'emptyImage' => $emptyImage
	,'forceDownload'=> $forceDownload
));
switch ($action) {
	case 'embed' : $embed = $video->process($link,false); $output = $embed['video']; break;
	case 'thumb' : $thumb = $video->process($link,true); $output = $thumb['image']; break;
}
}
return $output;
?>