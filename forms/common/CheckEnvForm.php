<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/9
 * Time: 15:18
 */

namespace app\forms\common;


class CheckEnvForm
{
    public function check()
    {
        $list = [];
        $list[] = $this->phpVersion();

        $pass = 1;
        foreach ($list as $item) {
            if ($item['pass'] !== 1) {
                $pass = 0;
                break;
            }
        }
        return [
            'pass' => $pass,
            'list' => $list,
        ];
    }

    private function phpVersion()
    {
        $res = [
            'name' => 'PHP版本',
            'desc' => '本系统要求PHP版本为7.0以上，当前PHP本版为'
        ];
        $res['pass'] = (version_compare(phpversion(), '7.0') >= 0) ? 1 : 0;
        return $res;
    }
}
