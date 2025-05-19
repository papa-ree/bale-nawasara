<?php

namespace Paparee\BaleNawasara\App\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

trait PaginationCollection
{
    public function paginate(Collection $items, int $perPage = 10, ?int $page = null)
    {
        $page = $page ?: LengthAwarePaginator::resolveCurrentPage();
        $itemsForCurrentPage = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            items: $itemsForCurrentPage,
            total: $items->count(),
            perPage: $perPage,
            currentPage: $page,
            options: [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }
}
