<?php

namespace App\Repositories;

use App\Interfaces\ProductInterface;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductInterface
{
    /**
     * Inserting product data in products table
     * @param array $data
     * @return bool
     */
    public function insertProduct(array $data): bool
    {
        return DB::table('products')->insert($data);
    }
}
