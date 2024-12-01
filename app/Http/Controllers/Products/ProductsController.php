<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product\Product;
use Auth;
use App\Models\Product\Cart;
use Redirect;
use Session;
use App\Models\Product\Order;


class ProductsController extends Controller
{


    public function singleProduct($id){

        $product = Product::find($id);


        $relatedProducts = Product::where('type', $product->type)
        ->where('id', '!=', $id)->take('4')
        ->orderBy('id', 'desc')
        ->get();


        //checking for products in cart

        $checkingInCart = Cart::where('pro_id', $id)
        ->where('user_id', Auth::user()->id)
        ->count();

        return view('products.productsingle', compact('product', 'relatedProducts', 'checkingInCart'));
    }


    public function addCart(Request $request, $id){

        $addCart = Cart::create([
            "pro_id" => $request->pro_id,
            "name" => $request->name,
            "image" => $request->image,
            "price" => $request->price,
            "user_id" => Auth::user()->id,
        ]);

        return Redirect::route('product.single', $id)->with(['success' => "product added to cart successfully"] );
    }

    public function cart() {

        $cartProducts = Cart::where('user_id', Auth::user()->id)
        ->orderBy('id', 'desc')
        ->get();


        $totalPrice = cart::where('user_id', Auth::user()->id)
        ->sum('price');

        return view('products.cart', compact('cartProducts', 'totalPrice'));
    }

    public function deleteProductCart($id) {

        $deleteProductCart = Cart::where('pro_id', $id)
        ->where('user_id', Auth::user()->id);

        $deleteProductCart->delete();

        if($deleteProductCart){
            return Redirect::route('cart')->with(['delete' => "product delete from cart successfully"] );
        }
    }

    public function prepareCheckout(Request $request) {

        $value = $request->price;


        $price = Session::put('price', $value);

        $newPrice = Session::get($price);

        if($newPrice > 0){
            return Redirect::route('checkout');
        }
    }

    public function checkout() {



            return view('products.checkout');


    }

    public function storeCheckout(Request $request){

        $checkout = Order::create($request->all());

        if($checkout){
            return Redirect::route('products.pay');
        }
    }

    public function payWithPaypal(){



        return view('products.pay');
    }

    public function success(){

        $deleteItems = Cart::where('user_id', Auth::user()->id);
        $deleteItems->delete();
        if($deleteItems) {

            return view('products.success');
            
        }


    }

}
