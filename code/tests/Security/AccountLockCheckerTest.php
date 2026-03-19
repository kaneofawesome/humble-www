<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\AccountLockChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\InMemoryUser;

class AccountLockCheckerTest extends TestCase
{
    private AccountLockChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new AccountLockChecker();
    }

    public function testActiveUserPassesPreAuth(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('hashed');

        $this->checker->checkPreAuth($user);

        $this->assertTrue(true);
    }

    public function testLockedUserFailsPreAuth(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('hashed');
        $user->lock('Policy violation');

        $this->expectException(CustomUserMessageAccountStatusException::class);

        $this->checker->checkPreAuth($user);
    }

    public function testNonUserObjectPassesPreAuth(): void
    {
        $user = new InMemoryUser('test', 'password');

        $this->checker->checkPreAuth($user);

        $this->assertTrue(true);
    }

    public function testPostAuthDoesNotThrow(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('hashed');

        $this->checker->checkPostAuth($user);

        $this->assertTrue(true);
    }
}
