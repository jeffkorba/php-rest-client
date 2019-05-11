<?php
class RestClient {

	private $defaults = [
		'options' => [
			'username' => null,
			'password' => null,
			'timeout' => 60, // 0 === infinite
			'responseType' => 'object', // object || array || json
			'contentType' => 'application/json', // multipart/form-data
			'authenticate' => false,
			'baseUrl' => null
		],
		'headers' => [
			'Content-Type: application/json'
		]
	];

	public function __construct ($defaults = []) {

		$this->setDefaults($defaults);
	}

	public function setDefaults ($defaults) {

		if (isset($defaults['options'])) {

			$this->setOptions($defaults['options']);
		}

		if (isset($defaults['headers'])) {

			$this->setHeaders($defaults['headers']);
		}
	}

	public function setOptions ($options) {

		$this->defaults['options'] = array_merge($this->defaults['options'], $options);
	}

	public function setHeaders ($headers) {

		$this->defaults['headers'] = $headers;
	}

	public function setCredentials ($username, $password) {

		$this->defaults['options']['username'] = $username;
		$this->defaults['options']['password'] = $password;
	}

	private function execute ($method, $uri, $data, $config) {

		$config = array_merge($this->defaults, $config);

		$curl = curl_init();

		if ($method === 'put' || $method === 'patch' || $method === 'delete') {

			$options[CURLOPT_CUSTOMREQUEST] = $method;
		}

		if ($method === 'post' || $method === 'put' || $method === 'patch') {

			if ($method === 'post') {

				$options[CURLOPT_POST] = true;
			}

			if (!empty($data)) {

				if ($config['options']['contentType'] === 'application/json') {

					$options[CURLOPT_POSTFIELDS] = json_encode($data);
				}
				else {
	
					$options[CURLOPT_POSTFIELDS] = $data;
				}
			}
		}

		if ($config['options']['authenticate'] && !empty($config['options']['username']) && !empty($config['options']['password'])) {

			$options[CURLOPT_USERPWD] = $config['options']['username'] . ':' . $config['options']['password'];
		}

		$options[CURLOPT_URL] = empty($config['options']['baseUrl']) ? $uri : $config['options']['baseUrl'] . $uri;
		$options[CURLOPT_HTTPHEADER] = $config['headers'];
		$options[CURLOPT_RETURNTRANSFER] = true;
		$options[CURLOPT_TIMEOUT] = $config['options']['timeout'];

		curl_setopt_array($curl, $options);

		$response = curl_exec($curl);

		//var_dump($response);

		curl_close($curl);

		if ($config['options']['responseType'] === 'object') {

			$response = json_decode($response);
		}
		else if ($config['options']['responseType'] === 'array') {

			$response = json_decode($response, true);
		}

		return $response;
	}

	public function get ($uri, $data = [], $config = []) {

		return $this->execute('get', $uri, $data, $config);
	}

	public function post ($uri, $data = [], $config = []) {

		return $this->execute('post', $uri, $data, $config);
	}

	public function put ($uri, $data = [], $config = []) {

		return $this->execute('put', $uri, $data, $config);
	}

	public function patch ($uri, $data = [], $config = []) {

		return $this->execute('patch', $uri, $data, $config);
	}

	public function delete ($uri, $data = [], $config = []) {

		return $this->execute('delete', $uri, $data, $config);
	}
}
