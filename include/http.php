<?php

 class http_request {

 	function send_request($url, $fields=null) {

		//Code for curl function from http://php.net/manual/en/book.curl.php#117138

		$curl = curl_init($url);

		//If needing an HTTP/S Proxy
    if (app_config::UseProxy == true)
    {
  		$proxy = '';
  		$proxyauth = '';
  		curl_setopt($curl, CURLOPT_PROXY, $proxy);
  		curl_setopt($curl, CURLOPT_PROXYUSERPWD, $proxyauth);
  		curl_setopt($curl, CURLOPT_PROXYAUTH, CURLAUTH_NTLM);
    }

		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_VERBOSE, 1);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

		if(isset($auth)){
			curl_setopt($curl, CURLOPT_USERPWD, "$auth");
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		}

		if($fields){
			$fields_string = http_build_query($fields);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
		}

		$response = curl_exec($curl);



		if ($response === false) {

			return array("body"=>null, "headers"=>null);

		} else {

			$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$header_string = substr($response, 0, $header_size);
			$body = substr($response, $header_size);

			$header_rows = explode(PHP_EOL, $header_string);
			$header_rows = array_filter($header_rows, "trim");
			$i=0;
			foreach((array)$header_rows as $hr){
				$colonpos = strpos($hr, ':');
				$key = $colonpos !== false ? substr($hr, 0, $colonpos) : (int)$i++;
				$headers[$key] = $colonpos !== false ? trim(substr($hr, $colonpos+1)) : $hr;
			}

			foreach((array)$headers as $key => $val){
				$vals = explode(';', $val);
				if(count($vals) >= 2){
					unset($headers[$key]);
					$j=0;
					foreach($vals as $vk => $vv){
						$equalpos = strpos($vv, '=');
						$vkey = $equalpos !== false ? trim(substr($vv, 0, $equalpos)) : (int)$j++;
						$headers[$key][$vkey] = $equalpos !== false ? trim(substr($vv, $equalpos+1)) : $vv;
					}
				}
				}

			curl_close($curl);

			return array("body"=>$body, "headers"=>$headers);
		}


	 }

	 function test_response($headers) {

		$allowed_return_codes = array(
		 "100", "200", "301", "302"
		);

	 	list($resp_proto, $resp_code, $resp_text) = explode(" ", $headers[0]);

		if (in_array($resp_code, $allowed_return_codes)) {
		 return true;
		}

		return false;

	 }

 }

 $this->http = new http_request();

?>
