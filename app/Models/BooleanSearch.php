<?php
declare(strict_types=1);

/**
 * Contains Boolean search from the search form.
 */
class FreshRSS_BooleanSearch implements \Stringable {

	private string $raw_input = '';
	/** @var list<FreshRSS_BooleanSearch|FreshRSS_Search> */
	private array $searches = [];

	/**
	 * @param string $input
	 * @param int $level
	 * @param 'AND'|'OR'|'AND NOT'|'OR NOT' $operator
	 * @param bool $allowUserQueries
	 */
	public function __construct(
		string $input,
		int $level = 0,
		private readonly string $operator = 'AND',
		bool $allowUserQueries = true
	) {
		$input = trim($input);
		if ($input === '') {
			return;
		}
		if ($level === 0) {
			$input = preg_replace('/:&quot;(.*?)&quot;/', ':"\1"', $input);
			if (!is_string($input)) {
				return;
			}
			$input = preg_replace('/(?<=[\s(!-]|^)&quot;(.*?)&quot;/', '"\1"', $input);
			if (!is_string($input)) {
				return;
			}
		}
		$this->raw_input = $input;

		if ($level === 0) {
			$input = self::escapeLiteralParentheses($input);
			$input = $this->parseUserQueryNames($input, $allowUserQueries);
			$input = $this->parseUserQueryIds($input, $allowUserQueries);
			$input = trim($input);
		}

		$input = self::consistentOrParentheses($input);

		// Either parse everything as a series of BooleanSearch’s combined by implicit AND
		// or parse everything as a series of Search’s combined by explicit OR
		$this->parseParentheses($input, $level) || $this->parseOrSegments($input);
	}

	/**
	 * Parse the user queries (saved searches) by name and expand them in the input string.
	 */
	private function parseUserQueryNames(string $input, bool $allowUserQueries = true): string {
		$all_matches = [];
		if (preg_match_all('/\bsearch:(?P<delim>[\'"])(?P<search>.*)(?P=delim)/U', $input, $matchesFound)) {
			$all_matches[] = $matchesFound;
		}
		if (preg_match_all('/\bsearch:(?P<search>[^\s"]*)/', $input, $matchesFound)) {
			$all_matches[] = $matchesFound;
		}

		if (!empty($all_matches)) {
			$queries = [];
			foreach (FreshRSS_Context::userConf()->queries as $raw_query) {
				if (($raw_query['name'] ?? '') !== '' && ($raw_query['search'] ?? '') !== '') {
					$queries[$raw_query['name']] = trim($raw_query['search']);
				}
			}

			$fromS = [];
			$toS = [];
			foreach ($all_matches as $matches) {
				if (empty($matches['search'])) {
					continue;
				}
				for ($i = count($matches['search']) - 1; $i >= 0; $i--) {
					$name = trim($matches['search'][$i]);
					if (!empty($queries[$name])) {
						$fromS[] = $matches[0][$i];
						if ($allowUserQueries) {
							$toS[] = '(' . self::escapeLiteralParentheses($queries[$name]) . ')';
						} else {
							$toS[] = '';
						}
					}
				}
			}

			$input = str_replace($fromS, $toS, $input);
		}
		return $input;
	}

	/**
	 * Parse the user queries (saved searches) by ID and expand them in the input string.
	 */
	private function parseUserQueryIds(string $input, bool $allowUserQueries = true): string {
		$all_matches = [];

		if (preg_match_all('/\bS:(?P<search>\d+)/', $input, $matchesFound)) {
			$all_matches[] = $matchesFound;
		}

		if (!empty($all_matches)) {
			$queries = [];
			foreach (FreshRSS_Context::userConf()->queries as $raw_query) {
				$queries[] = trim($raw_query['search'] ?? '');
			}

			$fromS = [];
			$toS = [];
			foreach ($all_matches as $matches) {
				if (empty($matches['search'])) {
					continue;
				}
				for ($i = count($matches['search']) - 1; $i >= 0; $i--) {
					// Index starting from 1
					$id = (int)(trim($matches['search'][$i])) - 1;
					if (!empty($queries[$id])) {
						$fromS[] = $matches[0][$i];
						if ($allowUserQueries) {
							$toS[] = '(' . self::escapeLiteralParentheses($queries[$id]) . ')';
						} else {
							$toS[] = '';
						}
					}
				}
			}

			$input = str_replace($fromS, $toS, $input);
		}
		return $input;
	}

