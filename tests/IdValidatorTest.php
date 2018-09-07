<?php
/**
 * Created by PhpStorm.
 * User: jxlwqq
 * Date: 2018/9/6
 * Time: 21:51.
 */

namespace Jxlwqq\IdValidator;

class IdValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testIsValid()
    {
        $idValidator = new IdValidator();
        $this->assertEquals(true, $idValidator->isValid('440308199901101512'));
        $this->assertEquals(false, $idValidator->isValid('440308199901101513'));
        $this->assertEquals(true, $idValidator->isValid('610104620927690'));
        $this->assertEquals(false, $idValidator->isValid('610104620932690'));
    }

    public function testFakeId()
    {
        $idValidator = new IdValidator();
        $this->assertEquals(true, $idValidator->isValid($idValidator->fakeId()));
        $this->assertEquals(true, $idValidator->isValid($idValidator->fakeId(false)));
    }

    public function testGetInfo()
    {
        $idValidator = new IdValidator();
        $this->assertEquals([
            'addressCode'   => '440308',
            'address'       => '广东省深圳市盐田区',
            'birthdayCode'  => '1999-01-10',
            'constellation' => '水瓶座',
            'chineseZodiac' => '卯兔',
            'sex'           => 1,
            'length'        => 18,
            'checkBit'      => '2', ],
            $idValidator->getInfo('440308199901101512'));
        $this->assertEquals(false, $idValidator->isValid('440308199901101513'));

        $idValidator = new IdValidator();
        $this->assertEquals([
            'addressCode'   => '610104',
            'address'       => '陕西省西安市莲湖区',
            'birthdayCode'  => '1962-09-27',
            'constellation' => '天秤座',
            'chineseZodiac' => '寅虎',
            'sex'           => 0,
            'length'        => 15,
            'checkBit'      => '', ],
            $idValidator->getInfo('610104620927690'));
        $this->assertEquals(false, $idValidator->isValid('610104620932690'));
    }
}
