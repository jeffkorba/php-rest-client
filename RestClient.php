<?php
class RestClient {

	private $defaults = [
		'url' => null,
		'baseUrl' => null,
		'method' => 'GET'
		'headers' => [], // Content-Type: application/json || multipart/form-data || application/x-www-form-urlencoded || 'text/plain'
		'data' => [],
		'params' => [],
		'authorization' => false,
		'authentication' => false,
		'credentials' => [
			'username' => null,
			'password' => null
		],
		'responseType' => 'array', // array || object
		'responseEncoding' => 'utf8',
		'maxRedirects' => 3,
		'maxContentLength' => 2000,
		'timeout' => 60 // 0 === infinite
	];

	private $curl;

	public function __construct ($config = []) {

		$this->defaults = array_merge($this->defaults, $config);

		$this->curl = curl_init();
	}

	public function setOption($option, $value) {

		if (isset($this->default[$option])) {

			$this->default[$option] = $value;
		}
	}

	public function setHeader ($header, $value) {

		$this->default['headers'][$header] = $value;

		return $this;
	}

	public function setHeaders ($headers) {

		$this->default['headers'] = $headers;

		return $this;
	}

	private function formatHeaders ($headers) {

		$formattedHeaders = [];

		foreach ($headers as $key => $value) {

			$formattedHeaders[] = $key . ': ' . $value;
		}

		return $formattedHeaders;
	}

	private function formatResponse ($response, $type) {

		if ($type === 'array') {

			return json_decode($response, true);
		}
		else if ($type === 'object') {

			return json_decode($response);
		}
		else {

			return $response;
		}
	}

	public function get ($url, $config = []) {

		return $this->execute('get', $url, $data = [], $config);
	}

	public function post ($url, $data = [], $config = []) {

		$config['headers']['Content-Type'] = 'multipart/form-data'; // default

		return $this->execute('post', $url, $data, $config);
	}

	public function put ($url, $data = [], $config = []) {

		$config['headers']['Content-Type'] = 'application/x-www-form-urlencoded'; // default

		return $this->execute('put', $url, $data, $config);
	}

	public function patch ($url, $data = [], $config = []) {

		$config['headers']['Content-Type'] = 'application/x-www-form-urlencoded'; // default

		return $this->execute('patch', $url, $data, $config);
	}

	public function delete ($url, $config = []) {

		return $this->execute('delete', $url, $data = [], $config);
	}

	private function execute ($method, $url, $data, $config) {

		$config = array_merge($this->defaults, $config);

		$options = [
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_URL => empty($config['baseUrl']) ? $url : $config['baseUrl'] . $url,
			CURLOPT_HTTPHEADER => $this->formatHeaders($config['headers']),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => $config['timeout']
		];

		if ($config['maxRedirects'] > 0) {

			$options[CURLOPT_FOLLOWLOCATION] = true;
			$options[CURLOPT_MAXREDIRS] = $config['maxRedirects'];
		}

		if ($config['authentication'] && !empty($config['credentials']['username']) && !empty($config['credentials']['password'])) {

			$options[CURLOPT_USERPWD] = $config['credentials']['username'] . ':' . $config['credentials']['password'];
		}

		if (!empty($data)) {

			if ($config['headers']['Content-Type'] === 'application/json') {

				$options[CURLOPT_POSTFIELDS] = json_encode($data);
			}
			else if ($config['headers']['Content-Type'] === 'application/x-www-form-urlencoded') {

				$options[CURLOPT_POSTFIELDS] = http_build_query($data);
			}
			else {

				$options[CURLOPT_POSTFIELDS] = $data;
			}
		}

		curl_setopt_array($curl, $options);

		$response = curl_exec($this->curl);

		$this->close();

		return $this->formatResponse($response, $config['responseType']);
	}

	private function close () {

		curl_close($this->curl);
	}
}
