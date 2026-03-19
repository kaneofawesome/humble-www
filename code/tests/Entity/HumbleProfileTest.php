<?php

namespace App\Tests\Entity;

use App\Entity\HumbleProfile;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class HumbleProfileTest extends TestCase
{
    public function testConstructorSetsIdAndCreatedAt(): void
    {
        $profile = new HumbleProfile();

        $this->assertInstanceOf(Uuid::class, $profile->getId());
        $this->assertInstanceOf(\DateTimeImmutable::class, $profile->getCreatedAt());
    }

    public function testSetAndGetUser(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $profile = new HumbleProfile();
        $profile->setUser($user);

        $this->assertSame($user, $profile->getUser());
    }

    public function testTwoProfilesHaveUniqueIds(): void
    {
        $profile1 = new HumbleProfile();
        $profile2 = new HumbleProfile();

        $this->assertFalse($profile1->getId()->equals($profile2->getId()));
    }
}
