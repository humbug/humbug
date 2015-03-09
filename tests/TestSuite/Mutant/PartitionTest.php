<?php

/**
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */
namespace Humbug\Test\TestSuite\Mutant;

use Humbug\TestSuite\Mutant\PartitionBuilder;

class PartitionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetBatchesReturnsMutantsInPartitions()
    {
        $partition = new PartitionBuilder();

        $mutation = $this->prophesize('Humbug\Mutation')->reveal();
        $mutable = $this->prophesize('Humbug\Mutable')->reveal();

        $partition->add($mutable, 1, $mutation);

        $this->assertCount(1, $partition->getPartitions(1));

        $partition->addMutations($mutable, 1, [ $mutation ]);

        $this->assertCount(2, $partition->getPartitions(1));
    }
}
