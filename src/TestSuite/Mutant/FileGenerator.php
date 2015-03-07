<?php

/**
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */
namespace Humbug\TestSuite\Mutant;

use Humbug\Container;
use Humbug\Mutation;
use Humbug\Utility\Tokenizer;

class FileGenerator
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Generates a mutant file from a mutation
     * @param Mutation $mutation
     *
     * @return string
     */
    public function generateFile(Mutation $mutation)
    {
        $file = $this->container->getCacheDirectory() . '/humbug.mutant.' . uniqid() . '.php';

        // generate mutated file
        $mutatorClass = $mutation->getMutator();

        $originalFileContent = file_get_contents($mutation->getFile());
        $tokens = Tokenizer::getTokens($originalFileContent);
        $mutatedFileContent = $mutatorClass::mutate($tokens, $mutation->getIndex());

        file_put_contents($file, $mutatedFileContent);

        return $file;
    }
}
