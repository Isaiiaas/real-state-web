<?php
	set_time_limit(0);
	
	class cURL
	{
		var $content;
		var $cookie;
		var $headers = array();
		var $ch;
		var $data;
		var $info;
		var $httpcode;
		var $proxy_ip;
		var $proxy_port;
		var $cookie_f;
		var $ref = '';
		
		function __construct($cookie = '') {
			if (!empty($cookie)) {
				
				$this->cookie_f = $cookie;
			}
		}
		
		function setproxy($proxy) {
			list($ip, $port) = explode(":", $proxy);
			$this->proxy_ip = $ip;
			$this->proxy_port = $port;
		}
		
		function setcookie($cookie) {
			$this->cookie = $cookie;
		}
		
		function open($type, $url) {
			$this->ch = curl_init();
			curl_setopt($this->ch, CURLOPT_URL, $url);
			curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0');
			curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($this->ch, CURLOPT_HEADER, 0);
			curl_setopt($this->ch, CURLOPT_VERBOSE, 0);
			if(!empty($this->ref)){
				curl_setopt($this->ch, CURLOPT_REFERER, $this->ref);
			}
			
			if (!empty($this->headers)) {
				curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
			}
			
			if (!empty($this->cookie)) {
				curl_setopt($this->ch, CURLOPT_COOKIE, $this->cookie);
			}
			
			if (!empty($this->proxy_ip)) {
				curl_setopt($this->ch, CURLOPT_HTTPPROXYTUNNEL, true);
				curl_setopt($this->ch, CURLOPT_PROXY, $this->proxy_ip.":".$this->proxy_port);
			}
			
			if (strtolower($type) == 'post') {
				curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($this->ch, CURLOPT_POST, 1);
				curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->data);
			}
			
			if (!empty($this->cookie_f)) {
				curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->cookie_f);
				curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookie_f);
			}
			
			$this->content = curl_exec($this->ch);
			$this->info = curl_getinfo($this->ch);
			$this->httpcode = $this->info['http_code'];
			curl_close($this->ch);
			return $this->content;
			
		}
	}
	
	//example
	
	$curl = new cURL();
	$curl->open("GET","https://www.realestatewebmasters.com/");// open the website
	
	if($curl->httpcode = 200){ //checking the http code
		//content is stored $curl->content
		preg_match_all("/<a href=\"(.*?)\"/i",$curl->content,$links);// getting links, simple regex
		echo "<pre>";
		print_r($links[1]);
		}else{
		echo "Ops! something went wrong";
	}
	
	
?>