	/**
	 * Temporarily escape parentheses used in regex expressions or inside quoted strings.
	 */
	public static function escapeLiteralParentheses(string $input): string {
		return preg_replace_callback('%(?<=[\\s(:#!-]|^)(?<![\\\\])(?P<delim>[\'"/]).+?(?<!\\\\)(?P=delim)[im]*%',
			fn(array $matches): string => str_replace(['(', ')'], ['\\u0028', '\\u0029'], $matches[0]),
			$input
		) ?? '';
	}

	public static function unescapeLiteralParentheses(string $input): string {
		return str_replace(['\\u0028', '\\u0029'], ['(', ')'], $input);
	}

	/**
	 * Example: 'ab cd OR ef OR "gh ij"' becomes '(ab cd) OR (ef) OR ("gh ij")'
	 */
	public static function addOrParentheses(string $input): string {
		$input = trim($input);
		if ($input === '') {
			return '';
		}
		$splits = preg_split('/\b(OR)\b/i', $input, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
		$ns = count($splits);
		if ($ns <= 1) {
			return $input;
		}
		$result = '';
		$segment = '';
		for ($i = 0; $i < $ns; $i++) {
			$segment .= $splits[$i];
			if (trim($segment) === '') {
				$segment = '';
			} elseif (strcasecmp($segment, 'OR') === 0) {
				$result .= $segment . ' ';
				$segment = '';
			} else {
				$quotes = substr_count($segment, '"') + substr_count($segment, '&quot;');
				if ($quotes % 2 === 0) {
					$segment = trim($segment);
					if (in_array($segment, ['!', '-'], true)) {
						$result .= $segment;
					} else {
						$result .= '(' . $segment . ') ';
					}
					$segment = '';
				}
			}
		}
		$segment = trim($segment);
		if (in_array($segment, ['!', '-'], true)) {
			$result .= $segment;
		} elseif ($segment !== '') {
			$result .= '(' . $segment . ')';
		}
		return trim($result);
	}

	/**
	 * If the query contains a mix of `OR` expressions with and without parentheses,
	 * then add parentheses to make the query consistent.
	 * Example: '(ab (cd OR ef)) OR gh OR ij OR (kl)' becomes '(ab ((cd) OR (ef))) OR (gh) OR (ij) OR (kl)'
	 */
	public static function consistentOrParentheses(string $input): string {
		if (!preg_match('/(?<!\\\\)\\(/', $input)) {
			// No unescaped parentheses in the input
			return trim($input);
		}
		$parenthesesCount = 0;
		$result = '';
		$segment = '';
		$length = strlen($input);

		for ($i = 0; $i < $length; $i++) {
			$c = $input[$i];
			$backslashed = $i >= 1 ? $input[$i - 1] === '\\' : false;
			if (!$backslashed) {
				if ($c === '(') {
					if ($parenthesesCount === 0) {
						if ($segment !== '') {
							$result = rtrim($result) . ' ' . self::addOrParentheses($segment);
							$negation = preg_match('/[!-]$/', $result);
							if (!$negation) {
								$result .= ' ';
							}
							$segment = '';
						}
						$c = '';
					}
					$parenthesesCount++;
				} elseif ($c === ')') {
					$parenthesesCount--;
					if ($parenthesesCount === 0) {
						$segment = self::consistentOrParentheses($segment);
						if ($segment !== '') {
							$result .= '(' . $segment . ')';
							$segment = '';
						}
						$c = '';
					}
				}
			}
			$segment .= $c;
		}
		if (trim($segment) !== '') {
			$result = rtrim($result);
			$negation = preg_match('/[!-]$/', $segment);
			if (!$negation) {
				$result .= ' ';
			}
			$result .= self::addOrParentheses($segment);
		}
		return trim($result);
	}

	/** @return bool True if some parenthesis logic took over, false otherwise */
	private function parseParentheses(string $input, int $level): bool {
		$input = trim($input);
		$length = strlen($input);
		$i = 0;
		$before = '';
		$hasParenthesis = false;
		$nextOperator = 'AND';
		while ($i < $length) {
			$c = $input[$i];
			$backslashed = $i >= 1 ? $input[$i - 1] === '\\' : false;

			if ($c === '(' && !$backslashed) {
				$hasParenthesis = true;

				$before = trim($before);
				if (preg_match('/[!-]$/', $before)) {
					// Trim trailing negation
					$before = rtrim($before, ' !-');
					$isOr = preg_match('/\bOR$/i', $before);
					if ($isOr) {
						// Trim trailing OR
						$before = substr($before, 0, -2);
					}

					// The text prior to the negation is a BooleanSearch
					$searchBefore = new FreshRSS_BooleanSearch($before, $level + 1, $nextOperator);
					if (count($searchBefore->searches()) > 0) {
						$this->searches[] = $searchBefore;
					}
					$before = '';

					// The next BooleanSearch will have to be combined with AND NOT or OR NOT instead of default AND
					$nextOperator = $isOr ? 'OR NOT' : 'AND NOT';
				} elseif (preg_match('/\bOR$/i', $before)) {
					// Trim trailing OR
					$before = substr($before, 0, -2);

					// The text prior to the OR is a BooleanSearch
					$searchBefore = new FreshRSS_BooleanSearch($before, $level + 1, $nextOperator);
					if (count($searchBefore->searches()) > 0) {
						$this->searches[] = $searchBefore;
					}
					$before = '';

					// The next BooleanSearch will have to be combined with OR instead of default AND
					$nextOperator = 'OR';
				} elseif ($before !== '') {
					// The text prior to the opening parenthesis is a BooleanSearch
					$searchBefore = new FreshRSS_BooleanSearch($before, $level + 1, $nextOperator);
					if (count($searchBefore->searches()) > 0) {
						$this->searches[] = $searchBefore;
					}
					$before = '';
				}

				// Search the matching closing parenthesis
				$parentheses = 1;
				$sub = '';
				$i++;
				while ($i < $length) {
					$c = $input[$i];
					$backslashed = $input[$i - 1] === '\\';
					if ($c === '(' && !$backslashed) {
						// One nested level deeper
						$parentheses++;
						$sub .= $c;
					} elseif ($c === ')' && !$backslashed) {
						$parentheses--;
						if ($parentheses === 0) {
							// Found the matching closing parenthesis
							$searchSub = new FreshRSS_BooleanSearch($sub, $level + 1, $nextOperator);
							$nextOperator = 'AND';
							if (count($searchSub->searches()) > 0) {
								$this->searches[] = $searchSub;
							}
							$sub = '';
							break;
						} else {
							$sub .= $c;
						}
					} else {
						$sub .= $c;
					}
					$i++;
				}
				// $sub = trim($sub);
				// if ($sub !== '') {
				// 	// TODO: Consider throwing an error or warning in case of non-matching parenthesis
				// }
			// } elseif ($c === ')') {
			// 	// TODO: Consider throwing an error or warning in case of non-matching parenthesis
			} else {
				$before .= $c;
			}
			$i++;
		}
		if ($hasParenthesis) {
			$before = trim($before);
			if (preg_match('/^OR\b/i', $before)) {
				// The next BooleanSearch will have to be combined with OR instead of default AND
				$nextOperator = 'OR';
				// Trim leading OR
				$before = substr($before, 2);
			}

			// The remaining text after the last parenthesis is a BooleanSearch
			$searchBefore = new FreshRSS_BooleanSearch($before, $level + 1, $nextOperator);
			$nextOperator = 'AND';
			if (count($searchBefore->searches()) > 0) {
				$this->searches[] = $searchBefore;
			}
			return true;
		}
		// There was no parenthesis logic to apply
		return false;
	}

	private function parseOrSegments(string $input): void {
		$input = trim($input);
		if ($input === '') {
			return;
		}
		$splits = preg_split('/\b(OR)\b/i', $input, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
		$segment = '';
		$ns = count($splits);
		for ($i = 0; $i < $ns; $i++) {
			$segment = $segment . $splits[$i];
			if (trim($segment) === '' || strcasecmp($segment, 'OR') === 0) {
				$segment = '';
			} else {
				$quotes = substr_count($segment, '"') + substr_count($segment, '&quot;');
				if ($quotes % 2 === 0) {
					$segment = trim($segment);
					$this->searches[] = new FreshRSS_Search($segment);
					$segment = '';
				}
			}
		}
		$segment = trim($segment);
		if ($segment !== '') {
			$this->searches[] = new FreshRSS_Search($segment);
		}
	}

	/**
	 * Either a list of FreshRSS_BooleanSearch combined by implicit AND
	 * or a series of FreshRSS_Search combined by explicit OR
	 * @return list<FreshRSS_BooleanSearch|FreshRSS_Search>
	 */
	public function searches(): array {
		return $this->searches;
	}

	/** @return 'AND'|'OR'|'AND NOT'|'OR NOT' depending on how this BooleanSearch should be combined */
	public function operator(): string {
		return $this->operator;
	}

	/** @param FreshRSS_BooleanSearch|FreshRSS_Search $search */
	public function add(FreshRSS_BooleanSearch|FreshRSS_Search $search): void {
		$this->searches[] = $search;
	}

	#[\Override]
	public function __toString(): string {
		return $this->getRawInput();
	}

	/** @return string Plain text search query. Must be XML-encoded or URL-encoded depending on the situation */
	public function getRawInput(): string {
		return $this->raw_input;
	}
}
