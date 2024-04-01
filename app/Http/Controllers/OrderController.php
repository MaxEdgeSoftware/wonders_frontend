<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user() ? auth()->user() : User::where("id", 36)->first();
        $orders = Order::with(["items", "address"])->where("user_id", $user->id)->get();
        return okResponse("Order fetched", OrderResource::collection($orders));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->all();
        $validator = Validator::make($validated, [
            'products' => 'required|array',
            'house_number' => 'required|string',
            'street_address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
        ]);


        if ($validator->fails()) {
            $erro = json_decode($validator->errors(), true);
            $msg = array_values($erro)[0];

            return errorResponse($msg[0], $erro);
        }
        $productIDs = [];
        foreach ($validated["products"] as $product) {
            if (!isset($product["product_id"]) || !isset($product["qty"])) return errorResponse("Invalid data passed");
            $p = $product["product_id"];
            $q = $product["qty"];
            $pro = Product::where("id", $p)->where("status", "active")->first();
            if (!$pro) return errorResponse("Invalid data passed");
            array_push($productIDs, $p);
        }
        $user = auth()->user() ? auth()->user() : User::where("id", 36)->first();
        $order = new Order;
        $order->user_id = $user->id;
        $order->order_id = $this->generateOrderID();
        $amount = Product::whereIn("id", $productIDs)->sum("price");
        $order->amount = $amount;
        $order->save();

        OrderAddress::create([
            "order_id" => $order->id,
            "house_number" => $request->house_number,
            "city" => $request->city,
            "country" => $request->country,
            "state" => $request->state,
            "street_address" => $request->street_address
        ]);
        foreach ($validated["products"] as $product) {
            OrderItem::create([
                "order_id" => $order->id,
                "user_id" => $user->id,
                "product_id" => $product["product_id"],
                "qty" => $product["qty"],
            ]);
        }
        // notify admin
        // notify user
        try {
            $data = [
                'title' => 'NEW ORDER RECEIVED FROM '.$user->email,
                'to' => env("NOTIFY_EMAIL2"),
                'full_name' => 'Admin',
                'body' => '
                <p>We received a an order of '.count($validated["products"]).' items(s) from '.$user->name.'</p>
               ',
            ];
            $view = view("emails.template", ["data" => $data])->render();
    
            sendMail($data["to"], $data["title"], $view);
    
            $data = [
                'title' => 'NEW ORDER RECEIVED FROM '.$user->email,
                'to' => env("NOTIFY_EMAIL2"),
                'full_name' => 'Admin',
                'body' => '
                <p>We received a an order of '.count($validated["products"]).' items(s) from '.$user->name.'</p>
               ',
            ];
            $view = view("emails.template", ["data" => $data])->render();
    
            sendMail($data["to"], $data["title"], $view);
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
       return okResponse("Order placed");
    }
    public function generateOrderID()
    {
        $u = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWZY"), 0, 10);
        if (Order::where("order_id", $u)->first()) return $this->generateOrderID();
        return $u;
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
