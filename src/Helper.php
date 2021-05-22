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
     * @param bool   $strictMode   是否启动严格模式检查
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

        $firstCharacter = $addressCode[0]; // 用于判断是否是港澳台居民居住证（8字开头）

        // 港澳台居民居住证无市级、县级信息
        if ($firstCharacter == '8') {
            return $addressInfo;
        }

        // 市级信息
        $cityAddressCode = substr($addressCode, 0, 4).'00';
        $addressInfo['city'] = $this->_getAddress($cityAddressCode, $birthdayCode, $strictMode);

        // 县级信息
        $addressInfo['district'] = $this->_getAddress($addressCode, $birthdayCode, $strictMode);

        // 这里不判断市级信息的原因：
        // 1）直辖市，无市级信息
        // 2）省直辖县或县级市，无市级信息
        return (empty($addressInfo['district']) or empty($addressInfo['province'])) ? false : $addressInfo;
    }

    /**
     * 获取省市区地址码.
     *
     * @param string $addressCode  地址码
     * @param string $birthdayCode 出生日期码
     * @param bool   $strictMode   是否启动严格模式检查
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
            if (empty($address) and ! $strictMode) {
                foreach ($timeline as $key => $val) {
                    // 由于较晚申请户口或身份证等原因，导致会出现地址码正式启用于2000年，但实际1999年出生的新生儿，由于晚了一年报户口，导致身份证上的出生年份早于地址码正式启用的年份
                    // 由于某些地区的地址码已经废弃，但是实际上在之后的几年依然在使用
                    // 这里就不做时间判断了
                    $address = $val['address'];
                    break;
                }
            }

            return $address;
        }

        // 修复 \d\d\d\d01、\d\d\d\d02、\d\d\d\d11 和 \d\d\d\d20 的历史遗留问题
        // 以上四种地址码，现实身份证真实存在，但民政部历年公布的官方地址码中可能没有查询到
        // 如：440401 450111 等
        // 所以这里需要特殊处理
        // 1980年、1982年版本中，未有制定省辖市市辖区的代码，所有带县的省辖市给予“××××20”的“市区”代码。
        // 1984年版本开始对地级市（前称省辖市）市辖区制定代码，其中“××××01”表示市辖区的汇总码，同时撤销“××××20”的“市区”代码（追溯至1983年）。
        // 1984年版本的市辖区代码分为城区和郊区两类，城区由“××××02”开始排起，郊区由“××××11”开始排起，后来版本已不再采用此方式，已制定的代码继续沿用。
        $suffixes = substr($addressCode, 4, 2);
        switch ($suffixes) {
            case '20':
                $address = '市区';
                break;
            case '01':
                $address = '市辖区';
                break;
            case '02':
                $address = '城区';
                break;
            case '11':
                $address = '郊区';
                break;
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
        }

        return $constellationList[$month]['name'];
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
