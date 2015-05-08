<?php

namespace TrainingBundle\Security\User;

class adUserCustom extends \Ztec\Security\ActiveDirectoryBundle\Security\User\adUser
{
    /** @var string */
    private $guid;
    
    public function setGUID($value)
    {
        $this->guid = $value;
    }
}
