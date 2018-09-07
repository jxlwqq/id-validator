<?php

namespace Jxlwqq\IdValidator;

/**
 * Class IdValidator.
 */
class IdValidator
{
    private $_addressCodeList = [];

    /**
     * IdValidator constructor.
     */
    public function __construct()
    {
        $this->_addressCodeList = include __DIR__.'/../data/addressCode.php';
    }

    /**
     * 验证身份证号合法性.
     *
     * @param string $id 身份证号
     *
     * @return bool
     */
    public function isValid($id)
    {
        // 基础验证
        $code = $this->_checkIdArgument($id);
        if (empty($code)) {
            return false;
        }

        // 验证：地址码
        if (!$this->_checkAddressCode($code['addressCode'])) {
            return false;
        }

        // 验证：出生日期码
        if (!$this->_checkBirthdayCode($code['birthdayCode'])) {
            return false;
        }

        // 验证：顺序码
        if (!$this->_checkOrderCode($code['order'])) {
            return false;
        }

        // 15位身份证不含校验码
        if ($code['type'] === 15) {
            return true;
        }

        // 验证：校验码
        $checkBit = $this->_generatorCheckBit($code['body']);

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
     * @param string $id 身份证号
     *
     * @return array|bool
     */
    public function getInfo($id)
    {
        // 验证有效性
        if ($this->isValid($id) === false) {
            return false;
        }
        $code = $this->_checkIdArgument($id);
        $addressInfo = $this->_getAddressInfo($code['addressCode']);
        $info = [];
        $info['addressCode'] = $code['addressCode'];
        $info['address'] = is_array($addressInfo) ? implode($addressInfo) : '';
        $info['birthdayCode'] = date('Y-m-d', strtotime($code['birthdayCode']));
        $info['constellation'] = $this->_getConstellation($code['birthdayCode']);
        $info['chineseZodiac'] = $this->_getChineseZodiac($code['birthdayCode']);
        $info['sex'] = ($code['order'] % 2 === 0 ? 0 : 1);
        $info['length'] = $code['type'];
        $info['checkBit'] = $code['checkBit'];

        return $info;
    }

    /**
     * 生成假数据.
     *
     * @param bool $eighteen 是否为 18 位
     *
     * @return string
     */
    public function fakeId($eighteen = true)
    {
        // 生成地址码
        $addressCode = $this->_generatorAddressCode();

        // 出生日期码
        $birthdayCode = $this->_generatorBirthdayCode();

        if (!$eighteen) {
            return $addressCode.substr($birthdayCode, 2)
                .$this->_getStrPad($this->_generatorRandInt(999, 1), 3, '1');
        }

        $body = $addressCode.$birthdayCode.$this->_getStrPad($this->_generatorRandInt(999, 1), 3, '1');

        $checkBit = $this->_generatorCheckBit($body);

        return $body.$checkBit;
    }

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
        // 省级信息
        $provinceAddressCode = substr($addressCode, 0, 2).'0000';
        $addressInfo['province'] = isset($this->_addressCodeList[$provinceAddressCode]) ? $this->_addressCodeList[$provinceAddressCode] : '';

        // 市级信息
        $cityAddressCode = substr($addressCode, 0, 4).'00';
        $addressInfo['city'] = isset($this->_addressCodeList[$cityAddressCode]) ? $this->_addressCodeList[$cityAddressCode] : '';

        // 县级信息
        $addressInfo['district'] = isset($this->_addressCodeList[$addressCode]) ? $this->_addressCodeList[$addressCode] : '';

        if (empty($addressInfo)) {
            return false;
        } else {
            return $addressInfo;
        }
    }

    /**
     * 获取星座信息
     * @param string $birthdayCode 出生日期码
     * @return string
     */
    public function _getConstellation($birthdayCode)
    {
        $constellationList = $this->_getConstellationList();

        $time = strtotime($birthdayCode);
        $year = substr($birthdayCode, 0, 4);
        $month = substr($birthdayCode, 4, 2);
        $day = substr($birthdayCode, 6, 2);

        // 1月份与12月份特殊处理
        if (($month == 1 && $day < 20) || ($month == 12 && $day > 21)) {
            return $constellationList[12]['name'];
        } elseif ($month == 1) {
            return $constellationList[1]['name'];
        } elseif ($month == 12) {
            return $constellationList[12]['name'];
        }

        $startDate = $year.'-'.$constellationList[$month]['start_date'];
        $endDate = $year.'-'.$constellationList[$month]['end_date'];
        if (strtotime($startDate) <= $time && strtotime($endDate) >= $time) {
            return $constellationList[$month]['name'];
        }
        $startDate = $year.'-'.$constellationList[$month - 1]['start_date'];
        $endDate = $year.'-'.$constellationList[$month - 1]['end_date'];
        if (strtotime($startDate) <= $time && strtotime($endDate) >= $time) {
            return $constellationList[$month - 1]['name'];
        }

        return '';
    }

    /**
     * 获取生肖信息
     * @param string $birthdayCode 出生日期码
     * @return mixed
     */
    public function _getChineseZodiac($birthdayCode)
    {
        $chineseZodiacList = $this->_getChineseZodiacList();
        $start = 1900; // 子鼠
        $end = substr($birthdayCode, 0, 4);
        $key = ($end - $start) % 12;
        $key = $key >= 0 ? $key : ($key + 12);

        return $chineseZodiacList[$key];
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

    /**
     * 获取生肖列表
     * @return array
     */
    private function _getChineseZodiacList()
    {
        return ['子鼠', '丑牛', '寅虎', '卯兔', '辰龙', '巳蛇', '午马', '未羊', '申猴', '酉鸡', '戌狗', '亥猪'];
    }

    /**
     * 获取星座列表
     * @return array
     */
    private function _getConstellationList()
    {
        return [
            '01' => [
                'name'       => '水瓶座',
                'start_date' => '01-20',
                'end_date'   => '02-18',
            ],
            '02' => [
                'name'       => '双鱼座',
                'start_date' => '02-19',
                'end_date'   => '03-20',
            ],
            '03' => [
                'name'       => '白羊座',
                'start_date' => '03-21',
                'end_date'   => '04-19',
            ],
            '04' => [
                'name'       => '金牛座',
                'start_date' => '04-20',
                'end_date'   => '05-20',
            ], '05' => [
                'name'       => '双子座',
                'start_date' => '05-21',
                'end_date'   => '06-21',
            ], '06' => [
                'name'       => '巨蟹座',
                'start_date' => '06-22',
                'end_date'   => '07-22',
            ], '07' => [
                'name'       => '狮子座',
                'start_date' => '07-23',
                'end_date'   => '08-22',
            ], '08' => [
                'name'       => '处女座',
                'start_date' => '08-23',
                'end_date'   => '09-22',
            ], '09' => [
                'name'       => '天秤座',
                'start_date' => '09-23',
                'end_date'   => '10-23',
            ], '10' => [
                'name'       => '天蝎座',
                'start_date' => '10-24',
                'end_date'   => '11-22',
            ], '11' => [
                'name'       => '射手座',
                'start_date' => '11-23',
                'end_date'   => '12-21',
            ], '12' => [
                'name'       => '水瓶座',
                'start_date' => '12-22',
                'end_date'   => '01-19',
            ],
        ];
    }
}
