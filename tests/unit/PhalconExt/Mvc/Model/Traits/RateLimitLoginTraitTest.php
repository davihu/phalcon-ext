<?php

namespace PhalconExt\Test\Mvc\Model\Traits;

use PhalconExt\Mvc\Model\Traits\RateLimitLoginTrait;

class RateLimitLoginTraitTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var RateLimitLoginTraitMock
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new RateLimitLoginTraitMock();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    /**
     * @covers PhalconExt\Mvc\Model\Traits\RateLimitLoginTrait::getFailedLoginAttempts
     */
    public function testGetFailedLoginAttempts()
    {
        $mock = $this->getMockForTrait('PhalconExt\Mvc\Model\Traits\RateLimitLoginTrait');
        $this->assertSame(0, $mock->getFailedLoginAttempts());
    }

    /**
     * @covers PhalconExt\Mvc\Model\Traits\RateLimitLoginTrait::getFailedLoginAttempts
     * @covers PhalconExt\Mvc\Model\Traits\RateLimitLoginTrait::hasAccountLocked
     * @covers PhalconExt\Mvc\Model\Traits\RateLimitLoginTrait::incrementFailedLoginAttempts
     */
    public function testAutoLock()
    {
        $this->assertSame(0, $this->object->getFailedLoginAttempts());
        $this->assertFalse($this->object->hasAccountLocked());
        $this->assertTrue($this->object->incrementFailedLoginAttempts());
        $this->assertSame(1, $this->object->getFailedLoginAttempts());
        $this->assertFalse($this->object->hasAccountLocked());
        $this->assertTrue($this->object->incrementFailedLoginAttempts());
        $this->assertSame(2, $this->object->getFailedLoginAttempts());
        $this->assertFalse($this->object->hasAccountLocked());
        $this->assertTrue($this->object->incrementFailedLoginAttempts());
        $this->assertSame(3, $this->object->getFailedLoginAttempts());
        $this->assertGreaterThan(0, $this->object->hasAccountLocked());
    }

    /**
     * @covers PhalconExt\Mvc\Model\Traits\RateLimitLoginTrait::getFailedLoginAttempts
     * @covers PhalconExt\Mvc\Model\Traits\RateLimitLoginTrait::hasAccountLocked
     * @covers PhalconExt\Mvc\Model\Traits\RateLimitLoginTrait::incrementFailedLoginAttempts
     */
    public function testAutoRelease()
    {
        $this->assertFalse($this->object->hasAccountLocked());
        $this->assertTrue($this->object->incrementFailedLoginAttempts());
        $this->assertTrue($this->object->incrementFailedLoginAttempts());
        $this->assertTrue($this->object->incrementFailedLoginAttempts());
        $this->assertGreaterThan(0, $this->object->hasAccountLocked());
        sleep(RateLimitLoginTraitMock::ACCOUNT_LOCK_DURATION + 1);
        $this->assertFalse($this->object->hasAccountLocked());
        $this->assertSame(0, $this->object->getFailedLoginAttempts());
    }

    /**
     * @covers PhalconExt\Mvc\Model\Traits\RateLimitLoginTrait::getFailedLoginAttempts
     * @covers PhalconExt\Mvc\Model\Traits\RateLimitLoginTrait::hasAccountLocked
     * @covers PhalconExt\Mvc\Model\Traits\RateLimitLoginTrait::incrementFailedLoginAttempts
     * @covers PhalconExt\Mvc\Model\Traits\RateLimitLoginTrait::resetFailedLoginAttempts
     */
    public function testResetLock()
    {
        $this->assertFalse($this->object->hasAccountLocked());
        $this->assertTrue($this->object->incrementFailedLoginAttempts());
        $this->assertTrue($this->object->incrementFailedLoginAttempts());
        $this->assertTrue($this->object->incrementFailedLoginAttempts());
        $this->assertGreaterThan(0, $this->object->hasAccountLocked());
        $this->assertTrue($this->object->resetFailedLoginAttempts());
        $this->assertFalse($this->object->hasAccountLocked());
        $this->assertSame(0, $this->object->getFailedLoginAttempts());
    }

}

class RateLimitLoginTraitMock
{

    use RateLimitLoginTrait;

    const MAX_FAILED_LOGIN_ATTEMPTS = 3;
    const ACCOUNT_LOCK_DURATION = 2;

    public function save()
    {
        return true;
    }

}
