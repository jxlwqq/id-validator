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
     * @param string $addressCode  地址码
     * @param string $birthdayCode 出生日期码
     *
     * @return bool|mixed|string
     */
    private function _getAddressInfo($addressCode, $birthdayCode)
    {
        $addressInfo = [
            'province' => '',
            'city'     => '',
            'district' => '',
        ];

        // 省级信息
        $provinceAddressCode = substr($addressCode, 0, 2).'0000';
        $addressInfo['province'] = $this->_getAddress($provinceAddressCode, $birthdayCode);

        $firstCharacter = substr($addressCode, 0, 1); // 用于判断是否是港澳台居民居住证（8字开头）

        // 港澳台居民居住证无市级、县级信息
        if ($firstCharacter == '8') {
            return $addressInfo;
        }

        // 市级信息
        $cityAddressCode = substr($addressCode, 0, 4).'00';
        $addressInfo['city'] = $this->_getAddress($cityAddressCode, $birthdayCode);

        // 县级信息
        $addressInfo['district'] = $this->_getAddress($addressCode, $birthdayCode);

        return empty($addressInfo) ? false : $addressInfo;
    }

    /**
     * 获取省市区地址码.
     *
     * @param string $addressCode  地址码
     * @param string $birthdayCode 出生日期码
     *
     * @return string
     */
    private function _getAddress($addressCode, $birthdayCode)
    {
        $year = substr($birthdayCode, 0, 4);
        $address = '';
        if (isset($this->_addressCodeList[$addressCode])) {
            $address = $this->_addressCodeList[$addressCode];
        } else {
            if (isset($this->_addressCodeTimeline[$addressCode])) {
                foreach ($this->_addressCodeTimeline[$addressCode] as $val) {
                    if ($year >= $val['star_year']) {
                        $address = $val['address'];
                    }
                }
            }
        }

        return $address;
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
        $month = (int) substr($birthdayCode, 4, 2);
        $day = (int) substr($birthdayCode, 6, 2);

        $start_date = $constellationList[$month]['start_date'];
        $start_day = (int) explode('-', $start_date)[1];

        if ($day < $start_day) {
            $tmp_month = $month == 1 ? 12 : $month - 1;

            return $constellationList[$tmp_month]['name'];
        } else {
            return $constellationList[$month]['name'];
        }
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
