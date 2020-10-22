<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\core\express\core;


interface WdInterface
{
    public const HTTP_OK = 200;
    public const HTTP_NOT_MODIFIED = 304;
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_METHOD_NOT_ALLOWED = 405;
    public const HTTP_CONFLICT = 409;
    public const HTTP_TOO_MANY_REQUESTS = 429;
    public const HTTP_INTERNAL_SERVER_ERROR = 500;

    public const WD_INTERFACE_TEXT = [
        self::HTTP_BAD_REQUEST => 'URL无效 或 appCode错误',
        self::HTTP_UNAUTHORIZED => 'appCode错误',
        self::HTTP_FORBIDDEN => '次数用完',
        self::HTTP_INTERNAL_SERVER_ERROR => 'API网管错误',
    ];
}