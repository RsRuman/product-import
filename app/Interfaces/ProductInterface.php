<?php

namespace App\Interfaces;

interface ProductInterface
{
    /**
     * Product insert interface
     * @param array $data
     * @return mixed
     */
    public function insertProduct(array $data): mixed;
}
