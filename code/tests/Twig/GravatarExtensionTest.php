<?php

namespace App\Tests\Twig;

use App\Twig\GravatarExtension;
use PHPUnit\Framework\TestCase;

class GravatarExtensionTest extends TestCase
{
    private GravatarExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new GravatarExtension();
    }

    public function testGeneratesCorrectUrlWithKnownHash(): void
    {
        $url = $this->extension->getGravatarUrl('test@example.com');
        $expectedHash = md5('test@example.com');

        $this->assertSame(
            "https://www.gravatar.com/avatar/{$expectedHash}?s=80&d=mp",
            $url
        );
    }

    public function testDefaultSizeIs80(): void
    {
        $url = $this->extension->getGravatarUrl('test@example.com');

        $this->assertStringContainsString('s=80', $url);
    }

    public function testCustomSize(): void
    {
        $url = $this->extension->getGravatarUrl('test@example.com', 200);

        $this->assertStringContainsString('s=200', $url);
    }

    public function testDefaultImageParameter(): void
    {
        $url = $this->extension->getGravatarUrl('test@example.com', 80, 'identicon');

        $this->assertStringContainsString('d=identicon', $url);
    }

    public function testEmailIsTrimmedAndLowercased(): void
    {
        $url1 = $this->extension->getGravatarUrl('test@example.com');
        $url2 = $this->extension->getGravatarUrl('  TEST@EXAMPLE.COM  ');

        $this->assertSame($url1, $url2);
    }

    public function testRegistersTwigFunction(): void
    {
        $functions = $this->extension->getFunctions();

        $this->assertCount(1, $functions);
        $this->assertSame('gravatar', $functions[0]->getName());
    }
}
