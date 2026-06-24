<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;

abstract class BaseRepository implements BaseRepositoryInterface
{
    public function __construct(
        protected readonly Model $model,
    ) {}

    public function findById(int|string $id): ?Model
    {
        return $this->model->find($id);
    }

    public function findByIdOrFail(int|string $id): Model
    {
        return $this->model->findOrFail($id);
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 15): CursorPaginator
    {
        return $this->model->cursorPaginate($perPage);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int|string $id, array $data): Model
    {
        $record = $this->findByIdOrFail($id);
        $record->update($data);

        return $record->fresh();
    }

    public function delete(int|string $id): bool
    {
        $record = $this->findByIdOrFail($id);

        return (bool) $record->delete();
    }

    public function exists(int|string $id): bool
    {
        return $this->model->where('id', $id)->exists();
    }
}
