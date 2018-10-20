<?php
/**
 * Created by PhpStorm.
 * User: jxlwqq
 * Date: 2018/9/6
 * Time: 21:51.
 */

namespace Jxlwqq\IdValidator\Tests;

use Jxlwqq\IdValidator\IdValidator;
use PHPUnit\Framework\TestCase;

class IdValidatorTest extends TestCase
{
    /**
     * @var \Jxlwqq\IdValidator\IdValidator
     */
    private $idValidator;

    protected function setUp()
    {
        $this->idValidator = new IdValidator();
    }

    public function testIsValid()
    {
        $this->assertFalse($this->idValidator->isValid('44030819990110'));     // 号码位数不合法
        $this->assertFalse($this->idValidator->isValid('111111199901101512')); // 地址码不合法
        $this->assertFalse($this->idValidator->isValid('440308199902301512')); // 出生日期码错不合法
        $this->assertFalse($this->idValidator->isValid('440308199901101513')); // 验证码不合法
        $this->assertFalse($this->idValidator->isValid('610104620932690'));    // 验证码不合法
        $this->assertTrue($this->idValidator->isValid('440308199901101512'));
        $this->assertTrue($this->idValidator->isValid('610104620927690'));
    }

    public function testFakeId()
    {
        $this->assertTrue($this->idValidator->isValid($this->idValidator->fakeId()));
        $this->assertTrue($this->idValidator->isValid($this->idValidator->fakeId(false)));
        $this->assertTrue($this->idValidator->isValid($this->idValidator->fakeId(true, '上海市', '2000', 1)));
        $this->assertTrue($this->idValidator->isValid($this->idValidator->fakeId(true, '黄浦区', '2001', 0)));
        $this->assertTrue($this->idValidator->isValid($this->idValidator->fakeId(true, '江苏省', '200001', 1)));
        $this->assertTrue($this->idValidator->isValid($this->idValidator->fakeId(true, '南京市', '2002', 0)));
        $this->assertTrue($this->idValidator->isValid($this->idValidator->fakeId(true, '秦淮区', '2003', 0)));
        $this->assertTrue($this->idValidator->isValid($this->idValidator->fakeId(true, '台湾省', '20181010', 0)));
        $this->assertTrue($this->idValidator->isValid($this->idValidator->fakeId(true, '香港特别行政区', '20181010', 1)));
        $this->assertTrue($this->idValidator->isValid($this->idValidator->fakeId(true, '澳门特别行政区', '20181111', 0)));
    }

    public function testGetInfo()
    {
        $this->assertEquals(
            [
            'addressCode'   => '440308',
            'abandoned'     => 0,
            'address'       => '广东省深圳市盐田区',
            'birthdayCode'  => '1999-01-10',
            'constellation' => '摩羯座',
            'chineseZodiac' => '卯兔',
            'sex'           => 1,
            'length'        => 18,
            'checkBit'      => '2', ],
            $this->idValidator->getInfo('440308199901101512')
        );
        $this->assertFalse($this->idValidator->isValid('440308199901101513'));

        $this->assertEquals(
            [
            'addressCode'   => '610104',
            'abandoned'     => 0,
            'address'       => '陕西省西安市莲湖区',
            'birthdayCode'  => '1962-09-27',
            'constellation' => '天秤座',
            'chineseZodiac' => '寅虎',
            'sex'           => 0,
            'length'        => 15,
            'checkBit'      => '', ],
            $this->idValidator->getInfo('610104620927690')
        );
        $this->assertFalse($this->idValidator->isValid('610104620932690'));
    }

    public function testUpgradeId()
    {
        $this->assertEquals('610104196209276908', $this->idValidator->upgradeId('610104620927690'));
    }
}
