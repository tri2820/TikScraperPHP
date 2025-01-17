<?php
namespace TikScraper\Models;

use TikScraper\Constants\Codes;

/**
 * Has information about how the request went
 * @param bool $success Request was successfull or not. True if $http_code is >= 200 and < 300 and $tiktok_code is 0
 * @param int $http_code HTTP Code response
 * @param int $tiktok_code TikTok's own error codes for their own API
 * @param string $tiktok_msg Detailed error message for $tiktok_code
 */
class Meta {
    public bool $success = false;
    public int $http_code = 503;
    public int $tiktok_code = -1;
    public string $tiktok_msg = '';

    function __construct(bool $http_success, int $code, $data) {
        $http_success = $http_success;
        $http_code = $code;

        if (empty($data)) {
            // *Something* went wrong
            $tiktok_code = -1;
        } elseif (is_object($data)) {
            // JSON
            $tiktok_code = $this->getCode($data);
        } else {
            // HTML
            $tiktok_code = 0;
        }

        $tiktok_msg = Codes::fromId($tiktok_code);

        // Setting values
        $this->success = $http_success && $tiktok_code === 0;
        $this->http_code = $http_code;
        $this->tiktok_code = $tiktok_code;
        $this->tiktok_msg = $tiktok_msg;
    }

    private function getCode(object $data): int {
        $code = -1;
        if (isset($data->statusCode)) {
            $code = intval($data->statusCode);
        } elseif (isset($data->status_code)) {
            $code = intval($data->status_code);
        } elseif (isset($data->type) && $data->type === "verify") {
            // Check verify
            $code = 10000;
        }
        return $code;
    }
}
