<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class BrandController extends Controller
{
    private const BRAND_DIR = 'upload/brand';

    private function imageDisk(): string
    {
        // Allow switching between local/public/s3 from .env via FILESYSTEM_DISK
        return config('filesystems.default', 'public');
    }

    private function storeBrandImage($uploadedFile): string
    {
        $manager = new ImageManager(new Driver());
        $name = hexdec(uniqid()).'.'.$uploadedFile->getClientOriginalExtension();

        // Resize with Intervention then store via Laravel filesystem (S3-compatible)
        $image = $manager->read($uploadedFile)->resize(100, 90);
        $path = self::BRAND_DIR.'/'.$name;

        Storage::disk($this->imageDisk())
            ->put($path, (string) $image->toJpeg(85), ['visibility' => 'public']);

        return $path;
    }

    private function deleteImageIfExists(?string $path): void
    {
        if (!$path) {
            return;
        }

        Storage::disk($this->imageDisk())->delete($path);
    }

    /**
     * Get the public URL for a brand image
     * Works with both local storage and S3
     */
    private function getImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $disk = $this->imageDisk();
        
        // For S3, use temporaryUrl or url method
        if ($disk === 's3') {
            // Check if file exists before generating URL
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->url($path);
            }
            return null;
        }
        
        // For local/public disks, use url helper
        return Storage::disk($disk)->url($path);
    }

    //All Brand
    public function AllBrand(){
        if (!auth()->user()->hasPermissionTo('all.brand')) {
            abort(403, 'Unauthorized Action');
        }
        $brand = Brand::latest()->get();
        // Pass image disk to view for proper URL generation
        $imageDisk = $this->imageDisk();
        return view('admin.backend.brand.all_brand',compact('brand', 'imageDisk'));
    }
    //End Method

    //Add Brand
    public function AddBrand(){
        return view('admin.backend.brand.add_brand');
    }
    //End Method

    //Store Brand
    public function StoreBrand(Request $request){
        $data = [
            'name' => $request->name,
        ];

        if ($request->file('image')){
            $data['image'] = $this->storeBrandImage($request->file('image'));
        }

        Brand::create($data);

        $notification = array(
            'message' => 'Brand Inserted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.brand')->with($notification);
    }
    //End Method

    //Edit Brand
    public function EditBrand($id){
        if (!auth()->user()->hasPermissionTo('edit.brand')) {
            abort(403, 'Unauthorized Action');
        }
        $brand = Brand::find($id);
        return view('admin.backend.brand.edit_brand',compact('brand'));
    }
    //End Method

    //Update Brand
    public function UpdateBrand(Request $request){
        $brand_id = $request->id;
        $brand = Brand::find($brand_id);

        if ($request->file('image')){
            $save_url = $this->storeBrandImage($request->file('image'));
            $this->deleteImageIfExists($brand?->image);

            Brand::find($brand_id)->update([
                'name' => $request->name,
                'image' => $save_url,
            ]);

            $notification = array(
                'message' => 'Brand Updated with Image Successfully',
                'alert-type' => 'success'
            );

            return redirect()->route('all.brand')->with($notification);
        } else {
            Brand::find($brand_id)->update([
                'name' => $request->name,
            ]);

            $notification = array(
                'message' => 'Brand Updated without Image Successfully',
                'alert-type' => 'success'
            );

            return redirect()->route('all.brand')->with($notification);
        }
    }
    //End Method

    //Delete Brand
    public function DeleteBrand($id){
        $item = Brand::find($id);
        $this->deleteImageIfExists($item?->image);

        Brand::find($id)->delete();

        $notification = array(
            'message' => 'Brand Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
    //End Method
}
