<?php

namespace Modules\Finance\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\NumberingSeries;
use Modules\Finance\Repositories\Contracts\NumberingSeriesRepositoryInterface;

class NumberingSeriesService
{
    public function __construct(
        protected NumberingSeriesRepositoryInterface $repository
    ) {}

    public function getAllSeries(): Collection
    {
        return $this->repository->all();
    }

    public function getSeriesById(string $id): ?NumberingSeries
    {
        return $this->repository->findById($id);
    }

    public function createSeries(array $data): NumberingSeries
    {
        $series = new NumberingSeries($data);
        $data['sample_number'] = $series->generateSample();

        return $this->repository->create($data);
    }

    public function updateSeries(string $id, array $data): bool
    {
        $series = $this->repository->findById($id);
        if ($series) {
            $series->fill($data);
            $data['sample_number'] = $series->generateSample();

            return $this->repository->update($id, $data);
        }

        return false;
    }

    public function deleteSeries(string $id): bool
    {
        return $this->repository->delete($id);
    }

    public function generateNextNumber(string $moduleCode, string $documentType): ?string
    {
        $series = $this->repository->findByModuleAndType($moduleCode, $documentType);

        return $series ? $series->getNextNumber() : null;
    }
}
