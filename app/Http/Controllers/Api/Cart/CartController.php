<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use Cart;
use Exception;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /** @throws Exception */
    public function index(Request $request): string
    {
        Cart::session($request->user()->id);

        $cart = Cart::getContent();

        if ($cart->isEmpty()) {
            return '';
        }

        $cart->put('quantity', Cart::getTotalQuantity())
            ->put('total', Cart::getTotal());

        return $cart->toJson();
    }

    /** @throws Exception */
    public function store(Request $request): string
    {
        $data = $request->validate([
            'id' => 'required|numeric',
            'name' => 'required|max:128',
            'price' => 'required|numeric',
            'quantity' => 'required|numeric',
        ]);
        Cart::session($request->user()->id);
        Cart::add($data);

        return Cart::getContent()
            ->put('quantity', Cart::getTotalQuantity())
            ->put('total', Cart::getTotal())
            ->toJson();
    }

    /** @throws Exception */
    public function update(Request $request, int $item): string
    {
        $data = $request->validate([
            'id' => 'nullable|numeric',
            'name' => 'nullable|max:128',
            'price' => 'nullable|numeric',
            'quantity' => 'nullable|numeric',
        ]);

        Cart::session($request->user()->id);
        Cart::update($item, $data);

        return Cart::getContent()
            ->put('quantity', Cart::getTotalQuantity())
            ->put('total', Cart::getTotal())
            ->toJson();
    }

    /** @throws Exception */
    public function destroy(Request $request, int $item): string
    {
        Cart::session($request->user()->id);
        Cart::remove($item);

        $cart = Cart::getContent();

        if ($cart->isEmpty()) {
            return '';
        }

        $cart->put('quantity', Cart::getTotalQuantity())
            ->put('total', Cart::getTotal());

        return $cart->toJson();
    }
}
