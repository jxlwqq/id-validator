<?php
/**
 * Created by PhpStorm.
 * User: jxlwqq
 * Date: 2018/9/6
 * Time: 19:03.
 */

namespace Jxlwqq\IdValidator;

class IdValidator
{
    private $addressCodeList = [];

    public function __construct()
    {
        $this->addressCodeList = require __DIR__.'/../data/addressCode.php';
    }

    /**
     * 验证身份证号合法性.
     *
     * @param string $id
     *
     * @return bool
     */
    public function isValid($id)
    {
        // 基础验证
        $code = $this->checkIdArgument($id);
        if (!$code) {
            return false;
        }

        // 验证：地址码
        if (!$this->checkAddressCode($code['addressCode'])) {
            return false;
        }

        // 验证：出生日期码
        if (!$this->checkBirthdayCode($code['birthdayCode'])) {
            return false;
        }

        // 验证：顺序码
        if (!$this->checkOrderCode($code['order'])) {
            return false;
        }

        // 15位身份证不含校验码
        if ($code['type'] === 15) {
            return true;
        }

        // 验证：校验码
        // 详细计算方法，点击百科：
        // https://zh.wikipedia.org/wiki/中华人民共和国公民身份号码
        $checkBit = $this->generatorCheckBit($code['body']);

        // 检查校验码
        if ($checkBit != $code['checkBit']) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 获取身份证信息.
     *
     * @param $id
     *
     * @return array|bool
     */
    public function getInfo($id)
    {
        // 验证有效性
        if ($this->isValid($id) === false) {
            return false;
        }
        $code = $this->checkIdArgument($id);
        $info = [];
        $info['addressCode'] = $code['addressCode'];
        $info['address'] = implode($this->getAddressInfo($code['addressCode']));
        $info['birthdayCode'] = date('Y-m-d', strtotime($code['birthdayCode']));
        $info['sex'] = ($code['order'] % 2 === 0 ? 0 : 1);
        $info['length'] = $code['type'];
        if ($code['type'] === 18) {
            $info['checkBit'] = $code['checkBit'];
        }

        return $info;
    }

    /**
     * 生成假数据.
     *
     * @param bool $fifteen
     *
     * @return string
     */
    public function fakeId($fifteen = false)
    {
        // 生成地址码
        $addressCode = $this->generatorAddressCode();

        // 出生日期码
        $birthdayCode = $this->generatorBirthdayCode();

        if ($fifteen) {
            return $addressCode.substr($birthdayCode, 2)
                .$this->getStrPad($this->generatorRandInt(999, 1), 3, '1');
        }

        $body = $addressCode.$birthdayCode.$this->getStrPad($this->generatorRandInt(999, 1), 3, '1');

        $checkBit = $this->generatorCheckBit($body);

        return $body.$checkBit;
    }

    /**
     * 获取位置加权.
     *
     * @return array
     */
    private function getPosWeight()
    {
        $posWeight = [];
        for ($i = 18; $i > 1; $i--) {
            $wei = pow(2, $i - 1) % 11;
            $posWeight[$i] = $wei;
        }

        return $posWeight;
    }

    /**
     * 获取数字补位.
     *
     * @param $str
     * @param int    $len
     * @param string $chr
     * @param bool   $right
     *
     * @return string
     */
    private function getStrPad($str, $len = 2, $chr = '0', $right = false)
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
     * @param $addressCode
     *
     * @return bool|mixed|string
     */
    private function getAddressInfo($addressCode)
    {
        $addressInfo = [];
        // 省级信息
        $provinceAddressCode = substr($addressCode, 0, 2).'0000';
        $addressInfo['province'] = isset($this->addressCodeList[$provinceAddressCode]) ? $this->addressCodeList[$provinceAddressCode] : '';

        // 市级信息
        $cityAddressCode = substr($addressCode, 0, 4).'00';
        $addressInfo['city'] = isset($this->addressCodeList[$cityAddressCode]) ? $this->addressCodeList[$cityAddressCode] : '';

        // 县级信息
        $addressInfo['district'] = isset($this->addressCodeList[$addressCode]) ? $this->addressCodeList[$addressCode] : '';

        if ($addressInfo) {
            return $addressInfo;
        } else {
            return false;
        }
    }

    /**
     * 检查并拆分身份证号.
     *
     * @param $id
     *
     * @return array|bool
     */
    private function checkIdArgument($id)
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
                    'type'         => 15,
                ];
                break;
        }

        return $code;
    }

    /**
     * 检查地址码
     *
     * @param $addressCode
     *
     * @return bool
     */
    private function checkAddressCode($addressCode)
    {
        $addressInfo = $this->getAddressInfo($addressCode);

        return $addressInfo ? true : false;
    }

    /**
     * 检查出生日期码
     *
     * @param $birthdayCode
     *
     * @return bool
     */
    private function checkBirthdayCode($birthdayCode)
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
     * @param $orderCode
     *
     * @return bool
     */
    private function checkOrderCode($orderCode)
    {
        // 暂无需检测
        return true;
    }

    /**
     * 生成随机数.
     *
     * @param $max
     * @param int $min
     *
     * @return int
     */
    private function generatorRandInt($max, $min = 1)
    {
        return rand($min, $max);
    }

    /**
     * 生成校验码
     *
     * @param $body
     *
     * @return int|string
     */
    private function generatorCheckBit($body)
    {
        // 位置加权
        $posWeight = $this->getPosWeight();

        // 累身份证号 body 部分与位置加权的积
        $bodySum = 0;
        $bodyArray = str_split($body);
        for ($j = 0; $j < count($bodyArray); $j++) {
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
    private function generatorAddressCode()
    {
        $addressCode = '110100';
        for ($i = 0; $i < 100; $i++) {
            $province = $this->getStrPad($this->generatorRandInt(66), 2, '0');
            $city = $this->getStrPad($this->generatorRandInt(20), 2, '0');
            $district = $this->getStrPad($this->generatorRandInt(20), 2, '0');
            $fakeAddressCode = $province.$city.$district;
            if (isset($this->addressCodeList[$fakeAddressCode])) {
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
    private function generatorBirthdayCode()
    {
        $year = $this->getStrPad($this->generatorRandInt(99, 50), 2, '0');
        $month = $this->getStrPad($this->generatorRandInt(12, 1), 2, '0');
        $day = $this->getStrPad($this->generatorRandInt(28, 1), 2, '0');
        $year = '19'.$year;
        $birthdayCode = $year.$month.$day;

        return $birthdayCode;
    }
}
