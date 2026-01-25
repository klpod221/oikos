<?php

namespace App\Traits;

use App\Utils\QueryBuilder;

/**
 * Filterable Trait
 *
 * Provides functionality to filter, sort, and search Eloquent queries.
 *
 * @package App\Traits
 */
trait Filterable
{
    /**
     * Apply filters, sorting, and search to the query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApplyFilters($query, array $filters)
    {
        return app(QueryBuilder::class)->apply($query, $this, $filters);
    }
}
