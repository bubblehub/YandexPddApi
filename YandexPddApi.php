<?php

class YandexPddApi {

	// Токен (необходим для работы с API)
	// Получить можно тут: https://pddimp.yandex.ru/get_token.xml?domain_name=example.com

	private $token;

	public $dieOnErrors = false; // debug mode

	public $useCURL = true;

	public function __construct($token) {

		$this->token = $token;
	}

	// Генерация пароля

	protected function generate_password() {

		return mt_rand(111111, 999999);
	}

	private function query($url) {

		if (!$this->useCURL) {
			return file_get_contents($url);
		} else {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_USERAGENT, "YandexPddApi");
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$result = curl_exec($ch);
			curl_close($ch);

			return $result;
		}
	}

	private function parse_query($query) {

		$parsed_query = [];

		switch (true) {

			case strpos($query, "bad_token"):
				$parsed_query['status']	= "error";
				$parsed_query['info']	= "bad_token";
				break;

			case strpos($query, "no_auth"):
				$parsed_query['status']	= "error";
				$parsed_query['info']	= "no_auth";
				break;

			case strpos($query, "bad_domain"):
				$parsed_query['status']	= "error";
				$parsed_query['info']	= "bad_domain";
				break;

			case strpos($query, "occupied"):
				$parsed_query['status']	= "error";
				$parsed_query['info']	= "occupied";
				break;

			case strpos($query, "badlogin"):
				$parsed_query['status']	= "error";
				$parsed_query['info']	= "badlogin";
				break;

			case strpos($query, "passwd-empty"):
				$parsed_query['status']	= "error";
				$parsed_query['info']	= "passwd-empty";
				break;

			case strpos($query, "passwd-tooshort"):
				$parsed_query['status']	= "error";
				$parsed_query['info']	= "passwd-tooshort";
				break;

			case strpos($query, "passwd-toolong"):
				$parsed_query['status']	= "error";
				$parsed_query['info']	= "passwd-toolong";
				break;

			case strpos($query, "passwd-badpasswd"):
				$parsed_query['status'] = "error";
				$parsed_qeury['info']	= "passwd-badpasswd";
				break;

			case strpos($query, "ok uid"):
				$parsed_query['status']	= "success";
				$parsed_query['info']	= "ok";
				break;

			case strpos($query, "no_user"):
				$parsed_query['status']	= "error";
				$parsed_query['info']	= "no_user";
				break;

			case strpos($query, "ok new_messages"):
				$parsed_query['status']	= "success";
				preg_match_all('/<ok new_messages=\"([0-9]+)\"\/>/', $query, $matches);
				$parsed_query['info']	= $matches;
				break;

			case strpos($query, "not_found"):
				$parsed_query['status']	= "error";
				$parsed_query['info']	= "not_found";
				break;

			case strpos($query, "<user><login>"):
				$parsed_query['status']	= "success";
				$p = xml_parser_create();
				xml_parse_into_struct($p, $query, $vals, $index);
				xml_parser_free($p);
				$parsed_query['info']	= $vals;
				break;

			case strpos($query, "unknown_sex"):
				$parsed_query['status']	= "error";
				$parsed_query['info']	= "unknown_sex";
				break;

			case strpos($query, "no_address"):
				$parsed_query['status']	= "error";
				$parsed_query['info']	= "no_address";
				break;

			case strpos($query, "unknown"):
				$parsed_query['status']	= "error";
				$parsed_query['info']	= "unknown";
				break;

			case strpos($query, "<ok/>"):
				$parsed_query['status']	= "success";
				$parsed_query['info']	= "ok";
				break;

			case strpos($query, "<result>exists</result>"):
				$parsed_query['status']	= "success";
				$parsed_query['info']	= "exists";
				break;

			case strpos($query, "<email><name>"):
				$parsed_query['status']	= "success";
				$p = xml_parser_create();
				xml_parse_into_struct($p, $query, $vals, $index);
				xml_parser_free($p);
				$parsed_query['info']	= $vals;
				break;
		}

		if ($this->dieOnErrors && $parsed_query['status'] == "error") {
			die(var_dump($parsed_query));
		} elseif (!$parsed_query) {
			return $query;
		} else {
			return $parsed_query;
		}
	}

	// Метод предназначен для регистрации пользователя
	// Принимает логин и пароль
	// Возвращает массив с информацией о запросе, именем пользователя

	public function reg_user_token($u_login, $u_password = null) {

		// Генерация пароля при его отсутствии

		!isset($u_password) AND $u_password = $this->generate_password();

		$query = $this->query("https://pddimp.yandex.ru/reg_user_token.xml?token={$this->token}&u_login={$u_login}&u_password={$u_password}");

		$result = $this->parse_query($query);
		$result['u_login'] = $u_login;

		return $result;
	}

	// Метод позволяет получить количество непрочитанных писем

	public function get_mail_info($u_login) {

		$query	= $this->query("https://pddimp.yandex.ru/get_mail_info.xml?token={$this->token}&login={$u_login}");
		$result	= $this->parse_query($query);
		$result['u_login'] = $u_login;

		return $result;
	}

	// Метод позволяет получить данные пользователя

	public function get_user_info($u_login) {

		$query = $this->query("https://pddimp.yandex.ru/get_user_info.xml?token={$this->token}&login={$u_login}");
		$result	= $this->parse_query($query);
		$result['u_login'] = $u_login;

		return $result;
	}

	// Метод позволяет изменить персональные данные пользователя: имя, фамилию, пол, пароль, секретный вопрос и ответ на секретный вопрос

	public function edit_user($u_login, array $u_settings) {

		$query = $this->query("https://pddimp.yandex.ru/edit_user.xml?token={$this->token}&login={$u_login}&password={$u_settings['password']}&domain_name={$u_settings['domain_name']}&iname={$u_settings['iname']}&fname={$u_settings['fname']}&sex={$u_settings['sex']}&hintq={$u_settings['hintq']}&hinta={$u_settings['hinta']}");

		$result	= $this->parse_query($query);
		$result['u_login'] = $u_login;

		return $result;
	}

	// Метод позволяет установить переадресацию для заданного пользователя

	public function set_forward($u_login, $address, $copy = "no") {

		$query = $this->query("https://pddimp.yandex.ru/set_forward.xml?token={$this->token}&login={$u_login}&address={$address}&copy={$copy}");

		$result	= $this->parse_query($query);
		$result['u_login'] = $u_login;

		return $result;
	}

	// Метод позволяет получить список переадресаций и фильтров

	public function get_forward_list($u_login) {

		$query = $this->query("https://pddimp.yandex.ru/get_forward_list.xml?token={$this->token}&login={$u_login}");

		$result	= $this->parse_query($query);
		$result['u_login'] = $u_login;

		return $result;
	}

	// Метод позволяет удалить переадресацию или фильтр

	public function delete_forward($u_login, $filter_id) {

		$query = $this->query("https://pddimp.yandex.ru/delete_forward.xml?token={$this->token}&login={$u_login}&filter_id={$filter_id}");

		$result	= $this->parse_query($query);
		$result['u_login'] = $u_login;

		return $result;
	}

	// Метод предназначен для удаления пользователя

	public function delete_user($u_login) {

		$query = $this->query("https://pddimp.yandex.ru/delete_user.xml?token={$this->token}&login={$u_login}");

		$result	= $this->parse_query($query);
		$result['u_login'] = $u_login;

		return $result;
	}

	// Метод позволяет проверить существование пользователя

	public function check_user($u_login) {

		$query = $this->query("https://pddimp.yandex.ru/check_user.xml?token={$this->token}&login={$u_login}");

		$result	= $this->parse_query($query);
		$result['u_login'] = $u_login;

		return $result;
	}

	// Метод позволяет получить список почтовых ящиков

	public function get_domain_users($on_page = 100, $page = 0) {

		$query = $this->query("https://pddimp.yandex.ru/get_domain_users.xml?token={$this->token}&on_page={$on_page}&page={$page}");
		$result	= $this->parse_query($query);

		return $result;
	}
}