<?php
namespace Metfan\RabbitSetup\Parser;

use Metfan\RabbitSetup\Configuration\ConfigExpertConfiguration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;


/**
 * Read Yaml config file and use Expert configuration to parse it
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Manager\Command
 */
class ParseExpertConfig
{
    /**
     * @param $configFile
     * @return array
     */
    public function parse($configFile)
    {
        if ('/' !== substr($configFile, 0, 1)) {
            $configFile = ROOT_PATH.'/'.$configFile;
        }

        if (!is_file($configFile) || !is_readable($configFile)) {
            throw new \InvalidArgumentException(sprintf('Le fichier de config est inutilisable: %s', $configFile));
        }

        $configs = Yaml::parse(file_get_contents($configFile));

        $processor = new Processor();
        return $processor->processConfiguration(new ConfigExpertConfiguration(), $configs);
    }
}
