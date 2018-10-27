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
        $address = '';
        if (isset($this->_addressCodeTimeline[$addressCode])) {
            $timeline = $this->_addressCodeTimeline[$addressCode];
            $year = substr($birthdayCode, 0, 4);
            foreach ($timeline as $key => $val) {
                if (($key == 0 && $year < $val['start_year']) || $year >= $val['start_year']) {
                    $address = $val['address'];
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
