<?php

namespace Jxlwqq\IdValidator;

/**
 * Trait Helper.
 */
trait Helper
{
    /**
     * 获取地址码信息.
     *
     * @param string $addressCode 地址码
     *
     * @return bool|mixed|string
     */
    private function _getAddressInfo($addressCode)
    {
        $addressInfo = [
            'province' => '',
            'city'     => '',
            'district' => '',
        ];

        // 省级信息
        $provinceAddressCode = substr($addressCode, 0, 2).'0000';
        $addressInfo['province'] = $this->_getAddress($provinceAddressCode);

        $firstCharacter = substr($addressCode, 0, 1); // 用于判断是否是港澳台居民居住证（8字开头）

        // 港澳台居民居住证无市级、县级信息
        if ($firstCharacter == '8') {
            return $addressInfo;
        }

        // 市级信息
        $cityAddressCode = substr($addressCode, 0, 4).'00';
        $addressInfo['city'] = $this->_getAddress($cityAddressCode);

        // 县级信息
        $addressInfo['district'] = $this->_getAddress($addressCode);

        return empty($addressInfo) ? false : $addressInfo;
    }

    /**
     * 获取省市区地址码.
     * 
     * @param string $addressCode 地址码
     *
     * @return string
     */
    private function _getAddress($addressCode)
    {
        return isset($this->_addressCodeList[$addressCode]) ? $this->_addressCodeList[$addressCode] : (isset($this->_abandonedAddressCodeList[$addressCode]) ? $this->_abandonedAddressCodeList[$addressCode] : '');
    }

    /**
     * 获取星座信息.
     *
     * @param string $birthdayCode 出生日期码
     *
     * @return string
     */
    private function _getConstellation($birthdayCode)
    {
        $constellationList = include __DIR__.'/../data/constellation.php';
        $time = strtotime($birthdayCode);
        $year = substr($birthdayCode, 0, 4);
        $month = substr($birthdayCode, 4, 2);
        $day = substr($birthdayCode, 6, 2);

        // 1月份与12月份特殊处理
        if (($month == '01' && $day < '20') || ($month == '12' && $day > '21')) {
            return $constellationList['12']['name'];
        } elseif ($month == '01') {
            return $constellationList['01']['name'];
        } elseif ($month == '12') {
            return $constellationList['12']['name'];
        }

        $startDate = $year.'-'.$constellationList[$month]['start_date'];
        $endDate = $year.'-'.$constellationList[$month]['end_date'];
        if (strtotime($startDate) <= $time && strtotime($endDate) >= $time) {
            return $constellationList[$month]['name'];
        }

        $key = (int) $month - 1; // 1月份已特殊处理
        $key = strlen($key) == 1 ? $this->_getStrPad($key) : (string) $key;

        $startDate = $year.'-'.$constellationList[$key]['start_date'];
        $endDate = $year.'-'.$constellationList[$key]['end_date'];
        if (strtotime($startDate) <= $time && strtotime($endDate) >= $time) {
            return $constellationList[$key]['name'];
        }

        return '';
    }

    /**
     * 获取生肖信息.
     *
     * @param string $birthdayCode 出生日期码
     *
     * @return mixed
     */
    private function _getChineseZodiac($birthdayCode)
    {
        $chineseZodiacList = include __DIR__.'/../data/chineseZodiac.php';
        $start = 1900; // 子鼠
        $end = substr($birthdayCode, 0, 4);
        $key = ($end - $start) % 12;
        $key = $key >= 0 ? $key : ($key + 12);

        return $chineseZodiacList[$key];
    }

    /**
     * 生成随机数.
     *
     * @param int $max 最大值
     * @param int $min 最小值
     *
     * @return int
     */
    private function _getRandInt($max, $min = 1)
    {
        return rand($min, $max);
    }

    /**
     * 获取数字补位.
     *
     * @param string|int $str   字符串
     * @param int        $len   长度
     * @param string     $chr   补位值
     * @param bool       $right 左右
     *
     * @return string
     */
    private function _getStrPad($str, $len = 2, $chr = '0', $right = false)
    {
        return str_pad((string) $str, $len, $chr, $right === true ? STR_PAD_RIGHT : STR_PAD_LEFT);
    }
}
