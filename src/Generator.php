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
        $orderCode = rand(111, 999);

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
        $year = $this->_datePad(substr($birthday, 0, 4), 'year');
        $month = $this->_datePad(substr($birthday, 4, 2), 'month');
        $day = $this->_datePad(substr($birthday, 6, 2), 'day');

        if ($year < 1800 || $year > date('Y')) {
            $year = $this->_datePad(rand(1950, date('Y') - 1), 'year');
        }

        if ($month < 1 || $month > 12) {
            $month = $this->_datePad(rand(1, 12), 'month');
        }

        if ($day < 1 || $day > 31) {
            $day = $this->_datePad(rand(1, 28), 'day');
        }

        if (!checkdate((int) $month, (int) $day, (int) $year)) {
            $year = $this->_datePad(rand(1950, date('Y') - 1), 'year');
            $month = $this->_datePad(rand(1, 12), 'month');
            $day = $this->_datePad(rand(1, 28), 'day');
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
            case 'country':
                $pattern = '/\d{4}(?!00)[0-9]{2}$/';
                $addressCode = $this->_getRandAddressCode($pattern);
                break;
            case 'province':
                $provinceCode = substr($addressCode, 0, 2);
                $pattern = '/^'.$provinceCode.'\d{2}(?!00)[0-9]{2}$/';
                $addressCode = $this->_getRandAddressCode($pattern);
                break;
            case 'city':
                $cityCode = substr($addressCode, 0, 4);
                $pattern = '/^'.$cityCode.'(?!00)[0-9]{2}$/';
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
    private function _addressCodeClassification($addressCode)
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

    /**
     * 日期补全.
     *
     * @param string|int $date 日期
     * @param string     $type 类型
     *
     * @return string
     */
    private function _datePad($date, $type = 'year')
    {
        $padLength = $type == 'year' ? 4 : 2;
        $newDate = str_pad($date, $padLength, '0', STR_PAD_LEFT);

        return $newDate;
    }
}
