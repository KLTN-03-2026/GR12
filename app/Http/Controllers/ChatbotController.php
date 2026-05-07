<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\Auth;

class ChatbotController extends Controller
{
    public function handle(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'history' => 'nullable|array',
        ]);

        $userMessage = $request->input('message');
        $history = $request->input('history', []);
        $apiKeyEnv = env('GEMINI_API_KEY');

        if (!$apiKeyEnv) {
            return response()->json([
                'success' => false,
                'message' => 'Gemini API Key is missing. Please add GEMINI_API_KEY to your .env file.'
            ], 500);
        }

        // Hỗ trợ nhiều key cách nhau bởi dấu phẩy
        $apiKeys = array_filter(array_map('trim', explode(',', $apiKeyEnv)));
        if (empty($apiKeys)) {
            return response()->json(['success' => false, 'message' => 'Invalid GEMINI_API_KEY format.'], 500);
        }
        
        // Chọn ngẫu nhiên 1 key để bắt đầu
        $apiKey = $apiKeys[array_rand($apiKeys)];

        try {
            // 1. SYSTEM INSTRUCTION
            $systemInstruction = "Bạn là FoodXpress AI, trợ lý ảo thông minh của ứng dụng đặt đồ ăn FoodXpress tại Đà Nẵng.\n";
            $systemInstruction .= "Nhiệm vụ của bạn là tư vấn món ăn, theo dõi đơn hàng và hỗ trợ khách hàng.\n";
            $systemInstruction .= "LƯU Ý QUAN TRỌNG: Bạn KHÔNG ĐƯỢC TỰ BỊA RA DỮ LIỆU. Bạn CÓ CÁC CÔNG CỤ (TOOLS) ĐỂ TÌM KIẾM MÓN ĂN, KIỂM TRA ĐƠN HÀNG. HÃY GỌI CÁC CÔNG CỤ NÀY KHI CẦN THIẾT TRƯỚC KHI TRẢ LỜI KHÁCH HÀNG.\n\n";
            
            $user = Auth::user();
            if ($user) {
                $systemInstruction .= "Khách hàng đang chat với bạn là: " . $user->name . ".\n";
            } else {
                $systemInstruction .= "Lưu ý: Khách hàng hiện tại chưa đăng nhập (Guest). Nếu họ muốn kiểm tra đơn hàng, thêm món vào giỏ, hãy thông báo rằng họ cần Đăng nhập trước.\n";
            }
            $systemInstruction .= "\nFoodXpress thường có mã giảm giá như FREESHIP, GIAM20K. Phí ship cơ bản là 15.000đ.";

            // 2. CHUẨN BỊ LỊCH SỬ CHAT
            $contents = [];
            foreach ($history as $msg) {
                $role = $msg['role'] === 'ai' ? 'model' : 'user';
                $contents[] = [
                    'role' => $role,
                    'parts' => [['text' => $msg['content']]]
                ];
            }
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $userMessage]]
            ];

            // 3. ĐỊNH NGHĨA TOOLS (Function Calling)
            $tools = [
                [
                    'functionDeclarations' => [
                        [
                            'name' => 'search_products',
                            'description' => 'Tìm kiếm món ăn trên hệ thống bằng từ khóa. Gọi hàm này khi khách hàng muốn tìm món, gợi ý món.',
                            'parameters' => [
                                'type' => 'OBJECT',
                                'properties' => [
                                    'keyword' => [
                                        'type' => 'STRING',
                                        'description' => 'Từ khóa món ăn cần tìm (VD: bún bò, trà sữa, gà rán)'
                                    ]
                                ],
                                'required' => ['keyword']
                            ]
                        ],
                        [
                            'name' => 'search_restaurants',
                            'description' => 'Tìm kiếm quán ăn trên hệ thống bằng từ khóa.',
                            'parameters' => [
                                'type' => 'OBJECT',
                                'properties' => [
                                    'keyword' => [
                                        'type' => 'STRING',
                                        'description' => 'Từ khóa tên quán (VD: Highlands, KFC)'
                                    ]
                                ],
                                'required' => ['keyword']
                            ]
                        ],
                        [
                            'name' => 'check_order_status',
                            'description' => 'Kiểm tra trạng thái các đơn hàng đang giao của khách hàng hiện tại. Không cần truyền tham số vì hệ thống tự nhận diện khách hàng.',
                            'parameters' => [
                                'type' => 'OBJECT',
                                'properties' => [
                                    'dummy' => [
                                        'type' => 'STRING',
                                        'description' => 'Tham số rỗng'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => 'add_to_cart',
                            'description' => 'Thêm một món ăn vào giỏ hàng của khách hàng.',
                            'parameters' => [
                                'type' => 'OBJECT',
                                'properties' => [
                                    'product_id' => [
                                        'type' => 'INTEGER',
                                        'description' => 'ID của món ăn cần thêm vào giỏ. Bắt buộc phải có ID thực tế của món ăn từ kết quả tìm kiếm.'
                                    ],
                                    'quantity' => [
                                        'type' => 'INTEGER',
                                        'description' => 'Số lượng món ăn. Mặc định là 1 nếu khách không nói.'
                                    ]
                                ],
                                'required' => ['product_id', 'quantity']
                            ]
                        ],
                        [
                            'name' => 'clear_cart',
                            'description' => 'Xóa toàn bộ giỏ hàng của khách hàng. Thường dùng khi thêm món thất bại do giỏ hàng đang có món của quán khác và khách yêu cầu xóa.',
                            'parameters' => [
                                'type' => 'OBJECT',
                                'properties' => [
                                    'dummy' => [
                                        'type' => 'STRING',
                                        'description' => 'Tham số rỗng'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            // 4. VÒNG LẶP GỌI GEMINI API (Multi-turn Agent Loop)
            $maxTurns = 5;
            $turn = 0;
            $cartUpdated = false;
            $replyText = "";

            while ($turn < $maxTurns) {
                $payload = [
                    'systemInstruction' => ['parts' => [['text' => $systemInstruction]]],
                    'contents' => $contents,
                    'tools' => $tools,
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 800,
                    ]
                ];

                $response = Http::withoutVerifying()->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey, 
                    $payload
                );

                if (!$response->successful()) {
                    $status = $response->status();
                    
                    // Cơ chế tự động đổi Key nếu bị quá tải (429)
                    if ($status === 429 && count($apiKeys) > 1) {
                        // Xóa key hiện tại ra khỏi danh sách vì nó đã bị giới hạn
                        $apiKeys = array_values(array_diff($apiKeys, [$apiKey]));
                        if (count($apiKeys) > 0) {
                            $apiKey = $apiKeys[array_rand($apiKeys)];
                            \Log::warning("Gemini 429 Error. Tự động chuyển sang API Key dự phòng...");
                            continue; // Thử lại ngay lập tức với key mới
                        }
                    }

                    \Log::error('Gemini API Error: ' . $response->body());
                    $message = 'Lỗi kết nối tới AI: ' . $response->json('error.message', 'Unknown error');
                    
                    if ($status === 429) {
                        $message = 'Tất cả đường truyền AI đều đang quá tải do có quá nhiều người dùng. Vui lòng thử lại sau 30 giây nhé!';
                    }
                    
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], $status);
                }

                $data = $response->json();
                $parts = $data['candidates'][0]['content']['parts'] ?? [];
                
                $functionCall = null;
                $textPart = "";

                foreach ($parts as $part) {
                    if (isset($part['functionCall'])) {
                        $functionCall = $part['functionCall'];
                    } elseif (isset($part['text'])) {
                        $textPart .= $part['text'];
                    }
                }

                // Nếu có function call, xử lý ở Backend
                if ($functionCall) {
                    $functionName = $functionCall['name'];
                    $args = $functionCall['args'] ?? [];
                    
                    \Log::info("AI Agent calls function: $functionName", $args);
                    
                    // Thực thi hàm nội bộ
                    $functionResult = $this->executeLocalFunction($functionName, $args, $user, $cartUpdated);

                    // Thêm Function Call vào lịch sử để Gemini biết nó đã gọi gì
                    $contents[] = [
                        'role' => 'model',
                        'parts' => [['functionCall' => $functionCall]]
                    ];

                    // Thêm Function Response vào lịch sử để trả kết quả cho Gemini
                    $contents[] = [
                        'role' => 'function',
                        'parts' => [
                            [
                                'functionResponse' => [
                                    'name' => $functionName,
                                    'response' => [
                                        'name' => $functionName,
                                        'content' => $functionResult
                                    ]
                                ]
                            ]
                        ]
                    ];
                    
                    $turn++;
                    continue; // Gọi lại Gemini API với dữ liệu mới
                }

                // Nếu không có function call, đó là tin nhắn cuối cùng để trả cho user
                $replyText = $textPart;
                break;
            }

            if (empty($replyText)) {
                $replyText = "Xin lỗi, tôi đã gặp khó khăn khi xử lý yêu cầu của bạn. Bạn vui lòng thử lại nhé.";
            }

            return response()->json([
                'success' => true,
                'reply' => $replyText,
                'cart_updated' => $cartUpdated
            ]);

        } catch (Exception $e) {
            \Log::error('Chatbot Exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Nơi tập trung logic thực thi các kỹ năng của AI Agent
     */
    private function executeLocalFunction($name, $args, $user, &$cartUpdated)
    {
        switch ($name) {
            case 'search_products':
                $keyword = $args['keyword'] ?? '';
                $query = Product::where('status', 'approved')
                    ->where('is_available', true)
                    ->with('user'); // Lấy kèm thông tin nhà hàng

                if (!empty($keyword)) {
                    $terms = array_filter(explode(' ', $keyword));
                    
                    $query->where(function ($q) use ($terms) {
                        foreach ($terms as $term) {
                            $term = trim($term);
                            if (empty($term)) continue;

                            $termVariations = [$term];
                            $lowerTerm = mb_strtolower($term, 'UTF-8');
                            // Xử lý biến thể mì/mỳ
                            if (str_contains($lowerTerm, 'mì')) {
                                $termVariations[] = str_replace('mì', 'mỳ', $lowerTerm);
                            } elseif (str_contains($lowerTerm, 'mỳ')) {
                                $termVariations[] = str_replace('mỳ', 'mì', $lowerTerm);
                            }

                            $q->where(function ($subQ) use ($termVariations) {
                                foreach ($termVariations as $var) {
                                    $subQ->orWhere('name', 'like', "%{$var}%")
                                         ->orWhere('description', 'like', "%{$var}%");
                                }
                            });
                        }
                    });
                }

                $products = $query->limit(10)->get();
                
                $results = [];
                foreach ($products as $p) {
                    $results[] = [
                        'id' => $p->id,
                        'name' => $p->name,
                        'price' => $p->price,
                        'restaurant_name' => $p->user->restaurant_name ?? $p->user->name,
                        'description' => $p->description
                    ];
                }
                
                if (empty($results)) {
                    return ['status' => 'not_found', 'message' => "Không tìm thấy món ăn nào với từ khóa '$keyword'."];
                }
                return ['status' => 'success', 'data' => $results];

            case 'search_restaurants':
                $keyword = $args['keyword'] ?? '';
                $query = User::where('role', 'restaurant')->where('status', 'active');

                if (!empty($keyword)) {
                    $terms = array_filter(explode(' ', $keyword));
                    
                    $query->where(function ($q) use ($terms) {
                        foreach ($terms as $term) {
                            $term = trim($term);
                            if (empty($term)) continue;

                            $termVariations = [$term];
                            $lowerTerm = mb_strtolower($term, 'UTF-8');
                            if (str_contains($lowerTerm, 'mì')) {
                                $termVariations[] = str_replace('mì', 'mỳ', $lowerTerm);
                            } elseif (str_contains($lowerTerm, 'mỳ')) {
                                $termVariations[] = str_replace('mỳ', 'mì', $lowerTerm);
                            }

                            $q->where(function ($subQ) use ($termVariations) {
                                foreach ($termVariations as $var) {
                                    $subQ->orWhere('name', 'like', "%{$var}%")
                                         ->orWhere('restaurant_name', 'like', "%{$var}%")
                                         ->orWhere('address', 'like', "%{$var}%");
                                }
                            });
                        }
                    });
                }

                $restaurants = $query->limit(5)->get();
                
                $results = [];
                foreach ($restaurants as $r) {
                    $results[] = [
                        'id' => $r->id,
                        'name' => $r->restaurant_name ?? $r->name,
                        'address' => $r->address
                    ];
                }
                
                if (empty($results)) {
                    return ['status' => 'not_found', 'message' => "Không tìm thấy quán nào."];
                }
                return ['status' => 'success', 'data' => $results];

            case 'check_order_status':
                if (!$user) return ['status' => 'error', 'message' => 'Yêu cầu khách hàng đăng nhập trước.'];
                
                $activeOrders = Order::where('user_id', $user->id)
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->with(['shipper.user'])
                    ->latest()
                    ->get();
                    
                if ($activeOrders->isEmpty()) {
                    return ['status' => 'empty', 'message' => 'Khách hàng không có đơn hàng nào đang giao.'];
                }
                
                $results = [];
                foreach ($activeOrders as $order) {
                    $results[] = [
                        'order_code' => $order->order_code,
                        'status' => $order->status, // pending, assigned, picked_up, shipping, delivering
                        'total' => $order->total,
                        'shipper_name' => $order->shipper ? $order->shipper->user->name : 'Chưa có shipper'
                    ];
                }
                return ['status' => 'success', 'data' => $results];

            case 'add_to_cart':
                if (!$user) return ['status' => 'error', 'message' => 'Yêu cầu đăng nhập.'];
                
                $productId = $args['product_id'] ?? 0;
                $qty = $args['quantity'] ?? 1;
                
                $product = Product::with('user')->find($productId);
                if (!$product) return ['status' => 'error', 'message' => 'Món ăn không tồn tại hoặc ID sai.'];
                
                $restaurantId = $product->user_id;
                $existingCartItems = CartItem::where('user_id', $user->id)->with('product')->get();
                
                if ($existingCartItems->isNotEmpty()) {
                    $currentRestaurantId = $existingCartItems->first()->product->user_id;
                    if ($currentRestaurantId !== $restaurantId) {
                        return [
                            'status' => 'conflict', 
                            'message' => 'Giỏ hàng đang có món của quán khác. Hãy hỏi khách có muốn xóa giỏ hàng cũ để đặt quán mới không.'
                        ];
                    }
                }

                $existingItem = CartItem::where('user_id', $user->id)
                    ->where('product_id', $productId)
                    ->first();
                    
                if ($existingItem) {
                    $existingItem->increment('quantity', $qty);
                } else {
                    CartItem::create([
                        'user_id' => $user->id,
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'options' => []
                    ]);
                }
                $cartUpdated = true;
                return ['status' => 'success', 'message' => "Đã thêm $qty {$product->name} vào giỏ hàng."];

            case 'clear_cart':
                if (!$user) return ['status' => 'error', 'message' => 'Yêu cầu đăng nhập.'];
                CartItem::where('user_id', $user->id)->delete();
                $cartUpdated = true;
                return ['status' => 'success', 'message' => 'Đã xóa toàn bộ giỏ hàng thành công.'];

            default:
                return ['status' => 'error', 'message' => 'Công cụ không được hỗ trợ.'];
        }
    }
}
