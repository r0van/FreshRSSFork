<?php
declare(strict_types=1);

/**
 * MINZ - Copyright 2011 Marien Fressinaud
 * Sous licence AGPL3 <http://www.gnu.org/licenses/>
*/

/**
 * Request représente la requête http
 */
class Minz_Request {

	private static string $controller_name = '';
	private static string $action_name = '';
	/** @var array<string,mixed> */
	private static array $params = [];

	private static string $default_controller_name = 'index';
	private static string $default_action_name = 'index';

	/** @var array{c?:string,a?:string,params?:array<string,mixed>} */
	private static array $originalRequest = [];

	/**
	 * Getteurs
	 */
	public static function controllerName(): string {
		return self::$controller_name;
	}
	public static function actionName(): string {
		return self::$action_name;
	}
	/** @return array<string,mixed> */
	public static function params(): array {
		return self::$params;
	}

	/**
	 * Read the URL parameter
	 * @param string $key Key name
	 * @param mixed $default default value, if no parameter is given
	 * @param bool $specialchars `true` to return special characters, `false` (default) to XML-encode them
	 * @return mixed value of the parameter
	 * @deprecated use typed versions instead
	 */
	public static function param(string $key, mixed $default = false, bool $specialchars = false): mixed {
		if (isset(self::$params[$key])) {
			$p = self::$params[$key];
			if (is_string($p) || is_array($p)) {
				return $specialchars ? $p : Minz_Helper::htmlspecialchars_utf8($p);
			} else {
				return $p;
			}
		} else {
			return $default;
		}
	}

	public static function hasParam(string $key): bool {
		return isset(self::$params[$key]);
	}

	/**
	 * @param bool $plaintext `true` to return special characters without any escaping (unsafe), `false` (default) to XML-encode them
	 * @return array<string|int,string|array<int|string,string|int|bool>>
	 */
	public static function paramArray(string $key, bool $plaintext = false): array {
		if (empty(self::$params[$key]) || !is_array(self::$params[$key])) {
			return [];
		}
		$result = [];
		foreach (self::$params[$key] as $k => $v) {
			if (is_string($v)) {
				$result[$k] = $v;
			} elseif (is_array($v)) {
				$vs = [];
				foreach ($v as $k2 => $v2) {
					if (is_string($v2) || is_int($v2) || is_bool($v2)) {
						$vs[$k2] = $v2;
					}
				}
				$result[$k] = $vs;
			}
		}
		return $plaintext ? $result : Minz_Helper::htmlspecialchars_utf8($result);
	}

	/**
	 * @param bool $plaintext `true` to return special characters without any escaping (unsafe), `false` (default) to XML-encode them
	 * @return list<string>
	 */
	public static function paramArrayString(string $key, bool $plaintext = false): array {
		if (empty(self::$params[$key]) || !is_array(self::$params[$key])) {
			return [];
		}
		$result = array_filter(self::$params[$key], 'is_string');
		$result = array_values($result);
		return $plaintext ? $result : Minz_Helper::htmlspecialchars_utf8($result);
	}

	public static function paramTernary(string $key): ?bool {
		if (isset(self::$params[$key])) {
			$p = self::$params[$key];
			$tp = is_string($p) ? trim($p) : true;
			if ($tp === '' || $tp === 'null') {
				return null;
			} elseif ($p == false || $tp == '0' || $tp === 'false' || $tp === 'no') {
				return false;
			}
			return true;
		}
		return null;
	}

	public static function paramBoolean(string $key): bool {
		if (null === $value = self::paramTernary($key)) {
			return false;
		}
		return $value;
	}

	public static function paramIntNull(string $key): ?int {
		return is_numeric(self::$params[$key] ?? null) ? (int)self::$params[$key] : null;
	}

	public static function paramInt(string $key): int {
		if (!empty(self::$params[$key]) && is_numeric(self::$params[$key])) {
			return (int)self::$params[$key];
		}
		return 0;
	}

	/**
	 * @param bool $plaintext `true` to return special characters without any escaping (unsafe), `false` (default) to XML-encode them
	 */
	public static function paramStringNull(string $key, bool $plaintext = false): ?string {
		if (isset(self::$params[$key])) {
			$s = self::$params[$key];
			if (is_string($s)) {
				$s = trim($s);
				return $plaintext ? $s : htmlspecialchars($s, ENT_COMPAT, 'UTF-8');
			}
			if (is_int($s) || is_bool($s)) {
				return (string)$s;
			}
		}
		return null;
	}

