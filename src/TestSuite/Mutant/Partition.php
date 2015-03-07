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

use Humbug\Mutable;
use Humbug\Mutation;

class Partition
{
    private $mutations = [];

    /**
     * @param Mutable $mutable
     * @param int $index
     * @param Mutation $mutation
     */
    public function add(Mutable $mutable, $index, Mutation $mutation)
    {
        $this->mutations[] = [ $mutable, $index, $mutation ];
    }

    /**
     * @param Mutable $mutable
     * @param int $index
     * @param Mutation[] $mutations
     */
    public function addMutations(Mutable $mutable, $index, array $mutations)
    {
        foreach ($mutations as $mutation) {
            $this->add($mutable, $index, $mutation);
        }
    }

    public function getBatches($partitionSize)
    {
        $batches = [];

        foreach ($this->mutations as $data) {
            list (, , $mutation) = $data;

            $batches[] = [ $mutation ];
        }

        return $batches;
    }
}