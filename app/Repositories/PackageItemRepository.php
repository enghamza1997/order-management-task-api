<?php

namespace App\Repositories;

use App\Contracts\PackageItemInterface;
use App\Models\PackageItem;
use App\Repositories\BaseRepositry;
use Illuminate\Database\Eloquent\Builder;

class PackageItemRepository extends BaseRepositry implements PackageItemInterface
{
  /**
   * get Model Class Name
   * @var string
   */
  protected $modelName = PackageItem::class;

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
