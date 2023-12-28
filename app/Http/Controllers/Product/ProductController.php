<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Repositories\Product\ProductRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{

    public function __construct(protected ProductRepository $productRepository){}

    /**
     * Método encargado de listar todos los productos disponibles
     * @author Juan Zambrano <juandiegozb@hotmail.com>
     */
    public function index() {

        $products = $this->productRepository->getAllProductsWithStockGreaterThanZero();

        if (Auth::guard('api')->check()) {

            $userProductIds = Auth::guard('api')->user()->products()->pluck('id')->toArray();

            $userProducts = $this->productRepository->getOnlyProductsByUserLogged($products, $userProductIds);

            $products = $this->productRepository->getAllProductsExceptByUserLogged($products, $userProductIds);
        } else {
            $userProducts = [];
        }

        $totalItems = count($products) + count($userProducts);

        foreach ($userProducts as $product) {
            $product['actions'] = [
                'edit' => route('product.update', $product['id']),
                'delete' => route('product.destroy', $product['id'])
            ];
        }

        return response()->json([
            'status' => 'success',
            'total_items' => $totalItems,
            'data' => [
            'products' => $products,
            'user_products' => $userProducts,
            ]

        ], 200);
    }


    /**
     * Método que permite el almacenamiento de productos
     * SOLO está disponible si existe un usuario autenticado
     * @author Juan Zambrano <juandiegozb@hotmail.com>
     */
    public function store(Request $request) {

        if (Auth::guard('api')->check()) {

            try {
                $request->validate([
                    'category_id' => 'required|exists:categories,id',
                    'name' => 'required|string',
                    'price' => 'required|numeric|between:0,99999999.99',
                    'stock' => 'required|integer',
                    'ean_13' => 'required|unique:products|numeric',
                ]);

                $product = $this->productRepository->storeAProduct($request);

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Producto creado correctamente',
                    'data' => $product,
                ], 201);

            } catch (ValidationException $e) {
                return response()->json($e->errors(), 422);
            }


        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'No cuenta con permisos para crear un producto',
            ], 403);
        }
    }

    public function destroy($productId) {

        $product = Product::find($productId);

        if ($product) {

            $user = Auth::guard('api')->user();

            if ($user->id === $product->user_id) {

                $product->delete();

                return response()->json(['message' => 'Producto eliminado con éxito'], 200);

            } else {

                return response()->json(['message' => 'Prohibido eliminar este recurso'], 403);

            }
        } else {

            return response()->json(['message' => 'Producto no encontrado'], 404);

        }

    }
}
