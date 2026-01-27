<?php

namespace App\Utils;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class QueryBuilder
{
    /**
     * Apply filters, sorting, and search to a query
     *
     * @param Builder $query
     * @param Model $model
     * @param array $filters
     * @return Builder
     */
    public function apply(Builder $query, Model $model, array $filters): Builder
    {
        // Apply filterable fields
        $this->applyFilters($query, $model, $filters);

        // Apply search
        $this->applySearch($query, $model, $filters);

        // Apply sorting
        $this->applySorting($query, $model, $filters);

        return $query;
    }

    /**
     * Apply filterable fields
     */
    protected function applyFilters(Builder $query, Model $model, array $filters): void
    {
        $filterable = $model->filterable ?? [];

        foreach ($filterable as $key => $config) {
            // Handle simple filterable: ['type', 'wallet_id']
            if (is_numeric($key)) {
                $field = $config;
                $operator = '=';
            } else {
                // Handle custom filterable: ['start_date' => 'transaction_date:>=']
                $field = $key;
                [$column, $operator] = $this->parseConfig($config);
                $field = $key;
            }

            // Check if filter value exists
            if (!isset($filters[$field]) || $filters[$field] === '' || $filters[$field] === null) {
                continue;
            }

            $value = $filters[$field];

            // Apply filter
            if (is_numeric($key)) {
                // Simple: where('type', '=', $value)
                $query->where($field, $operator, $value);
            } else {
                // Custom: where('transaction_date', '>=', $value)
                $query->where($column, $operator, $value);
            }
        }
    }

    /**
     * Apply search across searchable fields
     */
    protected function applySearch(Builder $query, Model $model, array $filters): void
    {
        $searchable = $model->searchable ?? [];
        $searchTerm = $filters['search'] ?? null;

        if (empty($searchable) || !$searchTerm) {
            return;
        }

        $query->where(function ($q) use ($searchable, $searchTerm) {
            foreach ($searchable as $field) {
                // Check if it's a relationship field (e.g., 'category.name')
                if (str_contains($field, '.')) {
                    [$relation, $column] = explode('.', $field, 2);
                    $q->orWhereHas($relation, function ($subQuery) use ($column, $searchTerm) {
                        $subQuery->where($column, 'ilike', "%{$searchTerm}%");
                    });
                } else {
                    // Direct field search
                    $q->orWhere($field, 'ilike', "%{$searchTerm}%");
                }
            }
        });
    }

    /**
     * Apply sorting
     */
    protected function applySorting(Builder $query, Model $model, array $filters): void
    {
        $sortable = $model->sortable ?? [];
        $sortBy = $filters['sort_by'] ?? null;
        $sortOrder = $filters['sort_order'] ?? 'desc';

        // Validate sort order
        $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';

        // If sort_by is provided and valid, apply it
        if ($sortBy && in_array($sortBy, $sortable)) {
            $query->orderBy($sortBy, $sortOrder);
        } elseif (!empty($sortable)) {
            // Apply default sort (first sortable field)
            $query->orderBy($sortable[0], 'desc');
        }
    }

    /**
     * Parse filter configuration string
     * Example: 'transaction_date:>=' returns ['transaction_date', '>=']
     */
    protected function parseConfig(string $config): array
    {
        if (str_contains($config, ':')) {
            return explode(':', $config, 2);
        }

        return [$config, '='];
    }
}
