<?php

class videoThumb {

	var $config;

	function __construct($config = array()) {
		$this->config = array_merge(array(
			'imagesPath' => dirname(__FILE__) . '/images/'
			,'imagesUrl' => '/images/'
			,'emptyImage' => '/images/_empty.png'
			,'forceDownload' => false
		),$config);

		if (!is_dir($this->config['imagesPath'])) {
			mkdir($this->config['imagesPath']);
		}
	}

	/*
	 * Check and format video link, then fire download of preview image
	 * @param string $video Remote url on video hosting
	 * @return array $array Array with formatted video link and preview url
	 * */
	function process($video = '', $getImage = false) {
		if (empty($video)) {return;}
		if (!preg_match('/^(http|https)\:\/\//i', $video)) {
			$video = 'http://' . $video;
		}
		// YouTube
		if (preg_match('/[http|https]+:\/\/(?:www\.|)youtube\.com\/watch\?(?:.*)?v=([a-zA-Z0-9_\-]+)/i', $video, $matches) || preg_match('/[http|https]+:\/\/(?:www\.|)youtube\.com\/embed\/([a-zA-Z0-9_\-]+)/i', $video, $matches) || preg_match('/[http|https]+:\/\/(?:www\.|)youtu\.be\/([a-zA-Z0-9_\-]+)/i', $video, $matches)) {
			$video = 'http://www.youtube.com/embed/'.$matches[1];
			$image = 'http://img.youtube.com/vi/'.$matches[1].'/0.jpg';

			$array = array(
				'video' => $video
				,'image' => $getImage ? $this->getRemoteImage($image) : ''
			);
		}
		// Vimeo
		else if (preg_match('/[http|https]+:\/\/(?:www\.|)vimeo\.com\/([a-zA-Z0-9_\-]+)(&.+)?/i', $video, $matches) || preg_match('/[http|https]+:\/\/player\.vimeo\.com\/video\/([a-zA-Z0-9_\-]+)(&.+)?/i', $video, $matches)) {
			$video = 'http://player.vimeo.com/video/'.$matches[1];
			$image = '';
			if ($xml = simplexml_load_file('http://vimeo.com/api/v2/video/'.$matches[1].'.xml')) {
				$image = $xml->video->thumbnail_large ? (string) $xml->video->thumbnail_large: (string) $xml->video->thumbnail_medium;
				$image = $this->getRemoteImage($image);
			}
			$array = array(
				'video' => $video
				,'image' => $getImage ? $this->getRemoteImage($image) : ''
			);
		}
		// ruTube
		else if (preg_match('/[http|https]+:\/\/(?:www\.|)rutube\.ru\/video\/embed\/([a-zA-Z0-9_\-]+)/i', $video, $matches) || preg_match('/[http|https]+:\/\/(?:www\.|)rutube\.ru\/tracks\/([a-zA-Z0-9_\-]+)(&.+)?/i', $video, $matches)) {
			$video = 'http://rutube.ru/video/embed/'.$matches[1];
			$image = '';
			if ($xml = simplexml_load_file("http://rutube.ru/cgi-bin/xmlapi.cgi?rt_mode=movie&rt_movie_id=".$matches[1]."&utf=1")) {
				$image = (string) $xml->movie->thumbnailLink;
				$image = $this->getRemoteImage($image);
			}
			$array = array(
				'video' => $video
				,'image' => $getImage ? $this->getRemoteImage($image) : ''
			);
		}
		else if (preg_match('/[http|https]+:\/\/(?:www\.|)rutube\.ru\/video\/([a-zA-Z0-9_\-]+)\//i', $video, $matches)) {
			$html = $this->Curl($matches[0]);
			return $this->process($html);
		}
		// No matches
		else {
			return;
			}

		return $array;
	}

	/*
	 * Download ans save image from remote service
	 * @param string $url Remote url
	 * @return string $image Url to image or false
	 * */
	function getRemoteImage($url = '') {
		if (empty($url)) {return $this->config['emptyImage'];}

		$image = '';
		$tmp = explode('.', $url);
		$ext = '.' . end($tmp);
		$filename = md5($url) . $ext;
		if (!$this->config['forceDownload'] && file_exists($this->config['imagesPath'] . $filename)) {
			$image = $this->config['imagesUrl'] . $filename;
			} 
		else {
			$response = $this->Curl($url);
				if (!empty($response)) {
					if (file_put_contents($this->config['imagesPath'] . $filename, $response)) {
					$image = $this->config['imagesUrl'] . $filename;
					}
				}
		}
		if (empty($image)) {$image = $this->config['emptyImage'];}

		return $image;
	}

	/*
	 * Method for loading remote url
	 * @param string $url Remote url
	 * @return mixed $data Results of an request
	 * */
	function Curl($url = '') {
		if (empty($url)) {return false;}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);

		$data = curl_exec($ch);
		return $data;
	}
}