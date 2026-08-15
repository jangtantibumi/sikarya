<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\Inventory\Brand;
use App\Models\Inventory\Category;
use App\Models\Inventory\Uom;

class MasterDataService
{
    public function getAllCategories()
    {
        return Category::with('children')->get();
    }

    public function createCategory(array $data)
    {
        return Category::create($data);
    }

    public function updateCategory(Category $category, array $data)
    {
        $category->update($data);

        return $category;
    }

    public function getAllBrands()
    {
        return Brand::all();
    }

    public function createBrand(array $data)
    {
        return Brand::create($data);
    }

    public function getAllUoms()
    {
        return Uom::all();
    }

    public function createUom(array $data)
    {
        return Uom::create($data);
    }
}
