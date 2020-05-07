<?php

namespace SFW\Util;

use SFW\Utils;

/**
 *
 * See <a>https://tools.ietf.org/html/rfc7232</a> for implementation details.
 *
 * @author Théo Rozier
 *
 */
final class CacheUtils {

	public static function send_no_store(): void {
		if (!headers_sent()) {
			header("Cache-Control: no-store, max-age=0");
			header("Pragma: no-cache");
		}
	}

	public static function send_to_revalidate(?int $last_mod = null, ?EntityTag $etag = null, bool $public_caches = false): void {
		if (!headers_sent()) {

			$privacy = $public_caches ? "public" : "private";

			header("Cache-Control: {$privacy}, no-cache, max-age=0, must-revalidate");
			header("Pragma: no-cache");

			if ($last_mod !== null) {

				header("Last-Modified: " . Utils::get_http_header_date($last_mod));

				if ($etag !== null) {
					header("ETag: {$etag->header_format()}");
				}

			}

		}
	}

	public static function validate_cache(int $last_mod, ?string $etag = null): bool {

		// TODO : Support for IF_MATCH and IF_UNMODIFIED_SINCE.

		if (isset($_SERVER["HTTP_IF_NONE_MATCH"])) {

			if ($etag === null) {
				$etag = EntityTag::from_timestamp($last_mod);
			}

			return EntityTag::header_list_weak_match($_SERVER["HTTP_IF_NONE_MATCH"], $etag);

		} else if (isset($_SERVER["HTTP_IF_MODIFIED_SINCE"])) {

			$if_mod_since = Utils::parse_http_header_date($_SERVER["HTTP_IF_MODIFIED_SINCE"]);
			return $if_mod_since !== false && $last_mod <= $if_mod_since;

		} else {
			return false;
		}

	}

}