	/**
	 * @param bool $plaintext `true` to return special characters without any escaping (unsafe), `false` (default) to XML-encode them
	 */
	public static function paramString(string $key, bool $plaintext = false): string {
		return self::paramStringNull($key, $plaintext) ?? '';
	}

	/**
	 * Extract text lines to array.
	 *
	 * It will return an array where each cell contains one line of a text. The new line
	 * character is used to break the text into lines. This method is well suited to use
	 * to split textarea content.
	 * @param bool $plaintext `true` to return special characters without any escaping (unsafe), `false` (default) to XML-encode them
	 * @return list<string>
	 */
	public static function paramTextToArray(string $key, bool $plaintext = false): array {
		if (isset(self::$params[$key]) && is_string(self::$params[$key])) {
			$result = preg_split('/\R/u', self::$params[$key]) ?: [];
			return $plaintext ? $result : Minz_Helper::htmlspecialchars_utf8($result);
		}
		return [];
	}

	public static function defaultControllerName(): string {
		return self::$default_controller_name;
	}
	public static function defaultActionName(): string {
		return self::$default_action_name;
	}
	/** @return array{c:string,a:string,params:array<string,mixed>} */
	public static function currentRequest(): array {
		return [
			'c' => self::$controller_name,
			'a' => self::$action_name,
			'params' => self::$params,
		];
	}

	/** @return array{c?:string,a?:string,params?:array<string,mixed>} */
	public static function originalRequest(): array {
		return self::$originalRequest;
	}

	/**
	 * @param array<string,mixed>|null $extraParams
	 * @return array{c:string,a:string,params:array<string,mixed>}
	 */
	public static function modifiedCurrentRequest(?array $extraParams = null): array {
		unset(self::$params['ajax']);
		$currentRequest = self::currentRequest();
		if (null !== $extraParams) {
			$currentRequest['params'] = array_merge($currentRequest['params'], $extraParams);
		}
		return $currentRequest;
	}

	/**
	 * Setteurs
	 */
	public static function _controllerName(string $controller_name): void {
		self::$controller_name = ctype_alnum($controller_name) ? $controller_name : '';
	}

	public static function _actionName(string $action_name): void {
		self::$action_name = ctype_alnum($action_name) ? $action_name : '';
	}

	/** @param array<string,mixed> $params */
	public static function _params(array $params): void {
		self::$params = $params;
	}

	public static function _param(string $key, ?string $value = null): void {
		if ($value === null) {
			unset(self::$params[$key]);
		} else {
			self::$params[$key] = $value;
		}
	}

	/**
	 * Initialise la Request
	 */
	public static function init(): void {
		self::_params(array_filter($_GET, 'is_string', ARRAY_FILTER_USE_KEY));
		self::initJSON();
	}

	public static function is(string $controller_name, string $action_name): bool {
		return self::$controller_name === $controller_name &&
			self::$action_name === $action_name;
	}

