<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Repositories\Product\ProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{

    public function __construct(protected ProductRepository $productRepository){}

    /**
     * Método encargado de listar todos los productos disponibles
     * @author Juan Zambrano <juandiegozb@hotmail.com>
     */
    public function index(): JsonResponse
    {

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

        foreach ($products as &$product) {

            $product['canPurchase'] = $product['stock'] > 0 && $product['status'];

            if ($product['canPurchase']) {
                $product['addToCart'] = [
                    'url' => route('product.addToCart', $product['id'])
                ];
            }
        }

        $myCartUrl = Auth::guard('api')->check() ? route('product.viewCart') : '';

        return response()->json([
            'status' => 'success',
            'total_items' => $totalItems,
            'data' => [
                'products' => $products,
                'user_products' => $userProducts,
                'my_cart_url' => $myCartUrl,
            ]

        ], 200);
    }

    /**
     * Método encargado de actualizar un producto existente
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     * @author Juan Zambrano <juandiegozb@hotmail.com>
     */
    public function update(Request $request, int $productId): JsonResponse {

        $user = Auth::guard('api')->user();

        if ($user) {

            $product = Product::find($productId);

            if (!$product) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Producto no encontrado.',
                ], 404);
            }

            if ($product->user_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tiene permisos para editar este producto.',
                ], 403);
            }

            $request->validate([
                'category_id' => 'required|exists:categories,id',
                'name' => 'required|string',
                'price' => 'required|numeric',
                'stock' => 'required|integer',
                'ean_13' => 'required|unique:products,ean_13,' . $productId . '|numeric',
            ]);

            $product->category_id = $request->input('category_id');
            $product->name = $request->input('name');
            $product->price = $request->input('price');
            $product->stock = $request->input('stock');
            $product->ean_13 = $request->input('ean_13');

            $product->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Producto actualizado correctamente.',
                'product' => $product,
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'No tiene permisos para actualizar productos.',
            ], 403);
        }
    }


    /**
     * Método que permite el almacenamiento de productos
     * SOLO está disponible si existe un usuario autenticado
     * @author Juan Zambrano <juandiegozb@hotmail.com>
     */
    public function store(Request $request): JsonResponse
    {

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

    /**
     *  Método encargado de agregar un producto al carrito, solo está disponible en productos que no son del usuario autenticado
     * @param Request $request
     * @param int $productId
     * @return JsonResponse
     * @author Juan Zambrano <juandiegozb@hotmail.com>
     */
    public function addProductToCart(Request $request, int $productId): JsonResponse
    {

        if (Auth::guard('api')->check()) {

            $product = Product::find($productId);

            if (!$product) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Producto no encontrado.',
                ], 404);
            }

            $existingCartItem = Cart::where('user_id', Auth::guard('api')->id())
                ->where('product_id', $product->id)
                ->first();

            if ($existingCartItem) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El producto ya está en el carrito.',
                ], 400);
            }

            $cartItem = new Cart([
                'user_id' => Auth::guard('api')->id(),
                'product_id' => $product->id,
                'quantity' => $request->input('quantity', 1),
            ]);

            $cartItem->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Producto agregado al carrito correctamente.',
                'cart_item' => $cartItem,
            ], 201);

        } else {

            return response()->json([
                'status' => 'error',
                'message' => 'No cuenta con permisos para agregar productos al carrito.',
            ], 403);
        }
    }

    public function viewCart(): JsonResponse
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            $cartItems = Cart::where('user_id', $user->id)->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'cart_items' => $cartItems,
                    'total' => $this->calculateTotalWithTaxes($cartItems),
                ],
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado.',
            ], 401);
        }
    }


    /**
     *  Método encargado de eliminar un recurso, si y solo si el usuario autenticado es el propietario del mismo.
     * @param int $productId
     * @return JsonResponse
     * @author Juan Zambrano <juandiegozb@hotmail.com>
     */
    public function destroy(int $productId): JsonResponse
    {

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

    private function calculateTotalWithTaxes($cartItems): float
    {
        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        $taxRate = 0.1;

        $taxes = $subtotal * $taxRate;
        $total = $subtotal + $taxes;

        return $total;
    }
}
