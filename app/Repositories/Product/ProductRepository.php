<?php

namespace App\Repositories\Product;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductRepository {

    public function getAllProductsWithStockGreaterThanZero () {
        return Product::where('stock', '>', 0)->get();
    }

    public function getOnlyProductsByUserLogged($products, $idsProductsAvailable) {
        return $products->whereIn('id', $idsProductsAvailable);
    }

    public function getAllProductsExceptByUserLogged($products, $idsProductsAvailable) {
        return $products->whereNotIn('id', $idsProductsAvailable);
    }

    public function storeAProduct($data) {

        $product = [

            'user_id' => Auth::guard('api')->id(),
            'category_id' => $data->input('category_id'),
            'name' => $data->input('name'),
            'price' => $data->input('price'),
            'stock' => $data->input('stock'),
            'ean_13' => $data->input('ean_13'),

        ];

        return Product::create($product);

    }

}
