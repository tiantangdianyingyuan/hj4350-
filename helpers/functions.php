<?php

/**
 * 强调：此处不要出现 use 语句！
 */

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed $default
     * @param string $delimiter
     * @return mixed
     */
    function env($key, $default = null, $delimiter = '')
    {
        $value = getenv($key);

        if ($value === false) {
            return value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }

        if (strlen($value) > 1 && str_starts_with($value, '"') && str_ends_with($value, '"')) {
            $value = substr($value, 1, -1);
        }

        if (strlen($delimiter) > 0) {
            if (strlen($value) == 0) {
                $value = $default;
            } else {
                $value = explode($delimiter, $value);
            }
        }

        return $value;
    }
}

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (!function_exists('str_starts_with')) {
    /**
     * Determine if a given string starts with a given substring.
     *
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    function str_starts_with($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle !== '' && substr($haystack, 0, strlen($needle)) === (string)$needle) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('str_ends_with')) {
    /**
     * Determine if a given string ends with a given substring.
     *
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    function str_ends_with($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if (substr($haystack, -strlen($needle)) === (string)$needle) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('define_once')) {
    /**
     * Define a const if not exists.
     *
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    function define_once($name, $value = true)
    {
        return defined($name) or define($name, $value);
    }
}

if (!function_exists('dd')) {
    /**
     * Dump the passed variable and end the script.
     *
     * @param mixed $arg
     * @param bool $dumpAndDie
     * @return void
     */
    function dd($arg, $dumpAndDie = true)
    {
        echo "<pre>";
        // http_response_code(500);
        \yii\helpers\VarDumper::dump($arg);
        echo "</pre>";
        if ($dumpAndDie) {
            die(1);
        }
    }
}

