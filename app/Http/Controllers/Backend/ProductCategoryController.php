<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    //All category
    public function AllCategory(){
        $user = auth()->user();
        if (!$user->hasPermissionTo('all.category') && !$user->hasPermissionTo('category.menu')) {
            abort(403, 'Unauthorized Action');
        }
        $category = ProductCategory::latest()->get();
        return view('admin.backend.category.all_category', compact('category'));
    }
    // End Method

    //Store Category
    public function StoreCategory(Request $request){
        if (!auth()->user()->hasPermissionTo('all.category')) {
            abort(403, 'Unauthorized Action');
        }
        ProductCategory::insert([
            'category_name' => $request->category_name,
            'category_slug' => strtolower(str_replace(' ','-',$request->category_name)),
        ]);

        $notification = array(
            'message' => 'Product Category Inserted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }
    // End Method

    public function EditCategory($id){
        if (!auth()->user()->hasPermissionTo('all.category')) {
            abort(403, 'Unauthorized Action');
        }
        $category = ProductCategory::find($id);
        return response()->json($category);
     }
      //End Method

      public function UpdateCategory(Request $request){
        if (!auth()->user()->hasPermissionTo('all.category')) {
            abort(403, 'Unauthorized Action');
        }
        $cat_id = $request->cat_id;

        ProductCategory::find($cat_id)->update([
            'category_name' => $request->category_name,
            'category_slug' => strtolower(str_replace(' ','-',$request->category_name)),
        ]);

        $notification = array(
            'message' => 'ProductCategory Updated Successfully',
            'alert-type' => 'success'
         );
         return redirect()->back()->with($notification);

    }
     //End Method

    public function DeleteCategory($id){
        if (!auth()->user()->hasPermissionTo('all.category')) {
            abort(403, 'Unauthorized Action');
        }
        ProductCategory::find($id)->delete();
        $notification = array(
            'message' => 'ProductCategory Delete Successfully',
            'alert-type' => 'success'
         );
         return redirect()->back()->with($notification);

    }
    //End Method
}
