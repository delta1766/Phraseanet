<?php

/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2013 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Tests\Phrasea\Utilities\Compiler;

use Alchemy\Phrasea\Utilities\Less\Compiler;
use Alchemy\BinaryDriver\Exception\ExecutionFailureException;

class CompilerTest extends \PhraseanetPHPUnitAbstract
{
    public function testCompileSuccess()
    {
        $recessDriver = $this->getMock('Alchemy\BinaryDriver\BinaryInterface');
        $recessDriver->expects($this->once())->method('command');

        $filesystem = $this->getMock('Symfony\Component\Filesystem\Filesystem');
        $filesystem->expects($this->once())->method('mkdir');
        $filesystem->expects($this->once())->method('dumpFile');

        $compiler = new Compiler($filesystem, $recessDriver);

        $compiler->compile(__DIR__ . '/output.css', __FILE__);
    }

    public function testCreate()
    {
        $compiler = Compiler::create(self::$DI['app']);
        $this->assertInstanceOf('Alchemy\Phrasea\Utilities\Less\Compiler', $compiler);
    }

   /**
     * @expectedException Alchemy\Phrasea\Exception\RuntimeException
     */
    public function testCompileFileNotExists()
    {
        $recessDriver = $this->getMock('Alchemy\BinaryDriver\BinaryInterface');

        $filesystem = $this->getMock('Symfony\Component\Filesystem\Filesystem');
        $filesystem->expects($this->once())->method('mkdir');

        $compiler = new Compiler($filesystem, $recessDriver);

        $compiler->compile(__DIR__ . '/output.css', 'not_existsing_file');
    }

   /**
     * @expectedException Alchemy\Phrasea\Exception\RuntimeException
     */
    public function testCompileExecutionFailure()
    {
        $recessDriver = $this->getMock('Alchemy\BinaryDriver\BinaryInterface');
        $recessDriver->expects($this->once())->method('command')->will(
            $this->throwException(new ExecutionFailureException())
        );

        $filesystem = $this->getMock('Symfony\Component\Filesystem\Filesystem');

        $compiler = new Compiler($filesystem, $recessDriver);

        $compiler->compile(__DIR__ . '/output.css', __FILE__);
    }
}