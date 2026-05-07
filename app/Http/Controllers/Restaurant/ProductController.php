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
            'available_from' => 'required|date_format:H:i,H:i:s',
            'available_to' => 'required|date_format:H:i,H:i:s|after:available_from',
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
                    'stock_quantity', 'is_available', 'available_from', 'available_to'
                ]);
                $data['user_id'] = auth()->id();
                $data['slug'] = Str::slug($request->name) . '-' . time();
                $data['status'] = 'pending';

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
    // ... các phần trên giữ nguyên ...

    public function destroy(Product $product)
    {
        if ($product->user_id !== auth()->id()) abort(403);

        try {
            // Logic: Ẩn món ăn thay vì xóa để bảo vệ toàn vẹn dữ liệu đơn hàng
            $product->update(['is_available' => false]);
            return back()->with('success', 'Đã tạm ngưng món ăn "' . $product->name . '" thành công! 🛑');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
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
            'available_from' => 'nullable|date_format:H:i,H:i:s',
            'available_to' => 'nullable|date_format:H:i,H:i:s',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|max:2048',
            'delete_gallery' => 'nullable|array',
            'options' => 'nullable|array',
        ]);

        try {
            DB::transaction(function () use ($request, $product) {
                $data = $request->only(['name', 'category_id', 'price', 'description', 'stock_quantity', 'is_available']);
                
                if ($request->filled('available_from')) $data['available_from'] = $request->available_from;
                if ($request->filled('available_to')) $data['available_to'] = $request->available_to;

                // Xử lý ảnh chính
                if ($request->hasFile('image')) {
                    if ($product->image) Storage::disk('public')->delete($product->image);
                    $data['image'] = $request->file('image')->store('products', 'public');
                }

                $product->update($data);

                // Xóa ảnh gallery được chỉ định trong View Edit
                if ($request->has('delete_gallery')) {
                    foreach ($request->delete_gallery as $imageId) {
                        $image = $product->gallery()->find($imageId);
                        if ($image) {
                            Storage::disk('public')->delete($image->image_path);
                            $image->delete();
                        }
                    }
                }

                // Cập nhật Toppings (Xóa cũ tạo mới nhưng có kiểm tra ảnh)
                if ($request->has('options')) {
                    // Trước khi xóa bản ghi, nên xóa file ảnh topping cũ nếu cần (tùy logic quản lý ảnh của bạn)
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
                }

                // Thêm ảnh mới vào Gallery
                if ($request->hasFile('gallery')) {
                    foreach ($request->file('gallery') as $image) {
                        $path = $image->store('products/gallery', 'public');
                        $product->gallery()->create(['image_path' => $path]);
                    }
                }
            });

            return redirect()->route('restaurant.products.index')
                ->with('success', 'Đã cập nhật món ăn "' . $request->name . '" thành công! ✨');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    // 5. Trang hiển thị form chỉnh sửa món ăn
    public function edit($id)
    {
        // Lấy thông tin món ăn kèm theo các quan hệ (category, options, gallery)
        $product = Product::with(['category', 'options', 'gallery'])->findOrFail($id);

        // Kiểm tra quyền sở hữu (tránh việc nhà hàng này sửa món của nhà hàng khác)
        if ($product->user_id !== auth()->id()) {
            abort(403, 'Bạn không có quyền chỉnh sửa món ăn này.');
        }

        return Inertia::render('Restaurant/Products/Edit', [
            'product' => $product,
            'categories' => Category::where('is_active', true)->get()
        ]);
    }

    public function show($id)
    {
        $product = Product::with(['category', 'options', 'gallery'])->findOrFail($id);

        return response()->json($product);
    }
}
