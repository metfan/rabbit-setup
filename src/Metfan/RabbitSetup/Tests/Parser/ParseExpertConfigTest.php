<?php
namespace Metfan\RabbitSetup\Tests\Parser;

use Metfan\RabbitSetup\Parser\ParseExpertConfig;

/**
 * Unit test of Metfan\RabbitSetup\Parser\ParseExpertConfig
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Tests\Parser
 */
class ParseExpertConfigTest extends \PHPUnit_Framework_TestCase
{
    public function provider()
    {

        return [
            ['test', '\InvalidArgumentException'],
            ['Parser/fixture', '\InvalidArgumentException'],
            ['/fixture', '\InvalidArgumentException'],
            ['Parser/fixture/config.yml']
        ];
    }

    /**
     * @dataProvider provider
     * @param $filePath
     * @param null $exception
     */
    public function test($filePath, $exception = null)
    {
        if (!defined('ROOT_PATH'))
            define('ROOT_PATH', realpath(__DIR__.'/../'));

        $parser = new ParseExpertConfig();

        if (null !== $exception) {
            $this->setExpectedException($exception);
        }

        $this->assertInternalType('array', $parser->parse($filePath));
    }
}
