<?php namespace Somepony\YandexPddApi;

class API
{
	/**
	 * Получить необходимо здесь: https://pddimp.yandex.ru/api2/admin/get_token
	 * @var string
	 */
	private $pdd_token;
	private $oauth_token;

	public $query;

	public $request_as;

	public function __construct($pdd_token, $oauth_token = null)
	{
		$this->pdd_token = $pdd_token;
		$this->oauth_token = $oauth_token;
		\Requests::register_autoloader();
	}

	public function registrar()
	{
		$this->request_as = 'registrar';
		return $this;
	}

	public function admin()
	{
		$this->request_as = 'admin';
		return $this;
	}

	public function request($method, $params = null)
	{
		if (!is_null($params) or !empty($params) && is_array($params))
		{
			foreach ($params as $param => $value)
			{
				$prefix = ($value == reset($params)) ? '?' : '&';
				$parameters .= sprintf("%s%s=%s", $prefix, $param, urlencode($value));
			}
		}
		else
		{
			throw new \Exception('Method request() must have an array argument.');
		}

		$this->query = null;

		if (is_null($this->request_as))
			$this->request_as = 'admin';

		# https://tech.yandex.ru/market/partner/doc/dg/concepts/error-codes-docpage/

		$response = \Requests::post("https://pddimp.yandex.ru/api2/{$this->request_as}{$method}{$parameters}", ['Accept' => 'application/json', 'PddToken' => $this->pdd_token, 'Authorization' => $this->oauth_token]);

		if ($response->status_code == 405)
		{
			$response = \Requests::get("https://pddimp.yandex.ru/api2/{$this->request_as}{$method}{$parameters}", ['Accept' => 'application/json', 'PddToken' => $this->pdd_token, 'Authorization' => $this->oauth_token]);
		}

		switch ($response->status_code) {
			case 200:
				return json_decode($response->body, true);
				break;

			case 405:
				throw new \Exception('Method Not Allowed');
				break;

			default:
				throw new \Exception($response->status_code);
				break;
		}

		$this->request_as = null;
	}

	public function __call($method, $arguments)
	{
		if (empty($arguments))
		{
			$this->query .= "/{$method}";
			return $this;
		}

		if (!is_array($arguments))
		{
			throw new \Exception("Method {$method}() must have an array argument.");
		}

		if (ctype_upper($method[0]) && preg_match_all('/([A-Z]{1}[a-z]+)/', $method))
		{
			throw new \Exception('Method names must be declared in camelCase.');
		}
		elseif (!ctype_upper($method[0]) && preg_match_all('/([A-Z]{1}[a-z]+)/', $method))
		{
			$pieces = preg_split('/(?=[A-Z])/', $method);
			foreach ($pieces as $word)
			{
				if (!ctype_upper($word[0]))
				{
					$this->query .= "/{$word}";
				}
				else
				{
					$this->query .= "_{$word}";
				}
			}
		}
		elseif (!ctype_upper($method[0]) && !preg_match_all('/([A-Z]{1}[a-z]+)/', $method))
		{
			$this->query .= "/{$method}";
		}
		else
		{
			throw new \Exception('Something went wrong. Bad method name i guess.');
		}

		return $this->request(strtolower($this->query), $arguments[0]);
	}
}