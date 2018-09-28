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
        $month = substr($birthdayCode, 4, 2);
        $day = substr($birthdayCode, 6, 2);
        switch ($month) {
            case 1:
                $constellation = $day >= 21 ? '水瓶座' : '摩羯座';
                break;
            case 2:
                $constellation = $day >= 20 ? '双鱼座' : '水瓶座';
                break;
            case 3:
                $constellation = $day >= 21 ? '白羊座' : '双鱼座';
                break;
            case 4:
                $constellation = $day >= 21 ? '金牛座' : '白羊座';
                break;
            case 5:
                $constellation = $day >= 22 ? '双子座' : '金牛座';
                break;
            case 6:
                $constellation = $day >= 23 ? '巨蟹座' : '双子座';
                break;
            case 7:
                $constellation = $day >= 24 ? '狮子座' : '巨蟹座';
                break;
            case 8:
                $constellation = $day >= 24 ? '处女座' : '狮子座';
                break;
            case 9:
                $constellation = $day >= 24 ? '天秤座' : '处女座';
                break;
            case 10:
                $constellation = $day >= 24 ? '天蝎座' : '天秤座';
                break;
            case 11:
                $constellation = $day >= 23 ? '射手座' : '天蝎座';
                break;
            case 12:
                $constellation = $day >= 22 ? '摩羯座' : '射手座';
                break;
            default:
                $constellation = '';
        }
        return $constellation;
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
        $chineseZodiacList = [
            '子鼠',
            '丑牛',
            '寅虎',
            '卯兔',
            '辰龙',
            '巳蛇',
            '午马',
            '未羊',
            '申猴',
            '酉鸡',
            '戌狗',
            '亥猪',
        ];
        $start = 1900; // 子鼠
        $end = substr($birthdayCode, 0, 4);
        $key = ($end - $start) % 12;
        $key = $key >= 0 ? $key : ($key + 12);

        return $chineseZodiacList[$key];
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
     * @param string|int $str 字符串
     * @param int $len 长度
     * @param string $chr 补位值
     * @param bool $right 左右
     *
     * @return string
     */
    private function _getStrPad($str, $len = 2, $chr = '0', $right = false)
    {
        return str_pad((string)$str, $len, $chr, $right === true ? STR_PAD_RIGHT : STR_PAD_LEFT);
    }
}
