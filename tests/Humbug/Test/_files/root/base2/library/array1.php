<?php

class Array1
{
    public function arrayConcatenate($op1)
    {
        $add1 = ['foo' => 'x'] + array('bar' => 'x');

        $add2 = ['foo' => 'x'] + ['bar' => 'x'];

        $add3 = ['foo' => 'x'] +
            ['bar' => 'x'];
    }
}