if (!function_exists('array_column')) {
    function array_column(array $input, $columnKey, $indexKey = null)
    {
        $array = array();
        foreach ($input as $value) {
            if (!array_key_exists($columnKey, $value)) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            } else {
                if (!array_key_exists($indexKey, $value)) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if (!is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }
}

if (!function_exists('make_dir')) {
    /**
     * Create the directory by pathname
     * @param string $pathname The directory path.
     * @param int $mode
     * @return bool
     */
    function make_dir($pathname, $mode = 0777)
    {
        if (is_dir($pathname)) {
            return true;
        }
        if (is_dir(dirname($pathname))) {
            return mkdir($pathname, $mode);
        }
        make_dir(dirname($pathname));
        return mkdir($pathname, $mode);
    }
}


if (!function_exists('app_version')) {
    /**
     * @return string
     */
    function app_version()
    {
        if (!class_exists('\Yii')) {
            return '0.0.0';
        }
        $versionFile = Yii::$app->basePath . '/version.json';
        if (!file_exists($versionFile)) {
            return '0.0.0';
        }
        $versionContent = file_get_contents($versionFile);
        if (!$versionContent) {
            return '0.0.0';
        }
        $versionData = json_decode($versionContent, true);
        if (!$versionData) {
            return '0.0.0';
        }
        return isset($versionData['version']) ? $versionData['version'] : '0.0.0';
    }
}

if (!function_exists('hump')) {
    /**
     * @param string $words
     * @param string $separator
     * @return string
     * 下划线转驼峰或者字符串第一个字母大写
     */
    function hump($words, $separator = '_')
    {
        if (strpos($words, $separator) !== false) {
            $newWords = str_replace($separator, " ", strtolower($words));
            return ltrim(str_replace(" ", "", ucwords($newWords)), $separator);
        } else {
            return ucfirst($words);
        }
    }
}

if (!function_exists('get_supported_image_lib')) {
    /**
     * 获取支持的图片处理库
     * @return array
     * @throws Exception
     */
    function get_supported_image_lib()
    {
        switch (true) {
            case function_exists('gd_info'):
                return ['Gd'];
            case class_exists('\Imagick') && method_exists((new \Imagick()), 'setImageOpacity'):
                return ['Imagick'];
            default:
                return ['Gd'];
        }
        throw new Exception('找不到可处理图片的扩展，请检查PHP是否正确安装了GD或Imagick扩展。');
    }
}

if (!function_exists('price_format')) {
    define_once('PRICE_FORMAT_FLOAT', 'float');
    define_once('PRICE_FORMAT_STRING', 'string');

    /**
     * @param $val
     * @param string $returnType PRICE_FORMAT_FLOAT|PRICE_FORMAT_STRING
     * @param int $decimals
     * @return float|string
     */
    function price_format($val, $returnType = 'string', $decimals = 2)
    {
        $val = floatval($val);
        $result = number_format($val, $decimals, '.', '');
        if ($returnType === PRICE_FORMAT_FLOAT) {
            return (float)$result;
        }
        return $result;
    }
}

if (!function_exists('mysql_timestamp')) {
    /**
     * 生成mysql数据库时间戳（eg. 2000-01-01 12:00:00）
     * @param integer $time
     * @return false|string
     */
    function mysql_timestamp($time = null)
    {
        if ($time === null) {
            $time = time();
        }
        return date('Y-m-d H:i:s', $time);
    }
}

if (!function_exists('new_date')) {
    function new_date($date)
    {
        if ($date == '0000-00-00 00:00:00') {
            return '';
        }

        $time = strtotime($date);
        $Y = date('Y', $time) . '.';
        $m = date('m', $time) . '.';
        $d = date('d', $time) . ' ';
        $His = date('H:i:s', $time);
        return $Y . $m . $d . $His;
    }
}

if (!function_exists('get_request_uri')) {
    /**
     * 获取当前请求的uri
     * @return string|string[]|null
     * @throws Exception
     */
    function get_request_uri()
    {
        if (isset($_SERVER['X-Rewrite-Url'])) { // IIS
            $requestUri = $_SERVER['X-Rewrite-Url'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
            if ($requestUri !== '' && $requestUri[0] !== '/') {
                $requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
            }
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0 CGI
            $requestUri = $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $requestUri .= '?' . $_SERVER['QUERY_STRING'];
            }
        } else {
            throw new Exception('Unable to determine the request URI.');
        }
        return $requestUri;
    }
}

if (!function_exists('is_we7')) {
    /**
     * 判断是否是微擎版
     * @return bool
     */
    function is_we7()
    {
        $moduleFile = __DIR__ . '/../module.php';
        if (@file_exists($moduleFile)) {
            if (mb_stripos(__DIR__, 'addons') !== false && mb_stripos(__DIR__, 'zjhj_bd') !== false) {
                return true;
            }
            return false;
        } else {
            return false;
        }
    }
}
if (!function_exists('is_we7_offline')) {
    /**
     * 判断是否是微擎离线版
     * @return bool
     */
    function is_we7_offline()
    {
        if (!is_we7()) {
            return false;
        }
        if (file_exists(__DIR__ . '/../module.php')) {
            if (mb_stripos(__DIR__, 'addons') !== false) {
                if (mb_stripos(__DIR__, 'zjhj_bd_offline') !== false) {
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }
    }
}
if (!function_exists('RGBToHex')) {
    /**
     * RGB转 十六进制
     * @param $rgb RGB颜色的字符串 如：rgb(255,255,255);
     * @return string 十六进制颜色值 如：#FFFFFF
     */
    function RGBToHex($rgb)
    {
        $regexp = "/^rgb\(([0-9]{0,3})\,\s*([0-9]{0,3})\,\s*([0-9]{0,3})\)/";
        $re = preg_match($regexp, $rgb, $match);
        $re = array_shift($match);
        $hexColor = "#";
        $hex = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
        for ($i = 0; $i < 3; $i++) {
            $r = null;
            $c = $match[$i];
            $hexAr = array();
            while ($c >= 16) {
                $r = $c % 16;
                $c = ($c / 16) >> 0;
                array_push($hexAr, $hex[$r]);
            }
            array_push($hexAr, $hex[$c]);
            $ret = array_reverse($hexAr);
            $item = implode('', $ret);
            $item = str_pad($item, 2, '0', STR_PAD_LEFT);
            $hexColor .= $item;
        }
        return $hexColor;
    }
}
if (!function_exists('hex2rgb')) {
    /**
     * 十六进制 转 RGB
     */
    function hex2rgb($hexColor)
    {
        $color = str_replace('#', '', $hexColor);
        if (strlen($color) > 3) {
            $rgb = array(
                'r' => hexdec(substr($color, 0, 2)),
                'g' => hexdec(substr($color, 2, 2)),
                'b' => hexdec(substr($color, 4, 2))
            );
        } else {
            $color = $hexColor;
            $r = substr($color, 0, 1) . substr($color, 0, 1);
            $g = substr($color, 1, 1) . substr($color, 1, 1);
            $b = substr($color, 2, 1) . substr($color, 2, 1);
            $rgb = array(
                'r' => hexdec($r),
                'g' => hexdec($g),
                'b' => hexdec($b)
            );
        }
        return $rgb;
    }
}

if (!function_exists('table_exists')) {
    /**
     * 检查数据表是否存在
     * @param $tableName
     * @return bool
     * @throws \yii\db\Exception
     */
    function table_exists($tableName)
    {
        $sql = "SHOW TABLES LIKE '{$tableName}';";
        $result = Yii::$app->db->createCommand($sql)->queryAll();
        if (is_array($result) && count($result)) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('sql_execute')) {


    /**
     * 执行SQL
     * @param string $sql 要运行的SQL
     * @param bool $split 自动拆分SQL
     * @param bool $continueOnError 遇到错误继续执行
     * @throws Exception
     */
    function sql_execute($sql, $split = true, $continueOnError = true)
    {
        if ($split) {
            $list = SqlFormatter::splitQuery($sql);
        } else {
            $list = [$sql];
        }
        foreach ($list as $item) {
            try {
                Yii::$app->db->createCommand($item)->execute();
            } catch (Exception $exception) {
                if (!$continueOnError) {
                    throw $exception;
                }
            }
        }
    }
}

if (!function_exists('get_distance')) {
    /**
     * 求两个已知经纬度之间的距离,单位为米
     *
     * @param $lng1 Number 位置1经度
     * @param $lat1 Number 位置1纬度
     * @param $lng2 Number 位置2经度
     * @param $lat2 Number 位置2纬度
     * @return float 距离，单位米
     */
    function get_distance($lng1, $lat1, $lng2, $lat2)
    {
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        return $s;
    }
}

if (!function_exists('we7_table_name')) {
    /**
     * 返回微擎数据表对应的完整表名称（添加前缀）
     * @param $name
     * @return string
     */
    function we7_table_name($name)
    {
        $configFile = __DIR__ . '/../../../data/config.php';
        if (!file_exists($configFile)) {
            $prefix = 'ims_';
        } else {
            require $configFile;
            if (isset($config['db']['master'])) {
                $prefix = $config['db']['master']['tablepre'];
            } else {
                $prefix = 'ims_';
            }
        }
        return $prefix . $name;
    }
}

if (!function_exists('we7_setting')) {
    /**
     * 返回微擎配置信息
     * @param string $name
     * @return array|mixed
     */
    function we7_setting($name = '')
    {
        $configFile = __DIR__ . '/../../../data/config.php';
        if (!file_exists($configFile)) {
            $config = [];
        } else {
            require $configFile;
            if (!empty($name) && isset($config['setting'][$name])) {
                return $config['setting'][$name];
            }
            if (isset($config['setting'])) {
                $config = $config['setting'];
            } else {
                $config = [];
            }
        }
        return $config;
    }
}

if (!function_exists('remove_dir')) {
    /**
     * 删除文件夹
     * @param $dir
     * @return bool
     */
    function remove_dir($dir)
    {
        //先删除目录下的文件：
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    remove_dir($fullpath);
                }
            }
        }

        closedir($dh);
        //删除当前文件夹：
        if (rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('is_point_in_polygon')) {
    /**
     * 判断一个坐标是否在一个多边形内（由多个坐标围成的）
     * 基本思想是利用射线法，计算射线与多边形各边的交点，如果是偶数，则点在多边形外，否则
     * 在多边形内。还会考虑一些特殊情况，如点在多边形顶点上，点在多边形边上等特殊情况。
     * @param array $point 指定点坐标
     * @param array $pts 多边形坐标 顺时针方向
     * @return bool
     */
    function is_point_in_polygon($point, $pts)
    {
        $N = count($pts);
        $boundOrVertex = true; //如果点位于多边形的顶点或边上，也算做点在多边形内，直接返回true
        $intersectCount = 0;//cross points count of x
        $precision = 2e-10; //浮点类型计算时候与0比较时候的容差
        $p1 = 0;//neighbour bound vertices
        $p2 = 0;
        $p = $point; //测试点

        $p1 = $pts[0];//left vertex
        for ($i = 1; $i <= $N; ++$i) {//check all rays
            // dump($p1);
            if ($p['lng'] == $p1['lng'] && $p['lat'] == $p1['lat']) {
                return $boundOrVertex;//p is an vertex
            }

            $p2 = $pts[$i % $N];//right vertex
            if ($p['lat'] < min($p1['lat'], $p2['lat']) || $p['lat'] > max($p1['lat'], $p2['lat'])) {
                //ray is outside of our interests
                $p1 = $p2;
                continue;//next ray left point
            }

            if ($p['lat'] > min($p1['lat'], $p2['lat']) && $p['lat'] < max($p1['lat'], $p2['lat'])) {
                //ray is crossing over by the algorithm (common part of)
                if ($p['lng'] <= max($p1['lng'], $p2['lng'])) {
                    //x is before of ray
                    if ($p1['lat'] == $p2['lat'] && $p['lng'] >= min($p1['lng'], $p2['lng'])) {
                        //overlies on a horizontal ray
                        return $boundOrVertex;
                    }

                    if ($p1['lng'] == $p2['lng']) {//ray is vertical
                        if ($p1['lng'] == $p['lng']) {//overlies on a vertical ray
                            return $boundOrVertex;
                        } else {//before ray
                            ++$intersectCount;
                        }
                    } else {//cross point on the left side
                        $xinters = ($p['lat'] - $p1['lat']) * ($p2['lng'] - $p1['lng']) / ($p2['lat'] - $p1['lat']) + $p1['lng'];//cross point of lng
                        if (abs($p['lng'] - $xinters) < $precision) {//overlies on a ray
                            return $boundOrVertex;
                        }

                        if ($p['lng'] < $xinters) {//before ray
                            ++$intersectCount;
                        }
                    }
                }
            } else {//special case when ray is crossing through the vertex
                if ($p['lat'] == $p2['lat'] && $p['lng'] <= $p2['lng']) {//p crossing over p2
                    $p3 = $pts[($i + 1) % $N]; //next vertex
                    if ($p['lat'] >= min($p1['lat'], $p3['lat']) && $p['lat'] <= max($p1['lat'], $p3['lat'])) {
                        //p.lat lies between p1.lat & p3.lat
                        ++$intersectCount;
                    } else {
                        $intersectCount += 2;
                    }
                }
            }
            $p1 = $p2;//next ray left point
        }

        if ($intersectCount % 2 == 0) {//偶数在多边形外
            return false;
        } else { //奇数在多边形内
            return true;
        }
    }
}

if (!function_exists('generate_order_no')) {
    /**
     * 生成 前缀+24位数字的订单号
     * @param string $prefix 前缀
     * @return string
     */
    function generate_order_no($prefix = '')
    {
        $randLen = 6;
        $id = base_convert(substr(uniqid(), 0 - $randLen), 16, 10);
        if (strlen($id) > 10) {
            $id = substr($id, -10);
        } elseif (strlen($id) < 10) {
            $rLen = 10 - strlen($id);
            $id = $id . rand(pow(10, $rLen - 1), pow(10, $rLen) - 1);
        }
        $dateTimeStr = date('YmdHis');
        return $prefix . $dateTimeStr . $id;
    }
}

if (!function_exists('sha256')) {
    /**
     * @param string $data 要进行哈希运算的消息。
     * @param bool $raw_output 设置为 TRUE 输出原始二进制数据， 设置为 FALSE 输出小写 16 进制字符串。
     * @return string
     * 进行sha256加密
     */
    function sha256($data, $raw_output = false)
    {
        return hash('sha256', $data, $raw_output);
    }
}

if (!function_exists('array_insert')) {
    function array_insert(&$array, $position, $item)
    {
        $first_array = array_splice($array, 0, $position);
        $array = array_merge($first_array, [$item], $array);
    }
}

if (!function_exists('filter_emoji')) {
    /**
     * 过滤emoji字符
     * @param $str
     * @return string|null
     */
    function filter_emoji($str)
    {
        $str = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);

        return $str;
    }
}
if (!function_exists('utf8_str_split')) {
    /**
     * 字符串转数组，支持UTF8中文
     * @param $str
     * @param int $split_len
     * @return array|bool|mixed
     */
    function utf8_str_split($str, $split_len = 1)
    {
        if (!preg_match('/^[0-9]+$/', $split_len) || $split_len < 1)
            return FALSE;

        $len = mb_strlen($str, 'UTF-8');
        if ($len <= $split_len)
            return array($str);

        preg_match_all('/.{' . $split_len . '}|[^x00]{1,' . $split_len . '}$/us', $str, $ar);

        return $ar[0];
    }
}

if (!function_exists('address_handle')) {
    /**
     * 地址串处理，输出省市区，支持 中国省市区详细地址 或 省市区详细地址的转换
     * 例：中国浙江省嘉兴市南湖区中环南路1882号 输出 浙江省 嘉兴市 南湖区
     * @param $address
     * @return string
     */
    function address_handle($address)
    {
        $new_address = '';
        $arr = utf8_str_split($address);
        $num = 0;
        $address_num = 0;
        foreach ($arr as $item) {
            if (($num < 2 && $item != '中' && $item != '国') || $num >= 2) {
                $new_address .= $item;
                if ($item == '省' || $item == '市' || $item == '区') {
                    if ($address_num < 2) {
                        $new_address .= ' ';
                    }
                    $address_num++;
                }
                $num++;
                if ($address_num >= 3) {
                    break;
                }
            }
        }
        return $new_address;
    }
}
