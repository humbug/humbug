<?php

namespace Humbug\Adapter\Phpunit;

class XmlConfigurationBuilder
{
    public function build($configurationDir)
    {
        $configurationFile = (new ConfigurationLocator())->locate($configurationDir);

        $dom = (new ConfigurationLoader())->load($configurationFile);

        $xmlConfiguration = new XmlConfiguration($dom);

        $xmlConfiguration->setBootstrap($this->getNewBootstrapPath());
        $xmlConfiguration->turnOffCacheTokens();

        return $xmlConfiguration;
    }

    private function getNewBootstrapPath()
    {
        return sys_get_temp_dir() . '/humbug.phpunit.bootstrap.php';
    }
}
