<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image; 
use Illuminate\Validation\Rule;


class UserController extends Controller
{

    public function __construct()
    {
        // create read update delete
        $this->middleware(['permission:read_users'])->only('index');
        $this->middleware(['permission:create_users'])->only('create');
        $this->middleware(['permission:update_users'])->only('edit');
        $this->middleware(['permission:delete_users'])->only('destroy');
    } // end of construct

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // if($request->search){
        //     $users = User::whereRoleIs('admin')->where('name', 'like', '%' . $request->search . '%')->get();
        // }else{
        //      // $users = User::all(); // Get All users
        //     $users = User::whereRoleIs('admin')->get(); // Get all admin without suber admin
        // }

        $users = User::whereRoleIs('admin')->where(function ($q) use ($request) {

            return $q->when($request->search, function ($query) use ($request) {

                return $query->where('first_name', 'like', '%' . $request->search . '%')
                    ->orWhere('last_name', 'like', '%' . $request->search . '%');

            });

        })->latest()->paginate(5);

        return view('dashboard.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('dashboard.users.create');
    } // end of create

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'image' => 'image',
            'password' => 'required|confirmed',
            'permissions' => 'required|min:1',
        ]);

        $request_data = $request->except(['password', 'password_confirmation', 'permissions', 'image']);
        $request_data['password'] = bcrypt($request->password);

        if($request->image){
            $filename = time() .'.'. $request->image->getClientOriginalExtension();  
            Image::make($request->image)
            ->resize(300, null, function ($constraint){
                $constraint->aspectRatio();
            })
            
            // ->save(public_path('uploads/user_images/' . $request->image->hasName()));
            ->save(public_path('uploads/user_images/' . $filename));

            $request_data['image'] = $filename;

        }// end of if request->image

        // dd($request->all());
        $user = User::create($request_data);
        $user->attachRole('admin');
        $user->syncPermissions($request->permissions);

        session()->flash('success', __('site.added_successfully'));

        return redirect()->route('dashboard.users.index');
    } //end of store

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    // public function show(User $user)
    // {
    //     //
    // }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        return view('dashboard.users.edit', compact('user'));
    } // end of edit

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
         $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => ['required', Rule::unique('users')->ignore($user->id),],
            'image' => 'image',
            'permissions' => 'required|min:1',
        ]);

        $request_data = $request->except(['permissions', 'image']);

        if($request->image){
            if($user->image != 'default.png'){
                Storage::disk('public_uploads')->delete('user_images/'. $user->image); 
            }
            $filename = time() .'.'. $request->image->getClientOriginalExtension();  
            Image::make($request->image)
            ->resize(300, null, function ($constraint){
                $constraint->aspectRatio();
            })
            
            // ->save(public_path('uploads/user_images/' . $request->image->hasName()));
            ->save(public_path('uploads/user_images/' . $filename));

            $request_data['image'] = $filename;

        }// end of if request->image

        $user->update($request_data);
        $user->syncPermissions($request->permissions);

        session()->flash('success', __('site.updated_successfully'));

        return redirect()->route('dashboard.users.index');
    } // end of update

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        if($user->image != 'default.png'){
           Storage::disk('public_uploads')->delete('user_images/'. $user->image); 
        }// End of if
        $user->delete();
        session()->flash('success', __('site.deleted_successfully'));
        return redirect()->route('dashboard.users.index');

    }
}
