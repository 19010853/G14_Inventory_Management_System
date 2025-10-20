<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProductController extends Controller
{
    // All Product
    public function AllProduct(){
        $allData = Product::orderBy('id','DESC')->get();
        return view('backend.product.all_product',compact('allData'));
    }
    // End Method

    // Add Product
    public function AddProduct(){
        $categories = ProductCategory::all();
        $brands = Brand::all();
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        return view('backend.product.add_product',compact('categories','brands','suppliers','warehouses'));
    }
    // End Method

    // Store Product
    public function StoreProduct(Request $request){
        $product = Product::create([
            'name' => $request->name,
            'code' => $request->code,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'warehouse_id' => $request->warehouse_id,
            'supplier_id' => $request->supplier_id,
            'price' => $request->price,
            'stock_alert' => $request->stock_alert,
            'note' => $request->note,
            'product_qty' => $request->product_qty,
            'status' => $request->status,
            'created_at' => now(),
        ]);

        $product_id = $product->id;

        // Multiple Image Upload
        if ($request->hasFile('image')){
            foreach($request->file('image') as $img){
                $manager = new ImageManager(new Driver());
                $name_gen = hexdec(uniqid()).'.'.$img->getClientOriginalExtension();
                $imgs = $manager->read($img);
                $imgs->resize(150, 150)->save(public_path('upload/producting/'.$name_gen));
                $save_url = 'upload/producting/'.$name_gen;

                ProductImage::create([
                    'product_id' => $product_id,
                    'image' => $save_url,
                ]);
            }
        }

        $notification = array(
            'message' => 'Product Inserted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.product')->with($notification);
    }
    // End Method

    // Edit Product
    public function EditProduct($id){
        $editData = Product::find($id);
        $categories = ProductCategory::all();
        $brands = Brand::all();
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        $multiImgs = ProductImage::where('product_id',$id)->get();

        return view('admin.backend.product.edit_product',compact('categories','brands','suppliers','warehouses','editData','multiImgs'));
    }
    // End Method

    // Update Product
    public function UpdateProduct(Request $request){
        $product_id = $request->id;

        $product = Product::find($product_id);

        $product->name = $request->name;
        $product->code = $request->code;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->warehouse_id = $request->warehouse_id;
        $product->price = $request->price;
        $product->stock_alert = $request->stock_alert;
        $product->note = $request->note;
        $product->supplier_id = $request->supplier_id;
        $product->product_qty = $request->product_qty;
        $product->status = $request->status;
        $product->save();

        if ($request->hasFile('iamge')){
            foreach($request->file('image') as $img){
                $manager = new ImageManager(new Driver());
                $name_gen = hexdec(uniqid()).'.'.$img->getClientOriginalExtension();
                $imgs = $manager->read($img);
                $imgs->resize(150, 150)->save(public_path('upload/producting/'.$name_gen));

                $product->imgs()->create([
                    'image' => 'upload/producting/'.$name_gen,
                ]);
            }
        }

        if ($request->has('remove_image')){
            foreach($request->remove_image as $removeImageId){
                $img = ProductImage::find($removeImageId);
                if ($img){
                    if (file_exists(public_path($img->image))){
                        unlink(public_path($img->image));
                    }
                    $img->delete();
                }
            }
        }

        $notification = array(
            'message' => 'Product Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.product')->with($notification);
    }
    // End Method

    // Delete Product
    public function DeleteProduct($id){
        $product = Product::findOrFail($id);

        // Delete associated images
        $images = ProductImage::where('product_id', $id)->get();
        foreach ($images as $img) {
            $imagePath = public_path($img->image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Delete image records from database
        ProductImage::where('product_id', $id)->delete();

        // Delete product record
        $product->delete();

        $notification = array(
            'message' => 'Product Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }
    // End Method

    // Product Details
    public function ProductDetails($id){
        $product = Product::findOrFail($id);

        return view('admin.backend.product.details_product',compact('product'));
    }
}
