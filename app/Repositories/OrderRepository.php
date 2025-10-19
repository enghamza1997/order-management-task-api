<?php

namespace App\Repositories;

use App\Contracts\OrderInterface;
use App\Models\Order;
use App\Repositories\BaseRepositry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrderRepository extends BaseRepositry implements OrderInterface
{
  /**
   * get Model Class Name
   * @var string
   */
  protected $modelName = Order::class;

  /**
   * Apply parameters, which can be extended in child classes for filtering.
   */
  protected function applyFilters(Builder $instance, array $filters = []): void
  {
    foreach ($filters as $key => $value) {
      $instance->where($key, $value);
    }
  }

  public function getLastOrder(): Order|null
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
