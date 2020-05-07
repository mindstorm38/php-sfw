<?php

namespace SFW\Util;

class EntityTag {

	private $content;
	private $weak;

	public function __construct(string $content, bool $weak = false) {
		$this->content = $content;
		$this->weak = $weak;
	}

	public function get_content(): string {
		return $this->content;
	}

	public function is_weak(): bool {
		return $this->weak;
	}

	public function is_strong(): bool {
		return !$this->weak;
	}

	public function strong_match(EntityTag $other): bool {
		return $this->is_strong() && $other->is_strong() && $this->content == $other->content;
	}

	public function weak_match(EntityTag $other): bool {
		return $this->content == $other->content;
	}

	public function weak_match_content(string $content): bool {
		return $this->content == $content;
	}

	public function header_format(): string {
		return $this->weak ? "\\W\"{$this->content}\"" : "\"{$this->content}\"";
	}

	public static function content_from_timestamp(int $timestamp): string {
		return dechex($timestamp);
	}

	public static function from_timestamp(int $timestamp): EntityTag {
		return new EntityTag(self::content_from_timestamp($timestamp));
	}

	public static function header_parse(string $content): ?EntityTag {

		$len = strlen($content);
		if ($len >= 2 && substr($content, -1, 1) == "\"") {
			if (substr($content, 0, 1) == "\"") {
				return new EntityTag(substr(1, $len - 2));
			} else if ($len > 2 && substr($content, 0, 2) == "\\W\"") {
				return new EntityTag(substr(3, $len - 4));
			}
		}

		return null;

	}

	public static function header_parse_list(string $content): array {

		// TODO : Support for "*"

		$arr = [];

		foreach (explode(",", $content) as $part) {
			if (($tag = self::header_parse(trim($part))) !== null) {
				$arr[] = $tag;
			}
		}

		return $arr;

	}

	public static function header_list_weak_match(string $content, string $etag_content): bool {

		foreach (self::header_parse_list($content) as $tag) {
			if ($tag->weak_match_content($etag_content)) {
				return true;
			}
		}

		return false;

	}

}