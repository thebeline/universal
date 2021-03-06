<?php
/**
 * This file is part of the Universal package.
 *
 * (c) Yo-An Lin <yoanlin93@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Universal\ClassLoader;

use Universal\ClassLoader\Psr0ClassLoader;
use Universal\ClassLoader\Psr4ClassLoader;
use Universal\ClassLoader\ChainedClassLoader;

class ChainedClassLoaderTest extends \PHPUnit\Framework\TestCase
{
    public function testChainedClassLoader()
    {
        $psr0 = new Psr4ClassLoader;
        $psr0->addPrefix('Universal\\ClassLoader\\', 'src/ClassLoader/');

        $psr4 = new Psr4ClassLoader;
        $psr4->addPrefix('MyBar\\', 'tests/fixtures/class_loader/psr4/simple/');

        $loader = new ChainedClassLoader([ $psr0, $psr4 ]);
        $classPath = $loader->resolveClass('MyBar\\Foo');
        $this->assertEquals('tests/fixtures/class_loader/psr4/simple/Foo.php', $classPath);
        $this->assertNotNull($classPath);
        $this->assertFileExists($classPath);

        $classPath = $loader->resolveClass(Psr0ClassLoader::class);
        $this->assertEquals('src/ClassLoader/Psr0ClassLoader.php', $classPath);
        $this->assertNotNull($classPath);
        $this->assertFileExists($classPath);
    }
}

