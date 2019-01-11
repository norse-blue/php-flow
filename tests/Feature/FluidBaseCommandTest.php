<?php

namespace NorseBlue\Flow\Tests\Feature;

use NorseBlue\Flow\Tests\Support\IdCommand;
use NorseBlue\Flow\Tests\TestCase;

/**
 * Class FluidBaseCommandTest
 *
 * @package NorseBlue\Flow\Tests\Feature
 */
class FluidBaseCommandTest extends TestCase
{
    /** @test */
    public function commandInitializesCorrectly(): void
    {
        $cmd = new IdCommand();

        $this->assertEquals('id', $cmd->getName());
        $this->assertEquals(['user'], array_keys($cmd->getArguments()->getDefinition()));
        $this->assertFalse(isset($cmd->user));
        $this->assertEquals([
            '-a',
            '-Z|--context',
            '-g|--group',
            '-G|--groups',
            '-n|--name',
            '-r|--real',
            '-u|--user',
            '-z|--zero',
            '--help',
            '--version',
        ], array_keys($cmd->getOptions()->getDefinition()));
        $this->assertFalse(isset($cmd->_a));
        $this->assertFalse(isset($cmd->_n));
        $this->assertFalse(isset($cmd->__version));
    }

    /** @test */
    public function argumentIsCorrectlySetThroughProperty(): void
    {
        $cmd = new IdCommand();

        $cmd->user = 'axel';

        $this->assertTrue(isset($cmd->user));
        $this->assertEquals('axel', $cmd->user);
    }

    /** @test */
    public function argumentIsCorrectlySetThroughMethod(): void
    {
        $cmd = new IdCommand();

        $cmd->user('axel');

        $this->assertTrue(isset($cmd->user));
        $this->assertEquals('axel', $cmd->user);
    }

    /** @test */
    public function optionIsCorrectlySetThroughProperty(): void
    {
        $cmd = new IdCommand();

        $cmd->_a = true;
        $cmd->_n = false;
        $cmd->__version = true;

        $this->assertTrue(isset($cmd->_a));
        $this->assertTrue(isset($cmd->_n));
        $this->assertTrue(isset($cmd->__version));
        $this->assertEquals(true, $cmd->_a);
        $this->assertEquals(false, $cmd->_n);
        $this->assertEquals(true, $cmd->__version);
    }

    /** @test */
    public function optionIsCorrectlySetThroughMethod(): void
    {
        $cmd = new IdCommand();

        $cmd->_a(true);
        $cmd->_n(false);
        $cmd->__version(true);

        $this->assertTrue(isset($cmd->_a));
        $this->assertTrue(isset($cmd->_n));
        $this->assertTrue(isset($cmd->__version));
        $this->assertEquals(true, $cmd->_a);
        $this->assertEquals(false, $cmd->_n);
        $this->assertEquals(true, $cmd->__version);
    }

    /** @test */
    public function commandIsConvertedToStringCorrectly(): void
    {
        $cmd = new IdCommand();

        $cmd->user('www-data')
            ->_g(true);

        $this->assertEquals('id -g www-data', (string)$cmd);
    }
}
