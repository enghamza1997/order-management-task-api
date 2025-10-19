<?php

namespace  App\Contracts;

interface UserInterface extends BaseInterface
{

    public function hasRole(array $roles = []);
}
