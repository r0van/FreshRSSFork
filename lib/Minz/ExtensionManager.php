<?php
declare(strict_types=1);

/**
 * An extension manager to load extensions present in CORE_EXTENSIONS_PATH and THIRDPARTY_EXTENSIONS_PATH.
 *
 * @todo see coding style for methods!!
 */
final class Minz_ExtensionManager {

	private static string $ext_metaname = 'metadata.json';
	private static string $ext_entry_point = 'extension.php';
	/** @var array<string,Minz_Extension> */
	private static array $ext_list = [];
	/** @var array<string,Minz_Extension> */
	private static array $ext_list_enabled = [];
	/** @var array<string,bool> */
	private static array $ext_auto_enabled = [];

	/**
	 * List of available hooks. Please keep this list sorted.
	 * @var array<string,array{'list':array<callable>,'signature':'NoneToNone'|'NoneToString'|'OneToOne'|'PassArguments'}>
	 */
	private static array $hook_list = [
		'check_url_before_add' => [	// function($url) -> Url | null
			'list' => [],
			'signature' => 'OneToOne',
		],
		'entries_favorite' => [	// function(array $ids, bool $is_favorite): void
			'list' => [],
			'signature' => 'PassArguments',
		],
		'entry_auto_read' => [	// function(FreshRSS_Entry $entry, string $why): void
			'list' => [],
			'signature' => 'PassArguments',
		],
		'entry_auto_unread' => [	// function(FreshRSS_Entry $entry, string $why): void
			'list' => [],
			'signature' => 'PassArguments',
		],
		'entry_before_display' => [	// function($entry) -> Entry | null
			'list' => [],
			'signature' => 'OneToOne',
		],
		'entry_before_insert' => [	// function($entry) -> Entry | null
			'list' => [],
			'signature' => 'OneToOne',
		],
		'feed_before_actualize' => [	// function($feed) -> Feed | null
			'list' => [],
			'signature' => 'OneToOne',
		],
		'feed_before_insert' => [	// function($feed) -> Feed | null
			'list' => [],
			'signature' => 'OneToOne',
		],
		'freshrss_init' => [	// function() -> none
			'list' => [],
			'signature' => 'NoneToNone',
		],
		'freshrss_user_maintenance' => [	// function() -> none
			'list' => [],
			'signature' => 'NoneToNone',
		],
		'js_vars' => [	// function($vars = array) -> array | null
			'list' => [],
			'signature' => 'OneToOne',
		],
		'menu_admin_entry' => [	// function() -> string
			'list' => [],
			'signature' => 'NoneToString',
		],
		'menu_configuration_entry' => [	// function() -> string
			'list' => [],
			'signature' => 'NoneToString',
		],
		'menu_other_entry' => [	// function() -> string
			'list' => [],
			'signature' => 'NoneToString',
		],
		'nav_menu' => [	// function() -> string
			'list' => [],
			'signature' => 'NoneToString',
		],
		'nav_reading_modes' => [	// function($readingModes = array) -> array | null
			'list' => [],
			'signature' => 'OneToOne',
		],
		'post_update' => [	// function(none) -> none
			'list' => [],
			'signature' => 'NoneToNone',
		],
		'simplepie_after_init' => [	// function(\SimplePie\SimplePie $simplePie, FreshRSS_Feed $feed, bool $result): void
			'list' => [],
			'signature' => 'PassArguments',
		],
		'simplepie_before_init' => [	// function(\SimplePie\SimplePie $simplePie, FreshRSS_Feed $feed): void
			'list' => [],
			'signature' => 'PassArguments',
		],
	];

	/** Remove extensions and hooks from a previous initialisation */
	private static function reset(): void {
		$hadAny = !empty(self::$ext_list_enabled);
		self::$ext_list = [];
		self::$ext_list_enabled = [];
		self::$ext_auto_enabled = [];
		foreach (self::$hook_list as $hook_type => $hook_data) {
			$hadAny |= !empty($hook_data['list']);
			$hook_data['list'] = [];
			self::$hook_list[$hook_type] = $hook_data;
		}
		if ($hadAny) {
			gc_collect_cycles();
		}
	}

