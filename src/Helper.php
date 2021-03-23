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
     * @param bool   $strictMode
     *
     * @return bool|mixed|string
     */
    private function _getAddressInfo($addressCode, $birthdayCode, $strictMode = false)
    {
        $addressInfo = [
            'province' => '',
            'city'     => '',
            'district' => '',
        ];

        // 省级信息
        $provinceAddressCode = substr($addressCode, 0, 2).'0000';
        $addressInfo['province'] = $this->_getAddress($provinceAddressCode, $birthdayCode, $strictMode);

        $firstCharacter = substr($addressCode, 0, 1); // 用于判断是否是港澳台居民居住证（8字开头）

        // 港澳台居民居住证无市级、县级信息
        if ($firstCharacter == '8') {
            return $addressInfo;
        }

        // 市级信息
        $cityAddressCode = substr($addressCode, 0, 4).'00';
        $addressInfo['city'] = $this->_getAddress($cityAddressCode, $birthdayCode, $strictMode);

        // 县级信息
        $addressInfo['district'] = $this->_getAddress($addressCode, $birthdayCode, $strictMode);

        return empty($addressInfo['district']) ? false : $addressInfo;
    }

    /**
     * 获取省市区地址码.
     *
     * @param string $addressCode  地址码
     * @param string $birthdayCode 出生日期码
     * @param bool   $strictMode
     *
     * @return string
     */
    private function _getAddress($addressCode, $birthdayCode, $strictMode = false)
    {
        $address = '';
        if (isset($this->_addressCodeTimeline[$addressCode])) {
            $timeline = $this->_addressCodeTimeline[$addressCode];
            $year = substr($birthdayCode, 0, 4);
            // 严格模式下，会检查【地址码正式启用的年份】与【身份证上的出生年份】
            foreach ($timeline as $key => $val) {
                $start_year = $val['start_year'] != '' ? $val['start_year'] : '0001';
                $end_year = $val['end_year'] != '' ? $val['end_year'] : '9999';
                if ($year >= $start_year and $year <= $end_year) {
                    $address = $val['address'];
                }
            }

            // 非严格模式下，则不会检查【地址码正式启用的年份】与【身份证上的出生年份】的关系
            if (empty($address) and !$strictMode) {
                foreach ($timeline as $key => $val) {
                    // 由于较晚申请户口或身份证等原因，导致会出现地址码正式启用于2000年，但实际1999年出生的新生儿，由于晚了一年报户口，导致身份证上的出生年份早于地址码正式启用的年份
                    $end_year = $val['end_year'] != '' ? $val['end_year'] : '9999';
                    if ($year <= $end_year) {
                        $address = $val['address'];
                        break;
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
}
