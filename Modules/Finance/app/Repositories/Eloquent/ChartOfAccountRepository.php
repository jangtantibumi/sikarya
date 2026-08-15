<?php

declare(strict_types=1);

namespace Modules\Finance\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Repositories\Contracts\ChartOfAccountRepositoryInterface;

class ChartOfAccountRepository implements ChartOfAccountRepositoryInterface
{
    public function all(): Collection
    {
        return ChartOfAccount::with(['accountGroup', 'parent', 'currency'])->orderBy('code', 'asc')->get();
    }

    public function findById(string $id): ?ChartOfAccount
    {
        return ChartOfAccount::with(['accountGroup', 'parent', 'children', 'currency'])->find($id);
    }

    public function create(array $data): ChartOfAccount
    {
        return ChartOfAccount::create($data);
    }

    public function update(string $id, array $data): bool
    {
        $account = $this->findById($id);

        return $account ? $account->update($data) : false;
    }

    public function delete(string $id): bool
    {
        $account = $this->findById($id);

        return $account ? (bool) $account->delete() : false;
    }
}
