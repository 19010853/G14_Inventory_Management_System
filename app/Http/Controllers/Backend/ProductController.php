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
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProductController extends Controller
{
    private const PRODUCT_DIR = 'upload/producting';

    private function imageDisk(): string
    {
        // Allow switching between local/public/s3 from .env via FILESYSTEM_DISK
        return config('filesystems.default', 'public');
    }

    private function storeProductImage($uploadedFile): string
    {
        $manager = new ImageManager(new Driver());
        $name = hexdec(uniqid()).'.'.$uploadedFile->getClientOriginalExtension();

        // Resize with Intervention then store via Laravel filesystem (S3-compatible)
        $image = $manager->read($uploadedFile)->resize(150, 150);
        $path = self::PRODUCT_DIR.'/'.$name;

        $disk = $this->imageDisk();
        
        try {
            // For S3, ensure visibility is set correctly
            if ($disk === 's3') {
                Storage::disk($disk)->put($path, (string) $image->toJpeg(85), [
                    'visibility' => 'public',
                    'ContentType' => 'image/jpeg'
                ]);
            } else {
                Storage::disk($disk)->put($path, (string) $image->toJpeg(85), ['visibility' => 'public']);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to store product image: ' . $e->getMessage());
            throw $e;
        }

        return $path;
    }

    private function deleteImageIfExists(?string $path): void
    {
        if (!$path) {
            return;
        }

        Storage::disk($this->imageDisk())->delete($path);
    }

    // All Product
    public function AllProduct(){
        $allData = Product::orderBy('id','DESC')->get();
        // Pass image disk to view for proper URL generation
        $imageDisk = $this->imageDisk();
        return view('admin.backend.product.product_list',compact('allData', 'imageDisk'));
    }
    // End Method

    // Add Product
    public function AddProduct(){
        $categories = ProductCategory::all();
        $brands = Brand::all();
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        return view('admin.backend.product.add_product',compact('categories','brands','suppliers','warehouses'));
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
                $save_url = $this->storeProductImage($img);

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
        // Pass image disk to view for proper URL generation
        $imageDisk = $this->imageDisk();

        return view('admin.backend.product.edit_product',compact('categories','brands','suppliers','warehouses','editData','multiImgs','imageDisk'));
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

        if ($request->hasFile('image')){
            foreach($request->file('image') as $img){
                $product->imgs()->create([
                    'image' => $this->storeProductImage($img),
                ]);
            }
        }

        if ($request->has('remove_image')){
            foreach($request->remove_image as $removeImageId){
                $img = ProductImage::find($removeImageId);
                if ($img){
                    $this->deleteImageIfExists($img->image);
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
            $this->deleteImageIfExists($img->image);
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
        // Pass image disk to view for proper URL generation
        $imageDisk = $this->imageDisk();

        return view('admin.backend.product.details_product',compact('product', 'imageDisk'));
    }
}
