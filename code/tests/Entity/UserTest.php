<?php

namespace App\Tests\Entity;

use App\Entity\HumbleProfile;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class UserTest extends TestCase
{
    public function testConstructorSetsIdAndCreatedAt(): void
    {
        $user = new User();

        $this->assertInstanceOf(Uuid::class, $user->getId());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
    }

    public function testDefaultRolesIncludeRoleUser(): void
    {
        $user = new User();

        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testSetAndGetEmail(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->assertSame('test@example.com', $user->getEmail());
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->assertSame('test@example.com', $user->getUserIdentifier());
    }

    public function testSetAndGetDisplayName(): void
    {
        $user = new User();

        $this->assertNull($user->getDisplayName());

        $user->setDisplayName('John');
        $this->assertSame('John', $user->getDisplayName());

        $user->setDisplayName(null);
        $this->assertNull($user->getDisplayName());
    }

    public function testSetAndGetPassword(): void
    {
        $user = new User();
        $user->setPassword('hashed_password');

        $this->assertSame('hashed_password', $user->getPassword());
    }

    public function testSetAndGetRoles(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $roles = $user->getRoles();
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testRolesAreUnique(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER', 'ROLE_USER']);

        $this->assertCount(1, $user->getRoles());
    }

    public function testIsVerifiedDefaultsFalse(): void
    {
        $user = new User();

        $this->assertFalse($user->isVerified());
    }

    public function testSetIsVerified(): void
    {
        $user = new User();
        $user->setIsVerified(true);

        $this->assertTrue($user->isVerified());
    }

    public function testDefaultAccountStatusIsActive(): void
    {
        $user = new User();

        $this->assertSame(User::STATUS_ACTIVE, $user->getAccountStatus());
        $this->assertFalse($user->isLocked());
    }

    public function testLockSetsStatusAndReason(): void
    {
        $user = new User();
        $user->lock('Policy violation');

        $this->assertTrue($user->isLocked());
        $this->assertSame(User::STATUS_LOCKED, $user->getAccountStatus());
        $this->assertSame('Policy violation', $user->getLockReason());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getLockedAt());
    }

    public function testUnlockResetsStatus(): void
    {
        $user = new User();
        $user->lock('Test');
        $user->unlock();

        $this->assertFalse($user->isLocked());
        $this->assertSame(User::STATUS_ACTIVE, $user->getAccountStatus());
        $this->assertNull($user->getLockedAt());
        $this->assertNull($user->getLockReason());
        $this->assertSame(0, $user->getWarningCount());
    }

    public function testIncrementWarningCount(): void
    {
        $user = new User();

        $this->assertSame(0, $user->getWarningCount());
        $this->assertSame(1, $user->incrementWarningCount());
        $this->assertSame(2, $user->incrementWarningCount());
    }

    public function testEraseCredentialsDoesNotThrow(): void
    {
        $user = new User();
        $user->eraseCredentials();

        $this->assertTrue(true);
    }

    public function testHumbleProfileAssociation(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->assertNull($user->getHumbleProfile());

        $profile = new HumbleProfile();
        $profile->setUser($user);
        $user->setHumbleProfile($profile);

        $this->assertSame($profile, $user->getHumbleProfile());
    }
}
