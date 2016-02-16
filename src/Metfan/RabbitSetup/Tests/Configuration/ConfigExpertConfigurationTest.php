<?php
namespace Metfan\RabbitSetup\Tests\Configuration;

use Metfan\RabbitSetup\Configuration\ConfigExpertConfiguration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;


/**
 *
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Tests\Configuration
 */
class ConfigExpertConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function provider()
    {
        $finder = new Finder();
        $finder->files()->in(__DIR__.'/fixtures');

        $fixtures = [];
        foreach ($finder as $file) {
            $fixtures[] = [$file];
        }

        return $fixtures;
    }

    /**
     * @dataProvider provider
     * @param \SplFileInfo $file
     */
    public function test(\SplFileInfo $file)
    {
        $data = $this->parseFixture($file);
        if (null !== $data['exception']) {
            $this->setExpectedException($data['exception']);
        }

        $yaml = Yaml::parse($data['yaml']);
        $processor = new Processor();
        $this->assertEquals($data['expect'], $processor->processConfiguration(new ConfigExpertConfiguration(), $yaml));
    }

    /**
     * Read content of fisture file. split content by "--EXPECT--"
     * First part is Yaml to be used with configuration.
     * Second part can be:
     *  - fully qualified name of exception throw during configuration processing
     *  - array resulting of processing configuration
     *
     * @param \SplFileInfo $file
     * @return array
     */
    private function parseFixture(\SplFileInfo $file)
    {
        $contents = $file->getContents();
        $data = ['yaml' => null, 'expect' => null, 'exception' => null];

        $values = explode('--EXPECT--', $contents);
        $data['yaml'] = $values[0];

        $result = str_replace([' ', "\n"], '', $values[1]);
        if (false === stripos($result, 'exception')) {
            eval("\$data['expect'] = $result ;");
        } else {
            $data['exception'] = $result;
        }

        return $data;

    }
}
