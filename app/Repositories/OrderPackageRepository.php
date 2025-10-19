<?php

namespace App\Repositories;

use App\Contracts\OrderPackageInterface;
use App\Models\OrderPackage;
use App\Repositories\BaseRepositry;
use Illuminate\Database\Eloquent\Builder;

class OrderPackageRepository extends BaseRepositry implements OrderPackageInterface
{
  /**
   * get Model Class Name
   * @var string
   */
  protected $modelName = OrderPackage::class;

  /**
   * Apply parameters, which can be extended in child classes for filtering.
   */
  protected function applyFilters(Builder $instance, array $filters = []): void
  {
    foreach ($filters as $key => $value) {
      $instance->where($key, $value);
    }
  }


  /**
   * @inheritDoc
   * @throws Exception
   */
  public function getQueryBuilder(): Builder
  {
    return $this->getNewInstance()->newQuery();
  }
}
