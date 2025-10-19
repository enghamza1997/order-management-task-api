<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\BaseRepositry;
use App\Contracts\UserInterface;

class UserRepository extends BaseRepositry implements UserInterface
{
    /**
     * get Model Class Name
     * @var string
     */
    protected $modelName = User::class;


    public function hasRole(array $roles = []) {
        $instance = $this->getQueryBuilder();

        return $instance
            ->role($roles)->get();
    }
}
