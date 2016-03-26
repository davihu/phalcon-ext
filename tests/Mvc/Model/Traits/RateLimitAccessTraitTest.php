<?php

namespace PhalconExt\Tests\Mvc\Model\Traits;

use PhalconExt\Mvc\Model\Traits\RateLimitAccessTrait;

class RateLimitAccessTraitTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var RateLimitAccessTraitMock
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new RateLimitAccessTraitMock();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    /**
     * @covers PhalconExt\Mvc\Model\Traits\RateLimitAccessTrait::getAccessRateLimitPeriod
     * @covers PhalconExt\Mvc\Model\Traits\RateLimitAccessTrait::setAccessRateLimitPeriod
     */
    public function testGetSetAccessRateLimitPeriod()
    {
        $this->assertSame('hourly', $this->object->getAccessRateLimitPeriod());
        $this->assertTrue($this->object->setAccessRateLimitPeriod('DIALY'));
        $this->assertSame('dialy', $this->object->getAccessRateLimitPeriod());
        $this->assertTrue($this->object->setAccessRateLimitPeriod('Hourly', false));
        $this->assertSame('hourly', $this->object->getAccessRateLimitPeriod());
    }

    /**
     * @covers PhalconExt\Mvc\Model\Traits\RateLimitAccessTrait::setAccessRateLimitPeriod
     */
    public function testSetAccessRateLimitPeriodException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->object->setAccessRateLimitPeriod('aaa');
    }

    /**
     * @covers PhalconExt\Mvc\Model\Traits\RateLimitAccessTrait::getRemainingAccessRateLimit
     */
    public function testGetRemainingAccessRateLimitMax()
    {
        $this->assertEquals(3600, $this->object->getRemainingAccessRateLimit(3600));
        $this->assertEquals(RateLimitAccessTraitMock::DEFAULT_MAX_ACCESS_RATE_LIMIT, $this->object->getRemainingAccessRateLimit());
    }

    /**
     * @covers PhalconExt\Mvc\Model\Traits\RateLimitAccessTrait::getRemainingAccessRateLimit
     * @covers PhalconExt\Mvc\Model\Traits\RateLimitAccessTrait::incrementAccessRateLimit
     */
    public function testIncrementAccessRateLimit()
    {
        $this->assertEquals(3, $this->object->getRemainingAccessRateLimit());
        $this->assertTrue($this->object->incrementAccessRateLimit());
        $this->assertEquals(2, $this->object->getRemainingAccessRateLimit());
        $this->assertTrue($this->object->incrementAccessRateLimit());
        $this->assertEquals(1, $this->object->getRemainingAccessRateLimit());
        $this->assertTrue($this->object->incrementAccessRateLimit());
        $this->assertEquals(0, $this->object->getRemainingAccessRateLimit());
    }

}

class RateLimitAccessTraitMock
{

    use RateLimitAccessTrait;

    const DEFAULT_MAX_ACCESS_RATE_LIMIT = 3;
    const DEFAULT_ACCESS_RATE_LIMIT_PERIOD = 0;

    public function save()
    {
        return true;
    }

}
