<?php

namespace TrainingBundle\Security\User;

class adUserCustom extends \Ztec\Security\ActiveDirectoryBundle\Security\User\adUser
{
    /** @var string */
    private $sid;
    
    public function setSID($value)
    {
        $this->sid = $value;
    }
}
