<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Humbug\Command\Humbug;

class Application extends BaseApplication
{

    private static $logo = '
 _  _            _              
| || |_  _ _ __ | |__ _  _ __ _ 
| __ | || | \'  \| \'_ \ || / _` |
|_||_|\_,_|_|_|_|_.__/\_,_\__, |
                          |___/ ';

    const NAME = 'Humbug';

    const VERSION = '1.0-dev';

    public function __construct()
    {
        parent::__construct(self::$logo.PHP_EOL.self::NAME, self::VERSION);
    }

    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        $inputDefinition->setArguments();
        return $inputDefinition;
    }

    protected function getCommandName(InputInterface $input)
    {
        return 'humbug';
    }

    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();
        $defaultCommands[] = new Humbug();
        return $defaultCommands;
    }

}
