<?php
/**
 * EvaThumber
 * URL based image transformation php library
 *
 * @link      https://github.com/AlloVince/EvaThumber
 * @copyright Copyright (c) 2012-2013 AlloVince (http://avnpc.com/)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @author    AlloVince
 */

namespace EvaEngineTest;

use Eva\EvaEngine\Engine;

class EvaEngine extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function testApplication()
    {
        $engine = new Engine();
        $this->assertTrue($engine->getApplication() instanceof \Phalcon\Mvc\Application);
    }

    public function testPath()
    {
        $engine = new Engine('foo');
        $this->assertEquals('foo', $engine->getAppRoot());
        $this->assertEquals('foo/config', $engine->getConfigPath());
        $this->assertEquals('foo/modules', $engine->getModulesPath());

        $engine->setConfigPath('bar');
        $engine->setModulesPath('bar');
        $this->assertEquals('bar', $engine->getConfigPath());
        $this->assertEquals('bar', $engine->getModulesPath());

    }
}
