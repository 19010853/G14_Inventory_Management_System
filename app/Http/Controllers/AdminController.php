<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function AdminLogout(Request $request){
        Auth::guard('web')->logout();

        $request->session()->invalidate(); 
        $request->session()->regenerateToken();

        return redirect('/login');
    }
    // End Method 

    public function AdminProfile(){
        $id = Auth::user()->id;
        $profileData = User::find($id);
        return view('admin.admin_profile',compact('profileData'));

    }
     // End Method 

    public function ProfileStore(Request $request){
        $id = Auth::user()->id;
        $data = User::find($id);

        // Validate input to allow ONLY image files for profile photo
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|max:255',
            'phone'  => 'nullable|string|max:255',
            'address'=> 'nullable|string|max:1000',
            'photo'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data->name = $validated['name'];
        $data->email = $validated['email'];
        $data->phone = $validated['phone'] ?? null;
        $data->address = $validated['address'] ?? null;

        // Keep track of existing photo file name in DB, not from request
        $oldPhotoPath = $data->photo;

        if ($request->hasFile('photo')){
            $file = $request->file('photo');
            $filename = time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('upload/user_images'),$filename);
            $data->photo = $filename;

            if ($oldPhotoPath && $oldPhotoPath !== $filename) {
                $this->deleteOldPhoto($oldPhotoPath);
            }
        }
        $data->save();

        $notification = array(
            'message' => 'Admin Profile Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    // End Method

    public function AdminPasswordUpdate(Request $request){
       $user = Auth::user();
       $request->validate([
        'old_password' => 'required',
        'new_password' => 'required|confirmed',
       ]);

       if (!Hash::check(($request->old_password), $user->password)){
            $notification = array(
                'message' => 'Old Password Does Not Match!',
                'alert-type' => 'error'
            );
        return back()->with($notification);
       }

       User::whereId($user->id)->update([
        'password' => Hash::make($request->new_password)
       ]);

       Auth::logout();

       $notification = array(
        'message' => 'Password Updated Successfully',
        'alert-type' => 'success'
       );

       return redirect()->route('login')->with($notification);
    } 

    // End Method

    private function deleteOldPhoto($oldPhotoPath) {
        $photoPath = public_path('upload/user_images/'.$oldPhotoPath);
        if (file_exists($photoPath)){
            unlink($photoPath);
        }
    }
}