<?php

namespace fast;

/**
 * 随机生成类
 */
class Random
{
    /**
     * 可以更换其中的顺序和字母，但是不可以包含数字零('0')
     * @var string ID转编码,给定字符序列
     */
    public static $idCodeChars = 'A,C,Y,2,H,S,F,9,4,T,G,E,W,L,5,Z,D,Q,X,8,V,7,R,P,6,M,J,B,U,K,3,N,I';

    /**
     * ID转编码
     * @param int $id 数字ID
     * @param string $divider 邀请码间隔码，因为有的邀请码是不足6位的，所以要有间隔码
     * @param int $code_min_length 最短设备码
     * @return string 字符编码
     */
    public static function id2code($id, $divider = '0', $code_min_length = 6)
    {
        $chars = explode(',', self::$idCodeChars);
        $chars_len = count($chars);
        $buf = '';
        // 最大下标
        $posMax = $chars_len - 1;
        // 将10进制的id转化为33进制的邀请码
        while (((int)($id / $chars_len)) > 0) {
            $ind = $id % $chars_len;
            $buf .= $chars[$ind];
            $id = (int)($id / $chars_len);
        }
        $buf .= $chars[(int)$id % $chars_len];
        // 反转buf字符串
        $buf = strrev($buf);
        // 补充长度
        $fixLen = $code_min_length - mb_strlen($buf, 'UTF-8');
        if ($fixLen > 0) {
            $buf .= $divider;
            for ($i = 0; $i < $fixLen - 1; $i++) {
                // 从字符序列中随机取出字符进行填充
                $buf .= $chars[random_int(0, $posMax)];
            }
        }
        return $buf;
    }

    /**
     * 编码转ID
     * @param string $code 字符编码
     * @param string $divider 邀请码间隔码，因为有的邀请码是不足6位的，所以要有间隔码
     * @return int 数字ID
     */
    public static function code2id($code, $divider = '0')
    {
        $chars = explode(',', self::$idCodeChars);
        $chars_len = count($chars);
        $code_len = mb_strlen($code, 'UTF-8');
        $id = 0;
        // 33进制转10进制
        for ($i = 0; $i < $code_len; $i++) {
            if ($code[$i] === $divider) {
                break;
            }
            $ind = 0;
            foreach ($chars as $j => $jValue) {
                if ($code[$i] === $jValue) {
                    $ind = $j;
                    break;
                }
            }
            if ($i > 0) {
                $id = $id * $chars_len + $ind;
            } else {
                $id = $ind;
            }
        }
        return $id;
    }

    /**
     * 生成数字和字母
     *
     * @param int $len 长度
     * @return string
     */
    public static function alnum($len = 6)
    {
        return self::build('alnum', $len);
    }

    /**
     * 仅生成字符
     *
     * @param int $len 长度
     * @return string
     */
    public static function alpha($len = 6)
    {
        return self::build('alpha', $len);
    }

    /**
     * 生成指定长度的随机数字
     *
     * @param int $len 长度
     * @return string
     */
    public static function numeric($len = 4)
    {
        return self::build('numeric', $len);
    }

    /**
     * 数字和字母组合的随机字符串
     *
     * @param int $len 长度
     * @return string
     */
    public static function nozero($len = 4)
    {
        return self::build('nozero', $len);
    }

    /**
     * 能用的随机数生成
     * @param string $type 类型 alpha/alnum/numeric/nozero/unique/md5/encrypt/sha1
     * @param int $len 长度
     * @return string
     */
    public static function build($type = 'alnum', $len = 8)
    {
        switch ($type) {
            case 'alpha':
            case 'alnum':
            case 'numeric':
            case 'nozero':
                switch ($type) {
                    case 'alpha':
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric':
                        $pool = '0123456789';
                        break;
                    case 'nozero':
                        $pool = '123456789';
                        break;
                }
                return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
            case 'unique':
            case 'md5':
                return md5(uniqid(mt_rand()));
            case 'encrypt':
            case 'sha1':
                return sha1(uniqid(mt_rand(), true));
        }
    }

    /**
     * 根据数组元素的概率获得键名
     *
     * @param array $ps array('p1'=>20, 'p2'=>30, 'p3'=>50);
     * @param int $num 默认为1,即随机出来的数量
     * @param bool $unique 默认为true,即当num>1时,随机出的数量是否唯一
     * @return mixed 当num为1时返回键名,反之返回一维数组
     */
    public static function lottery($ps, $num = 1, $unique = true)
    {
        if (!$ps) {
            return $num == 1 ? '' : [];
        }
        if ($num >= count($ps) && $unique) {
            $res = array_keys($ps);
            return $num == 1 ? $res[0] : $res;
        }
        $max_exp = 0;
        $res = [];
        foreach ($ps as $key => $value) {
            $value = substr($value, 0, stripos($value, ".") + 6);
            $exp = strlen(strchr($value, '.')) - 1;
            if ($exp > $max_exp) {
                $max_exp = $exp;
            }
        }
        $pow_exp = pow(10, $max_exp);
        if ($pow_exp > 1) {
            reset($ps);
            foreach ($ps as $key => $value) {
                $ps[$key] = $value * $pow_exp;
            }
        }
        $pro_sum = array_sum($ps);
        if ($pro_sum < 1) {
            return $num == 1 ? '' : [];
        }
        for ($i = 0; $i < $num; $i++) {
            $rand_num = mt_rand(1, $pro_sum);
            reset($ps);
            foreach ($ps as $key => $value) {
                if ($rand_num <= $value) {
                    break;
                } else {
                    $rand_num -= $value;
                }
            }
            if ($num == 1) {
                $res = $key;
                break;
            } else {
                $res[$i] = $key;
            }
            if ($unique) {
                $pro_sum -= $value;
                unset($ps[$key]);
            }
        }
        return $res;
    }

    /**
     * 获取全球唯一标识
     * @return string
     */
    public static function uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
