<?php

class Some_Class_With_If_Clause_In_Method
{
    protected function _getSession()
    {
        static $session = null;
        if ($session === null) {
            $session = new Zend_Session_Namespace(
                $this->getSessionNamespace(), true
            );
        }
    }
}
