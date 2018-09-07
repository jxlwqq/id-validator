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
    }

    public function testFakeId()
    {
        $idValidator = new IdValidator();
        $this->assertEquals(true, $idValidator->isValid($idValidator->fakeId()));
        $this->assertEquals(true, $idValidator->isValid($idValidator->fakeId(true)));
    }

    public function testGetInfo()
    {
        $idValidator = new IdValidator();
        $this->assertEquals([
            'addressCode'  => '440308',
            'address'      => '广东省深圳市盐田区',
            'birthdayCode' => '1999-01-10',
            'sex'          => 1,
            'length'       => 18,
            'checkBit'     => '2', ],
            $idValidator->getInfo('440308199901101512'));
        $this->assertEquals(false, $idValidator->isValid('440308199901101513'));
    }
}
