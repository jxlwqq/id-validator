<?php

namespace Jxlwqq\IdValidator;

/**
 * Trait Helper.
 */
trait Helper
{
    /**
     * 获取位置加权.
     *
     * @return array
     */
    private function _getPosWeight()
    {
        $posWeight = [];
        for ($i = 18; $i > 1; $i--) {
            $weight = pow(2, $i - 1) % 11;
            $posWeight[$i] = $weight;
        }

        return $posWeight;
    }

    /**
     * 获取数字补位.
     *
     * @param $str
     * @param int    $len   长度
     * @param string $chr   补位值
     * @param bool   $right 左右
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
        $addressInfo['province'] = isset($this->_addressCodeList[$provinceAddressCode]) ? $this->_addressCodeList[$provinceAddressCode] : '';

        // 市级信息（港澳台居民居住证无市级信息）
        if ($firstCharacter != '8') {
            $cityAddressCode = substr($addressCode, 0, 4).'00';
            $addressInfo['city'] = isset($this->_addressCodeList[$cityAddressCode]) ? $this->_addressCodeList[$cityAddressCode] : '';
        } else {
            $addressInfo['city'] = '';
        }

        // 县级信息（港澳台居民居住证无县级信息）
        if ($firstCharacter != '8') {
            $addressInfo['district'] = isset($this->_addressCodeList[$addressCode]) ? $this->_addressCodeList[$addressCode] : '';
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
        if (($month == 1 && $day < 20) || ($month == 12 && $day > 21)) {
            return $this->_constellationList['12']['name'];
        } elseif ($month == 1) {
            return $this->_constellationList['01']['name'];
        } elseif ($month == 12) {
            return $this->_constellationList['12']['name'];
        }

        $startDate = $year.'-'.$this->_constellationList[$month]['start_date'];
        $endDate = $year.'-'.$this->_constellationList[$month]['end_date'];
        if (strtotime($startDate) <= $time && strtotime($endDate) >= $time) {
            return $this->_constellationList[$month]['name'];
        }
        $startDate = $year.'-'.$this->_constellationList[$month - 1]['start_date'];
        $endDate = $year.'-'.$this->_constellationList[$month - 1]['end_date'];
        if (strtotime($startDate) <= $time && strtotime($endDate) >= $time) {
            return $this->_constellationList[$month - 1]['name'];
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
     * 检查并拆分身份证号.
     *
     * @param string $id 身份证号
     *
     * @return array|bool
     */
    private function _checkIdArgument($id)
    {
        $id = strtoupper($id);
        $length = strlen($id);
        $code = false;
        switch ($length) {
            case 18:
                $code = [
                    'body'         => substr($id, 0, 17),
                    'addressCode'  => substr($id, 0, 6),
                    'birthdayCode' => substr($id, 6, 8),
                    'order'        => substr($id, 14, 3),
                    'checkBit'     => substr($id, -1),
                    'type'         => 18,
                ];
                break;
            case 15:
                $code = [
                    'body'         => $id,
                    'addressCode'  => substr($id, 0, 6),
                    'birthdayCode' => '19'.substr($id, 6, 6),
                    'order'        => substr($id, 12, 3),
                    'checkBit'     => '',
                    'type'         => 15,
                ];
                break;
        }

        return $code;
    }

    /**
     * 检查地址码
     *
     * @param string $addressCode 地址码
     *
     * @return bool
     */
    private function _checkAddressCode($addressCode)
    {
        $addressInfo = $this->_getAddressInfo($addressCode);

        return $addressInfo ? true : false;
    }

    /**
     * 检查出生日期码
     *
     * @param string $birthdayCode 出生日期码
     *
     * @return bool
     */
    private function _checkBirthdayCode($birthdayCode)
    {
        $year = intval(substr($birthdayCode, 0, 4));
        $month = intval(substr($birthdayCode, 4, 2));
        $day = intval(substr($birthdayCode, -2));

        if ($year < 1800) {
            return false;
        }
        if ($month > 12 || $month === 0 || $day > 31 || $day === 0) {
            return false;
        }

        return true;
    }

    /**
     * 检查顺序码
     *
     * @param string $orderCode 顺序码
     *
     * @return bool
     */
    private function _checkOrderCode($orderCode)
    {
        if (strlen($orderCode) == 3) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 生成随机数.
     *
     * @param int $max 最大值
     * @param int $min 最小值
     *
     * @return int
     */
    private function _generatorRandInt($max, $min = 1)
    {
        return rand($min, $max);
    }

    /**
     * 生成校验码
     * 详细计算方法 @lint https://zh.wikipedia.org/wiki/中华人民共和国公民身份号码
     *
     * @param string $body 身份证号 body 部分
     *
     * @return int|string
     */
    private function _generatorCheckBit($body)
    {
        // 位置加权
        $posWeight = $this->_getPosWeight();

        // 累身份证号 body 部分与位置加权的积
        $bodySum = 0;
        $bodyArray = str_split($body);
        $count = count($bodyArray);
        for ($j = 0; $j < $count; $j++) {
            $bodySum += (intval($bodyArray[$j], 10) * $posWeight[18 - $j]);
        }

        // 生成校验码
        $checkBit = 12 - ($bodySum % 11);
        if ($checkBit == 10) {
            $checkBit = 'X';
        } elseif ($checkBit > 10) {
            $checkBit = $checkBit % 11;
        }

        return $checkBit;
    }

    /**
     * 生成地址码
     *
     * @return string
     */
    private function _generatorAddressCode()
    {
        $addressCode = '110100';
        for ($i = 0; $i < 100; $i++) {
            $province = $this->_getStrPad($this->_generatorRandInt(66), 2, '0');
            $city = $this->_getStrPad($this->_generatorRandInt(20), 2, '0');
            $district = $this->_getStrPad($this->_generatorRandInt(20), 2, '0');
            $fakeAddressCode = $province.$city.$district;
            if (isset($this->_addressCodeList[$fakeAddressCode])) {
                $addressCode = $fakeAddressCode;
                break;
            }
        }

        return $addressCode;
    }

    /**
     * 生成出生日期码
     *
     * @return string
     */
    private function _generatorBirthdayCode()
    {
        $year = $this->_getStrPad($this->_generatorRandInt(99, 50), 2, '0');
        $month = $this->_getStrPad($this->_generatorRandInt(12, 1), 2, '0');
        $day = $this->_getStrPad($this->_generatorRandInt(28, 1), 2, '0');
        $year = '19'.$year;
        $birthdayCode = $year.$month.$day;

        return $birthdayCode;
    }
}
