<?php

declare(strict_types=1);

namespace App\Interfaces\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;

interface BaseRepositoryInterface
{
    public function findById(int|string $id): ?Model;

    public function findByIdOrFail(int|string $id): Model;

    public function all(): Collection;

    public function paginate(int $perPage = 15): CursorPaginator;

    public function create(array $data): Model;

    public function update(int|string $id, array $data): Model;

    public function delete(int|string $id): bool;

    public function exists(int|string $id): bool;
}
