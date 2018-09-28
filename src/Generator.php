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
        $orderCode = $this->_getRandInt(999, 111);

        if ($sex !== null && $sex !== $orderCode % 2) {
            $orderCode -= 1;
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

        return $year.$month.$day;
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

        $addressCode = array_search($address, $this->_addressCodeList);
        $classification = $this->_addressCodeClassification($addressCode);
        switch ($classification) {
            case 'province':
                $provinceCode = substr($addressCode, 0, 2);
                $pattern = '/^'.$provinceCode.'\d{2}[^0]{2}$/';
                $addressCode = $this->_getRandAddressCode($pattern);
                break;
            case 'city':
                $cityCode = substr($addressCode, 0, 4);
                $pattern = '/^'.$cityCode.'[^0]{2}$/';
                $addressCode = $this->_getRandAddressCode($pattern);
                break;
            case 'random':
                $pattern = '/\d{4}[^0]{2}$/';
                $addressCode = $this->_getRandAddressCode($pattern);
                break;
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
            $bodySum += (intval($bodyArray[$j]) * $posWeight[18 - $j]);
        }

        // 生成校验码
        $checkBit = (12 - ($bodySum % 11)) % 11;

        return $checkBit == 10 ? 'X' : $checkBit;
    }

    /**
     * 地址码分类.
     *
     * @param $addressCode
     *
     * @return string
     */
    protected function _addressCodeClassification($addressCode)
    {
        if (!$addressCode) {
            // 全国
            return 'country';
        }
        if (substr($addressCode, 0, 1) == 8) {
            // 港澳台
            return 'special';
        }
        if (substr($addressCode, 2, 4) == '0000') {
            // 省级
            return 'province';
        }
        if (substr($addressCode, 4, 2) == '00') {
            // 市级
            return 'city';
        }
        // 县级
        return 'district';
    }

    /**
     * 获取随机地址码.
     *
     * @param string $pattern 模式
     *
     * @return string
     */
    private function _getRandAddressCode($pattern)
    {
        $keys = array_keys($this->_addressCodeList);
        $result = preg_grep($pattern, $keys);

        return $result[array_rand($result)];
    }
}
