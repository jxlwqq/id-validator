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
        $addressInfo = [];
        $firstCharacter = substr($addressCode, 0, 1); // 用于判断是否是港澳台居民居住证（8字开头）

        // 省级信息
        $provinceAddressCode = substr($addressCode, 0, 2).'0000';
        $addressInfo['province'] = isset($this->_addressCodeList[$provinceAddressCode]) ? $this->_addressCodeList[$provinceAddressCode] : (isset($this->_abandonedAddressCodeList[$provinceAddressCode]) ? $this->_abandonedAddressCodeList[$provinceAddressCode] : '');

        // 市级信息（港澳台居民居住证无市级信息）
        if ($firstCharacter != '8') {
            $cityAddressCode = substr($addressCode, 0, 4).'00';
            $addressInfo['city'] = isset($this->_addressCodeList[$cityAddressCode]) ? $this->_addressCodeList[$cityAddressCode] : (isset($this->_abandonedAddressCodeList[$cityAddressCode]) ? $this->_abandonedAddressCodeList[$cityAddressCode] : '');
        } else {
            $addressInfo['city'] = '';
        }

        // 县级信息（港澳台居民居住证无县级信息）
        if ($firstCharacter != '8') {
            $addressInfo['district'] = isset($this->_addressCodeList[$addressCode]) ? $this->_addressCodeList[$addressCode] : (isset($this->_abandonedAddressCodeList[$addressCode]) ? $this->_abandonedAddressCodeList[$addressCode] : '');
        } else {
            $addressInfo['district'] = '';
        }

        if (empty($addressInfo)) {
            return false;
        } else {
            return $addressInfo;
        }
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
        $time = strtotime($birthdayCode);
        $year = substr($birthdayCode, 0, 4);
        $month = substr($birthdayCode, 4, 2);
        $day = substr($birthdayCode, 6, 2);

        // 1月份与12月份特殊处理
        if (($month == '01' && $day < 20) || ($month == '12' && $day > 21)) {
            return $this->_constellationList['12']['name'];
        } elseif ($month == '01') {
            return $this->_constellationList['01']['name'];
        } elseif ($month == '12') {
            return $this->_constellationList['12']['name'];
        }

        $startDate = $year.'-'.$this->_constellationList[$month]['start_date'];
        $endDate = $year.'-'.$this->_constellationList[$month]['end_date'];
        if (strtotime($startDate) <= $time && strtotime($endDate) >= $time) {
            return $this->_constellationList[$month]['name'];
        }

        $key = (int) $month - 1; // 1月份以特殊处理
        $key = strlen($key) == 1 ? $this->_getStrPad($key) : (string) $key;

        $startDate = $year.'-'.$this->_constellationList[$key]['start_date'];
        $endDate = $year.'-'.$this->_constellationList[$key]['end_date'];
        if (strtotime($startDate) <= $time && strtotime($endDate) >= $time) {
            return $this->_constellationList[$key]['name'];
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
        $start = 1900; // 子鼠
        $end = substr($birthdayCode, 0, 4);
        $key = ($end - $start) % 12;
        $key = $key >= 0 ? $key : ($key + 12);

        return $this->_chineseZodiacList[$key];
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
        $str = strval($str);
        if (strlen($str) >= $len) {
            return $str;
        } else {
            for ($i = 0, $j = $len - strlen($str); $i < $j; $i++) {
                if ($right) {
                    $str = $str.$chr;
                } else {
                    $str = $chr.$str;
                }
            }

            return $str;
        }
    }
}
