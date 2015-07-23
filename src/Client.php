<?php

	/**
	 * Client Class
	 * 
	 * Send data to Server Class and receive.
	 *
	 * REQUIRED INSTALL: php-curl.
	 * 
	 * @example examples/exampleClient.php
	 *
	 */
	class Client {

		/**
		 * @var array List of proxys in hostings. For example "proxy1.webhosting.net/index.php"
		 */
		private $proxys = array(
			"exampleproxy1.webhosting.net/index.php"
			);

		/**
		 * @var constant Script version
		 */
		const VERSION = "0.2a";

		/**
		 * IMPORTANT: Put another random key for you.
		 * @var constant Key of 40 chars for encrypt connection between server and client.
		 */
		const KEY = "abcdfghijkmnlopqrstuvwxyz123456789101112";

		/**
		 * @var array Default configuration of request.
 		 * proxy    	: (Required)	The proxy in the "$proxys" list. : 
		 * url      	: (Required)	URL that you want to send request.
		 * post     	: (Optional)	If you need send post vars to the url.
		 * id       	: (Optional)	For use or set cookie.
		 * headers      : (Optional)	Custom headers to send to url.
		 * cookie   	: (Optional)	Content of custom cookie.
		 * showCookie   : (Optional)	Return cookie content.
		 */
		private $defaultDataRequest = array(
			"proxy"			=>	"",
			"url"			=>	"",
			"post"			=>	array(),
			"id"			=>	"",
			"headers"		=>	array(),
			"showCookie"	=>	false,
			"cookie"		=>	""
			);

		/**
		 * @param boolean $debug Debug mode.
		 */
		private $debug = false;

		/**
		 * Initialitze with debug mode.
		 * @param boolean $debug For set debug mode.
		 */
		function __construct( $debug = false ){
			$this->debug = $debug;
			if( $this->debug == true ){
				date_default_timezone_set("Europe/Madrid");
				echo "[INFO] jProxyClientClass: ".self::VERSION." \n";
				echo "[INFO] ".date("Y-m-d H:i:s")."\n";
			}
		}

		/**
		 * Encrypt string for send to server.
		 * @param  string $string To encrypt.
		 * @return string         Hash in sha256.
		 */
		private function encrypt( $string ){
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
		 * Dencrypt string received of server.
		 * @param  string $string Has to dencrypt.
		 * @return string         String decrypted.
		 */
		public function decrypt( $string ){
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
	     * Collector of info for curl send.
	     * @param  array  $customDataRequest Custom configuration data for request like $defaultDataRequest.
	     * @return array Return info response in array.
	     */
		private function sendRequest( $customDataRequest = array() ){

			try {
				$this->setDataRequest($customDataRequest);
			} catch (Exception $e) {
			    throw new Exception($e->getMessage());
			}

			$json = array();
			foreach( $this->dataRequest as $key => $value ){
				if( !empty($this->dataRequest[$key]) ){
					$json[$key] = $this->dataRequest[$key];
				}
			}

			if( $this->debug == true ){
				echo "[i] Data send:";
				print_r($json);
			}
			
			if( !($json_enc = $this->encrypt(json_encode($json))) ){
				throw new Exception("[!] Error Encrypt Data. \n");
			};

			$params_post = array(
				"data"	=>	$json_enc
				);

			$params_post = http_build_query($params_post);

			$curl = curl_init();
			curl_setopt($curl,CURLOPT_URL,$this->dataRequest["proxy"]);
			curl_setopt($curl,CURLOPT_HEADER,false);
			curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
	        curl_setopt($curl, CURLOPT_POST, true);
	        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $params_post);
			$source = curl_exec($curl);
			curl_close($curl);
			$resu = json_decode($this->decrypt($source),true);

			if( empty($resu) ){
				return $source;
			}

			return $resu;

		}

		/**
	     * Collector of info for curl send.
	     * @param  array  $customDataRequest Custom configuration data for request like $defaultDataRequest.
	     * @return array Return info response in array.
	     */
		public function setDataRequest( $customDataRequest = array() ){

			$this->dataRequest = array_merge($this->dataRequest,$customDataRequest);

			if( empty($this->dataRequest["proxy"]) ){
				throw new Exception("[!] Jproxyson need proxy. Proxy list : ".print_r($this->proxys)." \n");
			}

			if( empty($this->dataRequest["url"]) ){
				throw new Exception("[!] Jproxyson need url to send request. \n");
			}

			if( empty($this->dataRequest["id"]) ){
				if( $this->debug == true ){
					echo "[-] Id not passed ... cookie not create. \n";
				}
			}

		}

	}

?>