<?php

namespace Jxlwqq\IdValidator;

use DateTime;

/**
 * Trait Checker.
 */
trait Checker
{
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

        if ($length === 15) {
            return $this->_generateShortType($id);
        } elseif ($length === 18) {
            return $this->_generatelongType($id);
        }

        return false;
    }

    /**
     * Generation for the short type.
     *
     * @param string $id 身份证号
     *
     * @return array
     */
    private function _generateShortType($id)
    {
        preg_match('/(.{6})(.{6})(.{3})/', $id, $matches);

        return [
            'body'         => $matches[0],
            'addressCode'  => $matches[1],
            'birthdayCode' => '19'.$matches[2],
            'order'        => $matches[3],
            'checkBit'     => '',
            'type'         => 15,
        ];
    }

    /**
     * Generation for the long type.
     *
     * @param string $id 身份证号
     *
     * @return array
     */
    private function _generateLongType($id)
    {
        preg_match('/((.{6})(.{8})(.{3}))(.)/', $id, $matches);

        return [
            'body'         => $matches[1],
            'addressCode'  => $matches[2],
            'birthdayCode' => $matches[3],
            'order'        => $matches[4],
            'checkBit'     => $matches[5],
            'type'         => 18,
        ];
    }

    /**
     * 检查地址码
     *
     * @param string $addressCode  地址码
     * @param string $birthdayCode 出生日期码
     *
     * @return bool
     */
    private function _checkAddressCode($addressCode, $birthdayCode)
    {
        return (bool) $this->_getAddressInfo($addressCode, $birthdayCode);
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
        return strlen($orderCode) === 3;
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
        $date = DateTime::createFromFormat($format = 'Ymd', $birthdayCode);

        return $date->format($format) === $birthdayCode && (int) $date->format('Y') >= 1800;
    }
}
