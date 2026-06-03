<?php
// app/Traits/HasScopes.php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasScopes
{
    /**
     * Scope para limitar columnas devueltas por la consulta.
     * Usa un allowSelect por modelo como lista blanca.
     */
    public function scopeSelectFields(Builder $query)
    {
        $allowed = property_exists($this, 'allowSelect') && is_array($this->allowSelect)
            ? $this->allowSelect
            : [];

        if (empty($allowed)) {
            return $query;
        }

        $requested = request('fields');

        if (!empty($requested)) {
            $requestedFields = array_values(array_filter(array_map('trim', explode(',', $requested))));
            $filtered = array_values(array_intersect($requestedFields, $allowed));

            if (!empty($filtered)) {
                return $query->select($filtered);
            }

            return $query;
        }

        return $query->select($allowed);
    }

    /**
     * Scope para incluir relaciones
     */
    public function scopeIncluded(Builder $query)
    {
        if (empty($this->allowIncluded) || empty(request('include'))) {  // ← Cambiado
            return;
        }

        $relations = explode(',', request('include'));  // ← Cambiado
        $allowIncluded = collect($this->allowIncluded);

        foreach ($relations as $key => $relationship) {
            if (!$allowIncluded->contains($relationship)) {
                unset($relations[$key]);
            }
        }

        $query->with($relations);
    }
    /**
     * Scope para filtrar por campos
     */
    public function scopeFilter(Builder $query)
    {
        if (empty($this->allowFilter) || empty(request('filter'))) {
            return;
        }

        $filters = request('filter');
        $allowFilter = collect($this->allowFilter);

        foreach ($filters as $filter => $value) {
            if ($allowFilter->contains($filter)) {
                $query->where($filter, 'LIKE', '%' . $value . '%');
            }
        }
    }

    /**
     * Scope para ordenar
     */
    public function scopeSort(Builder $query)
    {
        if (empty($this->allowSort) || empty(request('sort'))) {
            return;
        }

        $sortFields = explode(',', request('sort'));
        $allowSort = collect($this->allowSort);

        foreach ($sortFields as $sortField) {
            $direction = 'asc';

            if (substr($sortField, 0, 1) == '-') {
                $direction = 'desc';
                $sortField = substr($sortField, 1);
            }

            if ($allowSort->contains($sortField)) {
                $query->orderBy($sortField, $direction);
            }
        }
    }

    /**
     * Scope para paginar u obtener todos
     */
    public function scopeGetOrPaginate(Builder $query)
    {
        if (request('perPage')) {
            $perPage = intval(request('perPage'));
            if ($perPage) {
                return $query->paginate($perPage);
            }
        }
        return $query->get();
    }
}
