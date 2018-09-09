<?php

namespace Jxlwqq\IdValidator;

/**
 * Trait Generator.
 */
trait Generator
{
    /**
     * 生成顺序码
     *
     * @param int $sex 性别
     *
     * @return int|string
     */
    private function _generatorOrderCode($sex)
    {
        $orderCode = $this->_getStrPad($this->_getRandInt(999, 1), 3, '1');
        if ($sex === 1) {
            $orderCode = $orderCode % 2 === 0 ? $orderCode - 1 : $orderCode;
        }
        if ($sex === 0) {
            $orderCode = $orderCode % 2 === 0 ? $orderCode : $orderCode - 1;
        }

        return $orderCode;
    }

    /**
     * 生成出生日期码
     *
     * @param int|string $birthday 出生日期
     *
     * @return string
     */
    private function _generatorBirthdayCode($birthday)
    {
        if ($birthday && is_numeric($birthday)) {
            $year = $this->_getStrPad(substr($birthday, 0, 4), 4);
            $month = $this->_getStrPad(substr($birthday, 4, 2), 2);
            $day = $this->_getStrPad(substr($birthday, 6, 2), 2);
        }
        if (!isset($year) || empty($year) || $year < 1800 || $year > date('Y')) {
            $year = $this->_getStrPad($this->_getRandInt(99, 50), 2, '0');
            $year = '19'.$year;
        }

        if (!isset($month) || empty($month) || $month < 1 || $month > 12) {
            $month = $this->_getStrPad($this->_getRandInt(12, 1), 2, '0');
        }

        if (!isset($day) || empty($day) || $day < 1 || $day > 31) {
            $day = $this->_getStrPad($this->_getRandInt(28, 1), 2, '0');
        }

        if (!checkdate($month, $day, $year)) {
            $year = $this->_getStrPad($this->_getRandInt(99, 50), 2, '0');
            $month = $this->_getStrPad($this->_getRandInt(12, 1), 2, '0');
            $day = $this->_getStrPad($this->_getRandInt(28, 1), 2, '0');
        }
        $birthdayCode = $year.$month.$day;

        return $birthdayCode;
    }

    /**
     * 生成地址码
     *
     * @param string $address 地址（行政区全称）
     *
     * @return false|int|string
     */
    private function _generatorAddressCode($address)
    {
        $addressCode = '';
        if ($address) {
            $addressCode = array_search($address, $this->_addressCodeList);
        }

        if ($addressCode && substr($addressCode, 0, 1) == 8) {
            // 台湾省、香港特别行政区和澳门特别行政区（8字开头）暂缺地市和区县信息
            return $addressCode;
        }

        if ($addressCode) {
            // 省级
            if (substr($addressCode, 2, 4) == '0000') {
                $keys = array_keys($this->_addressCodeList);
                $provinceCode = substr($addressCode, 0, 2);
                $pattern = '/^'.$provinceCode.'\d{2}[^0]{2}$/';
                $result = preg_grep($pattern, $keys);
                $addressCode = $result[array_rand($result)];
            }
            // 市级
            if (substr($addressCode, 4, 2) == '00') {
                $keys = array_keys($this->_addressCodeList);
                $cityCode = substr($addressCode, 0, 4);
                $pattern = '/^'.$cityCode.'[^0]{2}$/';
                $result = preg_grep($pattern, $keys);
                $addressCode = $result[array_rand($result)];
            }
        } else {
            $addressCode = '110100'; // Default value
            for ($i = 0; $i < 100; $i++) {
                $province = $this->_getStrPad($this->_getRandInt(66), 2, '0');
                $city = $this->_getStrPad($this->_getRandInt(20), 2, '0');
                $district = $this->_getStrPad($this->_getRandInt(20), 2, '0');
                $fakeAddressCode = $province.$city.$district;
                if (isset($this->_addressCodeList[$fakeAddressCode])) {
                    $addressCode = $fakeAddressCode;
                    break;
                }
            }
        }

        return $addressCode;
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
        $posWeight = [];
        for ($i = 18; $i > 1; $i--) {
            $weight = pow(2, $i - 1) % 11;
            $posWeight[$i] = $weight;
        }

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
}