	/**
	 * Return true if the request is over HTTPS, false otherwise (HTTP)
	 */
	public static function isHttps(): bool {
		$header = is_string($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : '';
		if ('' !== $header) {
			return 'https' === strtolower($header);
		}
		return 'on' === ($_SERVER['HTTPS'] ?? '');
	}

	/**
	 * Try to guess the base URL from $_SERVER information
	 *
	 * @return string base url (e.g. http://example.com)
	 */
	public static function guessBaseUrl(): string {
		$protocol = self::extractProtocol();
		$host = self::extractHost();
		$port = self::extractPortForUrl();
		$prefix = self::extractPrefix();
		$path = self::extractPath();

		return filter_var("{$protocol}://{$host}{$port}{$prefix}{$path}", FILTER_SANITIZE_URL) ?: '';
	}

	private static function extractProtocol(): string {
		return self::isHttps() ? 'https' : 'http';
	}

	private static function extractHost(): string {
		$host = is_string($_SERVER['HTTP_X_FORWARDED_HOST'] ?? null) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : '';
		if ($host !== '') {
			return parse_url("http://{$host}", PHP_URL_HOST) ?: 'localhost';
		}
		$host = is_string($_SERVER['HTTP_HOST'] ?? null) ? $_SERVER['HTTP_HOST'] : '';
		if ($host !== '') {
			// Might contain a port number, and mind IPv6 addresses
			return parse_url("http://{$host}", PHP_URL_HOST) ?: 'localhost';
		}
		$host = is_string($_SERVER['SERVER_NAME'] ?? null) ? $_SERVER['SERVER_NAME'] : '';
		if ($host !== '') {
			return $host;
		}
		return 'localhost';
	}

	private static function extractPort(): int {
		$port = is_numeric($_SERVER['HTTP_X_FORWARDED_PORT'] ?? null) ? $_SERVER['HTTP_X_FORWARDED_PORT'] : '';
		if ($port !== '') {
			return intval($port);
		}
		$proto = is_string($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : '';
		if ($proto !== '') {
			return 'https' === strtolower($proto) ? 443 : 80;
		}
		$port = is_numeric($_SERVER['SERVER_PORT'] ?? null) ? $_SERVER['SERVER_PORT'] : '';
		if ($port !== '') {
			return intval($port);
		}
		return self::isHttps() ? 443 : 80;
	}

	private static function extractPortForUrl(): string {
		if (self::isHttps() && 443 !== $port = self::extractPort()) {
			return ":{$port}";
		}
		if (!self::isHttps() && 80 !== $port = self::extractPort()) {
			return ":{$port}";
		}
		return '';
	}

	private static function extractPrefix(): string {
		$prefix = is_string($_SERVER['HTTP_X_FORWARDED_PREFIX'] ?? null) ? $_SERVER['HTTP_X_FORWARDED_PREFIX'] : '';
		if ($prefix !== '') {
			return rtrim($prefix, '/ ');
		}
		return '';
	}

	private static function extractPath(): string {
		$path = is_string($_SERVER['REQUEST_URI'] ?? null) ? $_SERVER['REQUEST_URI'] : '';
		if ($path !== '') {
			$path = parse_url($path, PHP_URL_PATH) ?: '';
			return substr($path, -1) === '/' ? rtrim($path, '/') : dirname($path);
		}
		return '';
	}

	/**
	 * Return the base_url from configuration
	 * @throws Minz_ConfigurationException
	 */
	public static function getBaseUrl(): string {
		$conf = Minz_Configuration::get('system');
		$url = trim($conf->base_url, ' /\\"');
		return filter_var($url, FILTER_SANITIZE_URL) ?: '';
	}

	/**
	 * Test if a given server address is publicly accessible.
	 *
	 * Note: for the moment it tests only if address is corresponding to a
	 * localhost address.
	 *
	 * @param string $address the address to test, can be an IP or a URL.
	 * @return bool true if server is accessible, false otherwise.
	 * @todo improve test with a more valid technique (e.g. test with an external server?)
	 */
	public static function serverIsPublic(string $address): bool {
		if (strlen($address) < strlen('http://a.bc')) {
			return false;
		}
		$host = parse_url($address, PHP_URL_HOST);
		if (!is_string($host)) {
			return false;
		}

		$is_public = !in_array($host, [
			'localhost',
			'localhost.localdomain',
			'[::1]',
			'ip6-localhost',
			'localhost6',
			'localhost6.localdomain6',
		], true);

		if ($is_public) {
			$is_public &= !preg_match('/^(10|127|172[.]16|192[.]168)[.]/', $host);
			$is_public &= !preg_match('/^(\[)?(::1$|fc00::|fe80::)/i', $host);
		}

		return (bool)$is_public;
	}

	private static function requestId(): string {
		if (!is_string($_GET['rid'] ?? null) || !ctype_xdigit($_GET['rid'])) {
			$_GET['rid'] = uniqid();
		}
		return $_GET['rid'];
	}

	private static function setNotification(string $type, string $content, string $notificationName = ''): void {
		Minz_Session::lock();
		$requests = Minz_Session::paramArray('requests');
		$requests[self::requestId()] = [
				'time' => time(),
				'notification' => [ 'type' => $type, 'content' => $content, 'notificationName' => $notificationName ],
			];
		Minz_Session::_param('requests', $requests);
		Minz_Session::unlock();
	}

	public static function setGoodNotification(string $content, string $notificationName = ''): void {
		self::setNotification('good', $content, $notificationName);
	}

	public static function setBadNotification(string $content, string $notificationName = ''): void {
		self::setNotification('bad', $content, $notificationName);
	}

	/**
	 * @param $pop true (default) to remove the notification, false to keep it.
	 * @return array{type:string,content:string,notificationName:string}|null
	 */
	public static function getNotification(bool $pop = true): ?array {
		$notif = null;
		Minz_Session::lock();
		/** @var array<string,array{time:int,notification:array{type:string,content:string,notificationName:string}}> */
		$requests = Minz_Session::paramArray('requests');
		if (!empty($requests)) {
			//Delete abandoned notifications
			$requests = array_filter($requests, static function (array $r) { return $r['time'] > time() - 3600; });

			$requestId = self::requestId();
			if (!empty($requests[$requestId]['notification'])) {
				$notif = $requests[$requestId]['notification'];
				if ($pop) {
					unset($requests[$requestId]);
				}
			}
			Minz_Session::_param('requests', $requests);
		}
		Minz_Session::unlock();
		return $notif;
	}

	/**
	 * Restart a request
	 * @param array{c?:string,a?:string,params?:array<string,mixed>} $url an array presentation of the URL to route to
	 * @param bool $redirect If true, uses an HTTP redirection, and if false (default), performs an internal dispatcher redirection.
	 * @throws Minz_ConfigurationException
	 */
	public static function forward(array $url = [], bool $redirect = false): void {
		if (empty(Minz_Request::originalRequest())) {
			self::$originalRequest = $url;
		}

		$url = Minz_Url::checkControllerUrl($url);
		$url['params']['rid'] = self::requestId();

		if ($redirect) {
			header('Location: ' . Minz_Url::display($url, 'php', 'root'));
			exit();
		} else {
			self::_controllerName($url['c']);
			self::_actionName($url['a']);
			$merge = array_merge(self::$params, $url['params']);
			self::_params($merge);
			Minz_Dispatcher::reset();
		}
	}

	/**
	 * Wrappers good notifications + redirection
	 * @param string $msg notification content
	 * @param array{c?:string,a?:string,params?:array<string,mixed>} $url url array to where we should be forwarded
	 */
	public static function good(string $msg, array $url = [], string $notificationName = ''): void {
		Minz_Request::setGoodNotification($msg, $notificationName);
		Minz_Request::forward($url, true);
	}

	/**
	 * Wrappers bad notifications + redirection
	 * @param string $msg notification content
	 * @param array{c?:string,a?:string,params?:array<string,mixed>} $url url array to where we should be forwarded
	 */
	public static function bad(string $msg, array $url = [], string $notificationName = ''): void {
		Minz_Request::setBadNotification($msg, $notificationName);
		Minz_Request::forward($url, true);
	}

	/**
	 * Allows receiving POST data as application/json
	 */
	private static function initJSON(): void {
		if (!str_starts_with(self::extractContentType(), 'application/json')) {
			return;
		}
		$ORIGINAL_INPUT = file_get_contents('php://input', false, null, 0, 1048576);
		if ($ORIGINAL_INPUT == false) {
			return;
		}
		if (!is_array($json = json_decode($ORIGINAL_INPUT, true))) {
			return;
		}

		foreach ($json as $k => $v) {
			if (!isset($_POST[$k])) {
				$_POST[$k] = $v;
			}
		}
	}

	private static function extractContentType(): string {
		$contentType = is_string($_SERVER['CONTENT_TYPE'] ?? null) ? $_SERVER['CONTENT_TYPE'] : '';
		return strtolower(trim($contentType));
	}

	public static function isPost(): bool {
		return 'POST' === ($_SERVER['REQUEST_METHOD'] ?? '');
	}

	/**
	 * @return list<string>
	 */
	public static function getPreferredLanguages(): array {
		$acceptLanguage = is_string($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
		if (preg_match_all('/(^|,)\s*(?P<lang>[^;,]+)/', $acceptLanguage, $matches) > 0) {
			return $matches['lang'];
		}
		return ['en'];
	}
}
