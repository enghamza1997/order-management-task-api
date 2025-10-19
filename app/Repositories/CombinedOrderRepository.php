<?php

namespace App\Repositories;

use App\Contracts\CombinedOrderInterface;
use App\Models\CombinedOrder;
use App\Repositories\BaseRepositry;
use Illuminate\Database\Eloquent\Builder;

class CombinedOrderRepository extends BaseRepositry implements CombinedOrderInterface
{
  /**
   * get Model Class Name
   * @var string
   */
  protected $modelName = CombinedOrder::class;

  /**
   * Apply parameters, which can be extended in child classes for filtering.
   */
  protected function applyFilters(Builder $instance, array $filters = []): void
  {
    foreach ($filters as $key => $value) {
      $instance->where($key, $value);
    }
  }

  public function getLastOrder(): CombinedOrder|null
  {
    $instance = $this->getQueryBuilder();
    return $instance->latest('created_at')->first() ?? null;
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
