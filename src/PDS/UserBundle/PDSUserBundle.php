<?php

namespace PDS\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PDSUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