	/**
	 * Initialize the extension manager by loading extensions in EXTENSIONS_PATH.
	 *
	 * A valid extension is a directory containing metadata.json and
	 * extension.php files.
	 * metadata.json is a JSON structure where the only required fields are
	 * `name` and `entry_point`.
	 * extension.php should contain at least a class named <name>Extension where
	 * <name> must match with the entry point in metadata.json. This class must
	 * inherit from Minz_Extension class.
	 * @throws Minz_ConfigurationNamespaceException
	 */
	public static function init(): void {
		self::reset();

		$list_core_extensions = array_diff(scandir(CORE_EXTENSIONS_PATH) ?: [], [ '..', '.' ]);
		$list_thirdparty_extensions = array_diff(scandir(THIRDPARTY_EXTENSIONS_PATH) ?: [], [ '..', '.' ], $list_core_extensions);
		array_walk($list_core_extensions, function (&$s) { $s = CORE_EXTENSIONS_PATH . '/' . $s; });
		array_walk($list_thirdparty_extensions, function (&$s) { $s = THIRDPARTY_EXTENSIONS_PATH . '/' . $s; });

		$list_potential_extensions = array_merge($list_core_extensions, $list_thirdparty_extensions);

		$system_conf = Minz_Configuration::get('system');
		self::$ext_auto_enabled = $system_conf->extensions_enabled;

		foreach ($list_potential_extensions as $ext_pathname) {
			if (!is_dir($ext_pathname)) {
				continue;
			}
			$metadata_filename = $ext_pathname . '/' . self::$ext_metaname;

			// Try to load metadata file.
			if (!file_exists($metadata_filename)) {
				// No metadata file? Invalid!
				continue;
			}
			$meta_raw_content = file_get_contents($metadata_filename) ?: '';
			/** @var array{'name':string,'entrypoint':string,'path':string,'author'?:string,'description'?:string,'version'?:string,'type'?:'system'|'user'}|null $meta_json */
			$meta_json = json_decode($meta_raw_content, true);
			if (!is_array($meta_json) || !self::isValidMetadata($meta_json)) {
				// metadata.json is not a json file? Invalid!
				// or metadata.json is invalid (no required information), invalid!
				Minz_Log::warning('`' . $metadata_filename . '` is not a valid metadata file');
				continue;
			}

			$meta_json['path'] = $ext_pathname;

			// Try to load extension itself
			$extension = self::load($meta_json);
			if ($extension != null) {
				self::register($extension);
			}
		}
	}

	/**
	 * Indicates if the given parameter is a valid metadata array.
	 *
	 * Required fields are:
	 * - `name`: the name of the extension
	 * - `entry_point`: a class name to load the extension source code
	 * If the extension class name is `TestExtension`, entry point will be `Test`.
	 * `entry_point` must be composed of alphanumeric characters.
	 *
	 * @param array{'name':string,'entrypoint':string,'path':string,'author'?:string,'description'?:string,'version'?:string,'type'?:'system'|'user'} $meta
	 * is an array of values.
	 * @return bool true if the array is valid, false else.
	 */
	private static function isValidMetadata(array $meta): bool {
		$valid_chars = ['_'];
		return !(empty($meta['name']) || empty($meta['entrypoint']) || !ctype_alnum(str_replace($valid_chars, '', $meta['entrypoint'])));
	}

	/**
	 * Load the extension source code based on info metadata.
	 *
	 * @param array{'name':string,'entrypoint':string,'path':string,'author'?:string,'description'?:string,'version'?:string,'type'?:'system'|'user'} $info
	 * an array containing information about extension.
	 * @return Minz_Extension|null an extension inheriting from Minz_Extension.
	 */
	private static function load(array $info): ?Minz_Extension {
		$entry_point_filename = $info['path'] . '/' . self::$ext_entry_point;
		$ext_class_name = $info['entrypoint'] . 'Extension';

		include_once($entry_point_filename);

		// Test if the given extension class exists.
		if (!class_exists($ext_class_name)) {
			Minz_Log::warning("`{$ext_class_name}` cannot be found in `{$entry_point_filename}`");
			return null;
		}

		// Try to load the class.
		$extension = null;
		try {
			$extension = new $ext_class_name($info);
		} catch (Exception $e) {
			// We cannot load the extension? Invalid!
			Minz_Log::warning("Invalid extension `{$ext_class_name}`: " . $e->getMessage());
			return null;
		}

		// Test if class is correct.
		if (!($extension instanceof Minz_Extension)) {
			Minz_Log::warning("`{$ext_class_name}` is not an instance of `Minz_Extension`");
			return null;
		}

		return $extension;
	}

	/**
	 * Add the extension to the list of the known extensions ($ext_list).
	 *
	 * If the extension is present in $ext_auto_enabled and if its type is "system",
	 * it will be enabled at the same time.
	 *
	 * @param Minz_Extension $ext a valid extension.
	 */
	private static function register(Minz_Extension $ext): void {
		$name = $ext->getName();
		self::$ext_list[$name] = $ext;

		if ($ext->getType() === 'system' && !empty(self::$ext_auto_enabled[$name])) {
			self::enable($ext->getName(), 'system');
		}
	}

	/**
	 * Enable an extension so it will be called when necessary.
	 *
	 * The extension init() method will be called.
	 *
	 * @param string $ext_name is the name of a valid extension present in $ext_list.
	 * @param 'system'|'user'|null $onlyOfType only enable if the extension matches that type. Set to null to load all.
	 */
	private static function enable(string $ext_name, ?string $onlyOfType = null): void {
		if (isset(self::$ext_list[$ext_name])) {
			$ext = self::$ext_list[$ext_name];

			if ($onlyOfType !== null && $ext->getType() !== $onlyOfType) {
				// Do not enable an extension of the wrong type
				return;
			}

			self::$ext_list_enabled[$ext_name] = $ext;

			if (method_exists($ext, 'autoload')) {
				spl_autoload_register([$ext, 'autoload']);
			}
			$ext->enable();
			try {
				$ext->init();
			} catch (Minz_Exception $e) {	// @phpstan-ignore catch.neverThrown (Thrown by extensions)
				Minz_Log::warning('Error while enabling extension ' . $ext->getName() . ': ' . $e->getMessage());
				$ext->disable();
				unset(self::$ext_list_enabled[$ext_name]);
			}
		}
	}

