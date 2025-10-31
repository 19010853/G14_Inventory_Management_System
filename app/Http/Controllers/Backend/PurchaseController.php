<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    //All Purchase Methods
    public function AllPurchase(){
        $allData = Purchase::orderBy('id', 'desc')->get();
        return view('admin.backend.purchase.all_purchase',compact('allData'));
    }
}
