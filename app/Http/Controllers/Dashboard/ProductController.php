<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Category;
use Intervention\Image\ImageManagerStatic as Image; 
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $categories = Category::all();
        $products = Product::when($request->search, function($q) use ($request){
            return $q->whereTranslationLike('name', '%' . $request->search . '%');
        })->when($request->category_id, function($q) use ($request){
            return $q->where('category_id', $request->category_id);
        })->latest()->paginate(5);
        return view('dashboard.products.index', compact('categories','products'));
    }// end of index

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        return view('dashboard.products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $rules = [
            'category_id' => 'required'
        ];

        foreach (config('translatable.locales') as $locale) {
            $rules += [$locale . '.name' => 'required|unique:product_translations,name'];
            $rules += [$locale . '.description' => 'required'];
        } // end of foreach

        $rules += [
            'purchase_price' => 'required',
            'sale_price' => 'required',
            'stock' => 'required',
        ];

        $request->validate($rules);


        $request_data = $request->all();
 

        if($request->image){
            $filename = time() .'.'. $request->image->getClientOriginalExtension();  
            Image::make($request->image)
            ->resize(300, null, function ($constraint){
                $constraint->aspectRatio();
            })
            
            ->save(public_path('uploads/product_images/' . $filename));

            $request_data['image'] = $filename;

        }// end of if request->image

               // dd($request_data);
        Product::create($request_data);
        session()->flash('success', __('site.added_successfully'));
        return redirect()->route('dashboard.products.index');

    } // end of store

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('dashboard.products.edit', compact('categories', 'product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $rules = [
            'category_id' => 'required'
        ];

        foreach (config('translatable.locales') as $locale) {
            $rules += [$locale . '.name' => ['required', Rule::unique('product_translations', 'name')->ignore($product->id, 'product_id')]];
            $rules += [$locale . '.description' => 'required'];
        } // end of foreach

        $rules += [
            'purchase_price' => 'required',
            'sale_price' => 'required',
            'stock' => 'required',
        ];

        $request->validate($rules);


        $request_data = $request->all();
 

        if($request->image){

            if($product->iamge != 'default.png') {
                Storage::disk('public_uploads')->delete('/product_images/' . $product->image);
            }


            $filename = time() .'.'. $request->image->getClientOriginalExtension();  
            Image::make($request->image)
            ->resize(300, null, function ($constraint){
                $constraint->aspectRatio();
            })
            
            ->save(public_path('uploads/product_images/' . $filename));

            $request_data['image'] = $filename;

        }// end of if request->image

        $product->update($request_data);
        session()->flash('success', __('site.updated_successfully'));
        return redirect()->route('dashboard.products.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        if($product->iamge != 'default.png') {
            Storage::disk('public_uploads')->delete('/product_images/' . $product->image);
        }

        $product->delete();
        session()->flash('success', __('site.deleted_successfully'));
        return redirect()->route('dashboard.products.index');

    }
}
