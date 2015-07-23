<?php

	/**
	 * It's necesary for old versions of PHP
	 */
	if( !function_exists("json_encode") ){
		require_once("upgrade.php");
	}
	
	/**
	 * Server Class
	 * 
	 * Send data to Url and return to Client class the request.
	 *
	 * REQUIRED INSTALL: php-curl.
	 *
	 * @example examples/index.php
	 * 
	 */
	class Server {

		/**
		 * @var constant Script version
		 */
		const VERSION = "0.3a";

		/**
		 * @var  constant Path for store cookies creates in request to url.
		 */
		const PATH_COOKIE = "cookies/";

		/**
		 * IMPORTANT: Put another random key and add in Server Class and Client Class.
		 *
		 * @var constant Key of 40 chars for encrypt connection between server and client.
		 */
		const KEY = "abcdfghijkmnlopqrstuvwxyz123456789101112";

		/**
		 * @var array Request array returned to Client Class.
 		 * contentPage  :	Code of url. 
		 * errors      	:	Errors in prosess of request.
		 * info     	:	Information of process Server Class.
		 * cookie      	:	For use or set cookie.
		 * headers      :	Custom headers to send to url.
		 * cookie   	:	If ClientClass set "showCookie" Server Class put it here.
		 */
		private $resultRequest = array(
				"contentPage"	=>	"",
				"errors"		=>	"",
				"info"			=>	"",
				"cookie"		=>	""
			);

		function __construct(){

			if( !file_exists(self::PATH_COOKIE) ){
				mkdir(self::PATH_COOKIE,0770);
			}

			if( !is_dir(self::PATH_COOKIE) ){
				$this->resultRequest["errors"] .= "[!] Fail open dir cookies. \n";
			}

			$this->resultRequest["info"] .= "[INFO] jProxySon: ".self::VERSION." \n";
			$this->resultRequest["info"] .= "[INFO] ".date("Y-m-d H:i:s")."\n";
			$this->resultRequest["info"] .= "[INFO] Proxy: ".$_SERVER["HTTP_HOST"]."\n";

			$this->sendRequest();
			$this->showData();

		}

		/**
		 * Show encrypt hash in page.
		 * @return string Has data string.
		 */
		private function showData(){
			echo $this->encrypt(json_encode($this->resultRequest));
		}

		/**
		 * Encrypt string data to display.
		 * @param  string $string To encrypt.
		 * @return string         Hash in sha256.
		 */
		private function encrypt($string){
			$iv = mcrypt_create_iv(
			    mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC),
			    MCRYPT_DEV_URANDOM
			);
		
			$encrypted = base64_encode(
			    $iv .
			    mcrypt_encrypt(
			        MCRYPT_RIJNDAEL_256,
			        hash('sha256', self::KEY, true),
			        $string,
			        MCRYPT_MODE_CBC,
			        $iv
			    )
			);	
			return $encrypted;
		}

		/**
		 * Dencrypt string received of url request.
		 * @param  string $string Has to dencrypt.
		 * @return string         String decrypted.
		 */
	 	public function decrypt($string){
	        $data = base64_decode($string);
	        $iv = substr($data, 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC));

	        $decrypted = rtrim(
	            mcrypt_decrypt(
	                MCRYPT_RIJNDAEL_256,
	                hash('sha256', self::KEY, true),
	                substr($data, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)),
	                MCRYPT_MODE_CBC,
	                $iv
	            ),
	            "\0"
	        );
	        return $decrypted;
	    }

	    /**
	     * Send request to url.
	     * @return bool If something result wrong return false.
	     */
		public function sendRequest(){

			if( !isset($_POST["data"]) ){
				$this->resultRequest["errors"] .= "[!] Fail data not set \n";
				return false;
			}

			if( empty($_POST["data"]) ){
				$this->resultRequest["errors"] .= "[!] Fail data empty \n";
				return false;
			}

			$json = $this->decrypt($_POST["data"]);
			if( empty($json) ){
				$this->resultRequest["errors"] .= "[!] Fail data decrypt \n";
				return false;
			}

			$config = json_decode($json,true);

			if( @empty($config["url"]) ){
				$this->resultRequest["errors"] .= "[!] Fail url empty \n";
				return false;
			}

			$this->resultRequest["contentPage"] = utf8_encode($this->sendCurl($config));
			$this->resultRequest["info"] .= "[INFO] Config Array: \n".print_r($config,true);

			if( isset($config["showCookie"]) ){
				if( $config["showCookie"] === true ){
					$this->resultRequest["cookie"] = file_get_contents(self::PATH_COOKIE.$config["id"]);
				}
			}

		}

		/**
		 * Send curl to url.
		 * @param  array $config It's same array of Client Class "$defaultDataRequest".
		 * @return string        Code of page request.
		 */
		protected function sendCurl($config){

			if( isset($config["cookie"]) ){
				if( !empty($config["cookie"]) ){
					file_put_contents(self::PATH_COOKIE.$config["id"],$config["cookie"]);
				}
			}

			$curl = curl_init();
			curl_setopt($curl,CURLOPT_URL,$config["url"]);
			curl_setopt($curl,CURLOPT_HEADER,false);
			curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

			if( !empty($config["headers"]) ){
				curl_setopt($curl, CURLOPT_HTTPHEADER, $config["headers"]); 
			}
			if( !empty($config["post"]) ){
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt ($curl, CURLOPT_POSTFIELDS, $config["post"]);
			}
			if( !empty($config["id"]) ){
				curl_setopt($curl, CURLOPT_COOKIEFILE, self::PATH_COOKIE.$config["id"]);  
	            curl_setopt($curl, CURLOPT_COOKIE,self::PATH_COOKIE.$config["id"]);  
        		curl_setopt($curl, CURLOPT_COOKIEJAR, self::PATH_COOKIE.$config["id"]);
			}
			$source = curl_exec($curl);
			curl_close($curl);
			return $source;
		}

	}

?>