	/**
	 * Enable a list of extensions.
	 *
	 * @param array<string,bool> $ext_list the names of extensions we want to load.
	 * @param 'system'|'user'|null $onlyOfType limit the extensions to load to those of those type. Set to null string to load all.
	 */
	public static function enableByList(?array $ext_list, ?string $onlyOfType = null): void {
		if (empty($ext_list)) {
			return;
		}
		foreach ($ext_list as $ext_name => $ext_status) {
			if ($ext_status && is_string($ext_name)) {
				self::enable($ext_name, $onlyOfType);
			}
		}
	}

	/**
	 * Return a list of extensions.
	 *
	 * @param bool $only_enabled if true returns only the enabled extensions (false by default).
	 * @return Minz_Extension[] an array of extensions.
	 */
	public static function listExtensions(bool $only_enabled = false): array {
		if ($only_enabled) {
			return self::$ext_list_enabled;
		} else {
			return self::$ext_list;
		}
	}

	/**
	 * Return an extension by its name.
	 *
	 * @param string $ext_name the name of the extension.
	 * @return Minz_Extension|null the corresponding extension or null if it doesn't exist.
	 */
	public static function findExtension(string $ext_name): ?Minz_Extension {
		if (!isset(self::$ext_list[$ext_name])) {
			return null;
		}

		return self::$ext_list[$ext_name];
	}

	/**
	 * Add a hook function to a given hook.
	 *
	 * The hook name must be a valid one. For the valid list, see self::$hook_list
	 * array keys.
	 *
	 * @param string $hook_name the hook name (must exist).
	 * @param callable $hook_function the function name to call (must be callable).
	 */
	public static function addHook(string $hook_name, $hook_function): void {
		if (isset(self::$hook_list[$hook_name]) && is_callable($hook_function)) {
			self::$hook_list[$hook_name]['list'][] = $hook_function;
		}
	}

	/**
	 * Call functions related to a given hook.
	 *
	 * The hook name must be a valid one. For the valid list, see self::$hook_list
	 * array keys.
	 *
	 * @param string $hook_name the hook to call.
	 * @param mixed ...$args additional parameters (for signature, please see self::$hook_list).
	 * @return mixed|void|null final result of the called hook.
	 */
	public static function callHook(string $hook_name, ...$args) {
		if (!isset(self::$hook_list[$hook_name])) {
			return;
		}

		$signature = self::$hook_list[$hook_name]['signature'];
		if ($signature === 'OneToOne') {
			return self::callOneToOne($hook_name, $args[0] ?? null);
		} elseif ($signature === 'PassArguments') {
			foreach (self::$hook_list[$hook_name]['list'] as $function) {
				call_user_func($function, ...$args);
			}
		} elseif ($signature === 'NoneToString') {
			return self::callHookString($hook_name);
		} elseif ($signature === 'NoneToNone') {
			self::callHookVoid($hook_name);
		}
		return;
	}

	/**
	 * Call a hook which takes one argument and return a result.
	 *
	 * The result is chained between the extension, for instance, first extension
	 * hook will receive the initial argument and return a result which will be
	 * passed as an argument to the next extension hook and so on.
	 *
	 * If a hook return a null value, the method is stopped and return null.
	 *
	 * @param string $hook_name is the hook to call.
	 * @param mixed $arg is the argument to pass to the first extension hook.
	 * @return mixed|null final chained result of the hooks. If nothing is changed,
	 *         the initial argument is returned.
	 */
	private static function callOneToOne(string $hook_name, mixed $arg): mixed {
		$result = $arg;
		foreach (self::$hook_list[$hook_name]['list'] as $function) {
			$result = call_user_func($function, $arg);

			if ($result === null) {
				break;
			}

			$arg = $result;
		}
		return $result;
	}

	/**
	 * Call a hook which takes no argument and returns a string.
	 *
	 * The result is concatenated between each hook and the final string is
	 * returned.
	 *
	 * @param string $hook_name is the hook to call.
	 * @return string concatenated result of the call to all the hooks.
	 */
	public static function callHookString(string $hook_name): string {
		$result = '';
		foreach (self::$hook_list[$hook_name]['list'] ?? [] as $function) {
			$return = call_user_func($function);
			if (is_scalar($return)) {
				$result .= $return;
			}
		}
		return $result;
	}

	/**
	 * Call a hook which takes no argument and returns nothing.
	 *
	 * This case is simpler than callOneToOne because hooks are called one by
	 * one, without any consideration of argument nor result.
	 *
	 * @param string $hook_name is the hook to call.
	 */
	public static function callHookVoid(string $hook_name): void {
		foreach (self::$hook_list[$hook_name]['list'] ?? [] as $function) {
			call_user_func($function);
		}
	}
}
