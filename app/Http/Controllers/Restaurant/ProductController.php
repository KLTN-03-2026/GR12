<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // 1. Trang danh sách món ăn
    public function index()
    {
        $products = Product::where('user_id', auth()->id())
            ->with(['category', 'gallery']) // Lấy luôn cả gallery để hiển thị nếu cần
            ->latest()
            ->get();

        return Inertia::render('Restaurant/Products/Index', [
            'products' => $products
        ]);
    }

    // 2. Trang form thêm món mới (Lỗi của bạn nằm ở đây - thiếu hàm này)
    public function create()
    {
        return Inertia::render('Restaurant/Products/Create', [
            'categories' => Category::where('is_active', true)->get()
        ]);
    }

    // 3. Hàm lưu dữ liệu (Bản nâng cấp nhiều ảnh & ảnh topping)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'stock_quantity' => 'required|integer|min:0',
            'is_available' => 'boolean',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|max:2048',
            'options' => 'nullable|array',
            'options.*.option_name' => 'required|string',
            'options.*.option_value' => 'required|string',
            'options.*.additional_price' => 'required|numeric|min:0',
            'options.*.image' => 'nullable|image|max:1024',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $data = $request->only([
                    'name', 'category_id', 'price', 'description',
                    'stock_quantity', 'is_available'
                ]);
                $data['user_id'] = auth()->id();
                $data['slug'] = Str::slug($request->name) . '-' . time();

                if ($request->hasFile('image')) {
                    $data['image'] = $request->file('image')->store('products', 'public');
                }

                $product = Product::create($data);

                // Lưu Gallery
                if ($request->hasFile('gallery')) {
                    foreach ($request->file('gallery') as $image) {
                        $path = $image->store('products/gallery', 'public');
                        $product->gallery()->create(['image_path' => $path]);
                    }
                }

                // Lưu Toppings
                if ($request->has('options')) {
                    foreach ($request->options as $index => $optionData) {
                        $optionImage = null;
                        if ($request->hasFile("options.$index.image")) {
                            $optionImage = $request->file("options.$index.image")->store('options', 'public');
                        }

                        $product->options()->create([
                            'option_name' => $optionData['option_name'],
                            'option_value' => $optionData['option_value'],
                            'additional_price' => $optionData['additional_price'],
                            'image' => $optionImage,
                        ]);
                    }
                }
            });

            return redirect()->route('restaurant.products.index')
    ->with('success', 'Món ăn siêu cấp "' . $request->name . '" đã lên kệ thành công! 🚀');

        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    // 4. Xóa món ăn
    public function destroy(Product $product)
    {
        if ($product->user_id !== auth()->id()) abort(403);
        
        if ($product->image) Storage::disk('public')->delete($product->image);
        
        // Xóa thêm ảnh trong gallery nếu có
        foreach($product->gallery as $img) {
            Storage::disk('public')->delete($img->image_path);
        }

        $product->delete();
        return back()->with('success', 'Đã xóa món ăn và toàn bộ hình ảnh.');
    }

    public function edit(Product $product)
    {
        if ($product->user_id !== auth()->id()) abort(403);

        return Inertia::render('Restaurant/Products/Edit', [
            'product' => $product->load(['category', 'options', 'gallery']),
            'categories' => Category::where('is_active', true)->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        if ($product->user_id !== auth()->id()) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'stock_quantity' => 'required|integer|min:0',
            'is_available' => 'boolean',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|max:2048',
            'options' => 'nullable|array',
            'options.*.option_name' => 'required|string',
            'options.*.option_value' => 'required|string',
            'options.*.additional_price' => 'required|numeric|min:0',
            'options.*.image' => 'nullable|image|max:1024',
        ]);

        try {
            DB::transaction(function () use ($request, $product) {
                $data = $request->only([
                    'name', 'category_id', 'price', 'description',
                    'stock_quantity', 'is_available'
                ]);

                if ($request->hasFile('image')) {
                    if ($product->image) {
                        Storage::disk('public')->delete($product->image);
                    }
                    $data['image'] = $request->file('image')->store('products', 'public');
                }

                $product->update($data);

                if ($request->has('options')) {
                    $product->options()->delete();
                    foreach ($request->options as $index => $optionData) {
                        $optionImage = $optionData['existing_image'] ?? null;
                        if ($request->hasFile("options.$index.image")) {
                            $optionImage = $request->file("options.$index.image")->store('options', 'public');
                        }

                        $product->options()->create([
                            'option_name' => $optionData['option_name'],
                            'option_value' => $optionData['option_value'],
                            'additional_price' => $optionData['additional_price'],
                            'image' => $optionImage,
                        ]);
                    }
                } else {
                    $product->options()->delete();
                }

                if ($request->hasFile('gallery')) {
                    foreach ($request->file('gallery') as $image) {
                        $path = $image->store('products/gallery', 'public');
                        $product->gallery()->create(['image_path' => $path]);
                    }
                }
            });

            return redirect()->route('restaurant.products.index')
                ->with('success', 'Món ăn đã được cập nhật thành công.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $product = Product::with(['category', 'options', 'gallery'])->findOrFail($id);

        return response()->json($product);
    }
}
