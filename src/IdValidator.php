<?php

namespace Jxlwqq\IdValidator;

/**
 * Class IdValidator.
 */
class IdValidator
{
    use Helper;

    private $_addressCodeList = []; // 所有地址码数据（现行+废弃）
    private $_abandonedAddressCodeList = []; // 废弃地址码数据
    private $_constellationList = [];
    private $_chineseZodiacList = [];


    /**
     * IdValidator constructor.
     */
    public function __construct()
    {
        $addressCodeList = include __DIR__.'/../data/addressCode.php';
        $abandonedAddressCodeList = include __DIR__.'/../data/abandonedAddressCode.php';
        $this->_abandonedAddressCodeList = $abandonedAddressCodeList;
        $this->_addressCodeList =$addressCodeList + $abandonedAddressCodeList;
        $this->_constellationList = include __DIR__.'/../data/constellation.php';
        $this->_chineseZodiacList = include __DIR__.'/../data/chineseZodiac.php';
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
        $info['abandoned'] = isset($this->_abandonedAddressCodeList[$code['addressCode']]) ? 1 : 0;
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
}
