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
        $context = $request->input('context', '');
        $user = Auth::user();

        // 0. TỐI ƯU TỐC ĐỘ VÀ CHI PHÍ (FAST-REPLY)
        // Bắt các câu hỏi gợi ý để trả lời ngay lập tức (0ms) mà không tốn tiền gọi AI
        // Chỉ áp dụng ở lượt chat đầu tiên (history <= 1) để không làm đứt mạch trò chuyện.
        if (count($history) <= 1) {
            $fastReply = $this->getFastReply($userMessage, $user);
            if ($fastReply) {
                return response()->json($fastReply);
            }
        }

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
            $systemInstruction .= "LƯU Ý QUAN TRỌNG: Bạn KHÔNG ĐƯỢC TỰ BỊA RA DỮ LIỆU. Bạn CÓ CÁC CÔNG CỤ (TOOLS) ĐỂ TÌM KIẾM MÓN ĂN, KIỂM TRA ĐƠN HÀNG, TẶNG VOUCHER. HÃY GỌI CÁC CÔNG CỤ NÀY KHI CẦN THIẾT TRƯỚC KHI TRẢ LỜI KHÁCH HÀNG.\n\n";
            $systemInstruction .= "QUY TẮC CHĂM SÓC KHÁCH HÀNG: Nếu khách hàng tức giận, phàn nàn dịch vụ (đồ ăn tệ, giao hàng chậm, vv...), BẮT BUỘC PHẢI gọi công cụ 'issue_apology_voucher' để tạo mã giảm giá 20.000đ đền bù cho họ, sau đó báo cho họ biết mã giảm giá này kèm lời xin lỗi.\n\n";
            
            if ($user) {
                $systemInstruction .= "Khách hàng đang chat với bạn là: " . $user->name . ".\n";
            } else {
                $systemInstruction .= "Lưu ý: Khách hàng hiện tại chưa đăng nhập (Guest). Nếu họ muốn kiểm tra đơn hàng, thêm món vào giỏ, hãy thông báo rằng họ cần Đăng nhập trước.\n";
            }
            $systemInstruction .= "\n[QUAN TRỌNG NHẤT]: Khi bạn gọi công cụ 'search_products' và nhận được danh sách món ăn, BẮT BUỘC PHẢI hiển thị hình ảnh món ăn ĐẦU TIÊN bằng thẻ markdown. BẮT BUỘC PHẢI hiển thị địa chỉ của quán. Phải trình bày chính xác theo định dạng sau cho mỗi món ăn:\n\n![Tên món ăn](image_url)\n**Tên món ăn**\n*Topping/Options: ...*\n*Giá: ...*\n*Quán: [Tên quán] - [Địa chỉ quán]*\n*Phí ship dự kiến: ...*\n\nKhông được bỏ sót hình ảnh (image_url) hay địa chỉ quán (restaurant_address) mà công cụ trả về.\n";
            $baseShippingFee = \App\Models\Setting::getValue('base_shipping_fee', 15000);
            $baseRadiusKm = \App\Models\Setting::getValue('base_radius_km', 2);
            $extraShippingFee = \App\Models\Setting::getValue('extra_shipping_fee_per_km', 3000);
            
            $systemInstruction .= "Khách hàng rất hay hỏi về khuyến mãi. Hãy tự động sử dụng công cụ get_active_vouchers để tra cứu danh sách mã giảm giá thực tế đang có hiệu lực trên hệ thống và tư vấn cho khách. Phí ship cơ bản là " . number_format($baseShippingFee, 0, ',', '.') . "đ cho {$baseRadiusKm}km đầu tiên, từ km tiếp theo cộng thêm " . number_format($extraShippingFee, 0, ',', '.') . "đ/km. Hãy sử dụng công cụ calculate_shipping_fee để tính khoảng cách và phí ship chính xác nếu khách hỏi về phí giao hàng và hệ thống chưa tính được. Nếu khách không biết ăn gì, hãy gọi công cụ analyze_order_history để đưa ra các món gợi ý phù hợp với khẩu vị của khách.";

            // Xử lý ngữ cảnh (Context Awareness)
            if (!empty($context)) {
                if (str_contains($context, 'restaurant')) {
                    $systemInstruction .= "\n[NGỮ CẢNH HIỆN TẠI] Khách hàng đang xem trang chi tiết quán ăn. Nếu họ hỏi tìm món, hãy tự động ưu tiên hiểu là họ muốn tìm món trong quán ăn này.\n";
                } elseif (str_contains($context, 'cart') || str_contains($context, 'checkout')) {
                    $systemInstruction .= "\n[NGỮ CẢNH HIỆN TẠI] Khách hàng đang ở trang Giỏ Hàng/Thanh Toán. Họ có thể muốn tính phí ship, hỏi mã giảm giá hoặc xóa món.\n";
                } elseif (str_contains($context, 'my-orders')) {
                    $systemInstruction .= "\n[NGỮ CẢNH HIỆN TẠI] Khách hàng đang ở trang Lịch sử Đơn hàng. Hãy sẵn sàng kiểm tra trạng thái đơn hàng (check_order_status) nếu họ cần hỗ trợ.\n";
                }
            }

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
                        ],
                        [
                            'name' => 'remove_from_cart',
                            'description' => 'Xóa một món ăn khỏi giỏ hàng của khách dựa vào tên.',
                            'parameters' => [
                                'type' => 'OBJECT',
                                'properties' => [
                                    'product_name' => [
                                        'type' => 'STRING',
                                        'description' => 'Tên món ăn muốn xóa'
                                    ]
                                ],
                                'required' => ['product_name']
                            ]
                        ],
                        [
                            'name' => 'update_cart_quantity',
                            'description' => 'Cập nhật lại số lượng của một món ăn trong giỏ hàng dựa vào tên.',
                            'parameters' => [
                                'type' => 'OBJECT',
                                'properties' => [
                                    'product_name' => [
                                        'type' => 'STRING',
                                        'description' => 'Tên món ăn'
                                    ],
                                    'quantity' => [
                                        'type' => 'INTEGER',
                                        'description' => 'Số lượng mới (lớn hơn 0)'
                                    ]
                                ],
                                'required' => ['product_name', 'quantity']
                            ]
                        ],
                        [
                            'name' => 'calculate_shipping_fee',
                            'description' => 'Tính toán khoảng cách và phí giao hàng từ quán ăn đến địa chỉ khách hàng.',
                            'parameters' => [
                                'type' => 'OBJECT',
                                'properties' => [
                                    'restaurant_name' => [
                                        'type' => 'STRING',
                                        'description' => 'Tên quán ăn (ví dụ: Pizza FoodXpress Đà Nẵng)'
                                    ],
                                    'destination_address' => [
                                        'type' => 'STRING',
                                        'description' => 'Địa chỉ giao hàng (tùy chọn). Nếu không có, hệ thống sẽ cố lấy vị trí mặc định.'
                                    ]
                                ],
                                'required' => ['restaurant_name']
                            ]
                        ],
                        [
                            'name' => 'get_active_vouchers',
                            'description' => 'Lấy danh sách các mã giảm giá (voucher) đang có hiệu lực trên hệ thống để giới thiệu cho khách hàng.',
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
                            'name' => 'analyze_order_history',
                            'description' => 'Đọc lịch sử mua hàng của khách hàng hiện tại để tìm ra các món ăn họ yêu thích và thường xuyên mua nhất, từ đó gợi ý cho họ.',
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
                            'name' => 'issue_apology_voucher',
                            'description' => 'Tạo một mã giảm giá đền bù trị giá 20.000đ. GỌI CÔNG CỤ NÀY khi khách hàng phàn nàn, tức giận, hoặc có trải nghiệm không tốt.',
                            'parameters' => [
                                'type' => 'OBJECT',
                                'properties' => [
                                    'reason' => [
                                        'type' => 'STRING',
                                        'description' => 'Lý do tặng voucher (ví dụ: Xin lỗi vì đơn hàng giao chậm)'
                                    ]
                                ],
                                'required' => ['reason']
                            ]
                        ]
                    ]
                ]
            ];

            // 4. VÒNG LẶP GỌI GEMINI API (Multi-turn Agent Loop)
            $maxTurns = 3; // Giảm từ 5 xuống 3 để tránh overload
            $turn = 0;
            $cartUpdated = false;
            $replyText = "";
            $widgetData = null; // Thêm biến lưu widget data

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

                    // Gắn Widget Tracking nếu gọi hàm check_order_status thành công
                    if ($functionName === 'check_order_status' && isset($functionResult['data']) && count($functionResult['data']) > 0) {
                        $widgetData = [
                            'type' => 'tracking',
                            'order' => $functionResult['data'][0] // Hiện tracking cho đơn đầu tiên
                        ];
                    }

                    // Thêm Function Call vào lịch sử để Gemini biết nó đã gọi gì
                    if (empty($functionCall['args'])) {
                        $functionCall['args'] = new \stdClass();
                    }
                    
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
                                        'content' => (object)$functionResult
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
                'cart_updated' => $cartUpdated,
                'widget_data' => $widgetData
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
     * Logic bắt nhanh các câu hỏi mẫu để phản hồi ngay lập tức, tiết kiệm chi phí gọi LLM
     */
    private function getFastReply($userMessage, $user)
    {
        $messageLower = mb_strtolower(trim($userMessage), 'UTF-8');
        $cartUpdated = false;

        // 1. Bắt câu hỏi Tracking Đơn Hàng
        if (str_contains($messageLower, 'đơn hàng của mình đang ở đâu') || str_contains($messageLower, 'đơn hàng của tôi ở đâu')) {
            $result = $this->executeLocalFunction('check_order_status', [], $user, $cartUpdated);
            if ($result['status'] === 'success' && !empty($result['data'])) {
                return [
                    'success' => true,
                    'reply' => 'Đây là thông tin đơn hàng đang giao của bạn nhé:',
                    'cart_updated' => false,
                    'widget_data' => [
                        'type' => 'tracking',
                        'order' => $result['data'][0]
                    ]
                ];
            } else {
                return [
                    'success' => true,
                    'reply' => $result['message'],
                    'cart_updated' => false,
                    'widget_data' => null
                ];
            }
        }

        // 2. Bắt câu hỏi Mã Giảm Giá
        if (str_contains($messageLower, 'có mã giảm giá nào không')) {
            $result = $this->executeLocalFunction('get_active_vouchers', [], $user, $cartUpdated);
            if ($result['status'] === 'success' && !empty($result['data'])) {
                $reply = "Hiện tại hệ thống đang có các mã giảm giá cực kỳ hấp dẫn sau:\n\n";
                foreach ($result['data'] as $v) {
                    $discount = $v['discount_type'] == 'percent' ? $v['discount_value'] . '%' : number_format($v['discount_value'], 0, ',', '.') . 'đ';
                    $reply .= "* **" . $v['code'] . "**: Giảm " . $discount . " (Đơn tối thiểu " . number_format($v['minimum_order_price'], 0, ',', '.') . "đ)\n";
                }
                $reply .= "\nNhanh tay sử dụng khi thanh toán bạn nhé!";
                return [
                    'success' => true,
                    'reply' => $reply,
                    'cart_updated' => false,
                    'widget_data' => null
                ];
            } else {
                return [
                    'success' => true,
                    'reply' => 'Tiếc quá, hiện tại FoodXpress đang tạm hết mã giảm giá. Bạn hãy theo dõi thêm nhé!',
                    'cart_updated' => false,
                    'widget_data' => null
                ];
            }
        }

        if (str_contains($messageLower, 'phí ship tính thế nào')) {
            $baseShippingFee = \App\Models\Setting::getValue('base_shipping_fee', 15000);
            $baseRadiusKm = \App\Models\Setting::getValue('base_radius_km', 2);
            $extraShippingFee = \App\Models\Setting::getValue('extra_shipping_fee_per_km', 3000);
            
            return [
                'success' => true,
                'reply' => "Phí giao hàng của FoodXpress được tính rất ưu đãi như sau:\n* **" . number_format($baseShippingFee, 0, ',', '.') . "đ** cho {$baseRadiusKm}km đầu tiên.\n* Từ km thứ " . ($baseRadiusKm + 1) . " trở đi, cộng thêm **" . number_format($extraShippingFee, 0, ',', '.') . "đ/km**.\n\nNếu bạn muốn tính phí chính xác, hãy hỏi mình kèm theo tên quán ăn nhé (Ví dụ: *Tính phí ship từ quán Pizza FoodXpress*)!",
                'cart_updated' => false,
                'widget_data' => null
            ];
        }

        return null;
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
                    ->with(['user', 'options']); // Lấy kèm thông tin nhà hàng và toppings

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
                
                $customerLat = session('user_latitude');
                $customerLng = session('user_longitude');
                if ($user && $user->latitude && $user->longitude) {
                    $customerLat = $user->latitude;
                    $customerLng = $user->longitude;
                }

                $results = [];
                foreach ($products as $p) {
                    $shippingInfo = "Chưa rõ vị trí khách hàng để tính phí ship";
                    if ($customerLat && $customerLng && $p->user && $p->user->latitude && $p->user->longitude) {
                        $distance = $this->distanceBetweenCoordinates($p->user->latitude, $p->user->longitude, $customerLat, $customerLng);
                        $baseFee = \App\Models\Setting::getValue('base_shipping_fee', 15000);
                        $baseRadiusKm = \App\Models\Setting::getValue('base_radius_km', 2);
                        $extraFeePerKm = \App\Models\Setting::getValue('extra_shipping_fee_per_km', 3000);
                        
                        $additionalFee = max(0, $distance - $baseRadiusKm) * $extraFeePerKm;
                        $shippingFee = $baseFee + $additionalFee;
                        $shippingInfo = "Khoảng cách: " . round($distance, 1) . "km, Phí ship: " . number_format($shippingFee, 0, ',', '.') . "đ";
                    }

                    $optionsStr = '';
                    if ($p->options && $p->options->count() > 0) {
                        $ops = [];
                        foreach ($p->options as $opt) {
                            $price = $opt->additional_price > 0 ? " (+" . number_format($opt->additional_price, 0, ',', '.') . "đ)" : "";
                            $ops[] = $opt->option_name . ": " . $opt->option_value . $price;
                        }
                        $optionsStr = implode(', ', $ops);
                    }

                    $imageUrl = '';
                    if ($p->image) {
                        if (str_starts_with($p->image, 'http')) {
                            $imageUrl = $p->image;
                        } else {
                            $imageUrl = asset('storage/' . $p->image);
                        }
                    }

                    $results[] = [
                        'id' => $p->id,
                        'name' => $p->name,
                        'price' => $p->price,
                        'restaurant_name' => $p->user->restaurant_name ?? $p->user->name,
                        'restaurant_address' => $p->user->address ?? 'Đang cập nhật địa chỉ',
                        'description' => $p->description,
                        'image_url' => $imageUrl,
                        'toppings_and_options' => $optionsStr ?: 'Không có',
                        'estimated_shipping' => $shippingInfo
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
                        'id' => $order->id,
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

            case 'remove_from_cart':
                if (!$user) return ['status' => 'error', 'message' => 'Yêu cầu đăng nhập.'];
                $productName = $args['product_name'] ?? '';
                if (empty($productName)) return ['status' => 'error', 'message' => 'Cần tên món ăn.'];
                
                $deleted = CartItem::where('user_id', $user->id)
                    ->whereHas('product', function($q) use($productName) {
                        $q->where('name', 'like', "%{$productName}%");
                    })->delete();
                
                if ($deleted) {
                    $cartUpdated = true;
                    return ['status' => 'success', 'message' => "Đã xóa $productName khỏi giỏ hàng."];
                }
                return ['status' => 'error', 'message' => "Không tìm thấy $productName trong giỏ hàng."];

            case 'update_cart_quantity':
                if (!$user) return ['status' => 'error', 'message' => 'Yêu cầu đăng nhập.'];
                $productName = $args['product_name'] ?? '';
                $quantity = $args['quantity'] ?? 1;
                if (empty($productName)) return ['status' => 'error', 'message' => 'Cần tên món ăn.'];
                if ($quantity <= 0) return ['status' => 'error', 'message' => 'Số lượng phải lớn hơn 0.'];

                $cartItem = CartItem::where('user_id', $user->id)
                    ->whereHas('product', function($q) use($productName) {
                        $q->where('name', 'like', "%{$productName}%");
                    })->first();

                if ($cartItem) {
                    $cartItem->update(['quantity' => $quantity]);
                    $cartUpdated = true;
                    return ['status' => 'success', 'message' => "Đã cập nhật số lượng $productName thành $quantity."];
                }
                return ['status' => 'error', 'message' => "Không tìm thấy $productName trong giỏ hàng."];

            case 'calculate_shipping_fee':
                $restaurantName = $args['restaurant_name'] ?? '';
                $destAddress = $args['destination_address'] ?? '';

                if (empty($restaurantName)) {
                    return ['status' => 'error', 'message' => 'Cần cung cấp tên quán ăn.'];
                }

                $restaurant = User::where('role', 'restaurant')
                    ->where('status', 'active')
                    ->where(function ($q) use ($restaurantName) {
                        $q->where('restaurant_name', 'like', "%{$restaurantName}%")
                          ->orWhere('name', 'like', "%{$restaurantName}%");
                    })->first();

                if (!$restaurant || !$restaurant->latitude || !$restaurant->longitude) {
                    return ['status' => 'error', 'message' => "Không tìm thấy tọa độ của quán '$restaurantName'."];
                }

                $customerLat = null;
                $customerLng = null;

                if (!empty($destAddress)) {
                    // Geocode via Nominatim
                    try {
                        // Nominatim yêu cầu User-Agent hợp lệ
                        $response = \Illuminate\Support\Facades\Http::withHeaders([
                            'User-Agent' => 'FoodXpress-Agent'
                        ])->timeout(5)->get('https://nominatim.openstreetmap.org/search', [
                            'q' => $destAddress,
                            'format' => 'json',
                            'limit' => 1
                        ]);
                        
                        if ($response->successful() && count($response->json()) > 0) {
                            $geo = $response->json()[0];
                            $customerLat = floatval($geo['lat']);
                            $customerLng = floatval($geo['lon']);
                        } else {
                            return ['status' => 'error', 'message' => "Không thể tìm thấy tọa độ cho địa chỉ: $destAddress"];
                        }
                    } catch (\Exception $e) {
                        return ['status' => 'error', 'message' => 'Lỗi kết nối dịch vụ bản đồ: ' . $e->getMessage()];
                    }
                } elseif ($user && $user->latitude && $user->longitude) {
                    $customerLat = $user->latitude;
                    $customerLng = $user->longitude;
                    $destAddress = $user->address ?? 'địa chỉ đăng ký';
                } elseif (session()->has('user_latitude')) {
                    $customerLat = session('user_latitude');
                    $customerLng = session('user_longitude');
                    $destAddress = 'vị trí hiện tại của bạn';
                } else {
                    return ['status' => 'error', 'message' => 'Khách hàng chưa đăng nhập hoặc chưa cung cấp địa chỉ giao hàng. Vui lòng hỏi địa chỉ cụ thể.'];
                }

                if ($customerLat && $customerLng) {
                    $distance = $this->distanceBetweenCoordinates($restaurant->latitude, $restaurant->longitude, $customerLat, $customerLng);
                    $baseFee = \App\Models\Setting::getValue('base_shipping_fee', 15000);
                    $baseRadiusKm = \App\Models\Setting::getValue('base_radius_km', 2);
                    $extraFeePerKm = \App\Models\Setting::getValue('extra_shipping_fee_per_km', 3000);
                    
                    $additionalFee = max(0, $distance - $baseRadiusKm) * $extraFeePerKm;
                    $shippingFee = $baseFee + $additionalFee;

                    return [
                        'status' => 'success',
                        'distance_km' => round($distance, 1),
                        'shipping_fee' => round($shippingFee, 0),
                        'message' => "Khoảng cách từ {$restaurant->restaurant_name} đến '$destAddress' là " . round($distance, 1) . " km. Phí ship: " . number_format($shippingFee, 0, ',', '.') . "đ."
                    ];
                }

                return ['status' => 'error', 'message' => 'Không thể xác định vị trí để tính phí.'];

            case 'get_active_vouchers':
                $vouchers = \App\Models\Voucher::active()->get();
                if ($vouchers->isEmpty()) {
                    return ['status' => 'empty', 'message' => 'Hiện tại hệ thống không có mã giảm giá nào.'];
                }
                
                $results = [];
                foreach ($vouchers as $v) {
                    $results[] = [
                        'code' => $v->code,
                        'discount_type' => $v->discount_type, // 'percent' or 'fixed'
                        'discount_value' => $v->discount_value,
                        'minimum_order_price' => $v->minimum_product_price,
                        'expires_at' => $v->expires_at
                    ];
                }
                return ['status' => 'success', 'data' => $results];

            case 'analyze_order_history':
                if (!$user) {
                    return ['status' => 'error', 'message' => 'Khách hàng chưa đăng nhập. Không thể phân tích sở thích.'];
                }

                $orders = \App\Models\Order::where('user_id', $user->id)->with('items.product')->get();
                if ($orders->isEmpty()) {
                    return ['status' => 'empty', 'message' => 'Khách hàng chưa từng đặt món nào trên FoodXpress. Hãy gợi ý các món phổ biến như Trà sữa, Gà rán, Pizza...'];
                }

                $productCounts = [];
                foreach ($orders as $order) {
                    foreach ($order->items as $item) {
                        if ($item->product) {
                            $pid = $item->product->id;
                            if (!isset($productCounts[$pid])) {
                                $productCounts[$pid] = [
                                    'name' => $item->product->name,
                                    'count' => 0
                                ];
                            }
                            $productCounts[$pid]['count'] += $item->quantity;
                        }
                    }
                }

                usort($productCounts, function($a, $b) {
                    return $b['count'] <=> $a['count'];
                });

                $topProducts = array_slice($productCounts, 0, 3);
                $recommendations = array_map(function($p) {
                    return $p['name'] . " (đã mua {$p['count']} lần)";
                }, $topProducts);

                return [
                    'status' => 'success',
                    'message' => 'Lịch sử mua hàng cho thấy khách thích: ' . implode(', ', $recommendations) . '. Hãy dựa vào đây để đưa ra lời khuyên thật tự nhiên.'
                ];

            case 'issue_apology_voucher':
                if (!$user) return ['status' => 'error', 'message' => 'Yêu cầu đăng nhập.'];
                
                $voucherCode = 'SORRY-' . strtoupper(substr(md5(uniqid()), 0, 6));
                
                \App\Models\Voucher::create([
                    'code' => $voucherCode,
                    'discount_type' => 'fixed',
                    'discount_value' => 20000, // Đền bù 20k
                    'min_order_value' => 0,
                    'max_discount' => 20000,
                    'usage_limit' => 1,
                    'used_count' => 0,
                    'start_date' => now(),
                    'end_date' => now()->addDays(7), // Hạn dùng 7 ngày
                    'description' => 'Voucher đền bù trải nghiệm: ' . ($args['reason'] ?? 'Xin lỗi khách hàng'),
                    'is_active' => true,
                ]);

                return [
                    'status' => 'success', 
                    'voucher_code' => $voucherCode,
                    'message' => "Đã tạo voucher đền bù thành công. Mã: $voucherCode. Trị giá 20.000đ. Hạn sử dụng: 7 ngày."
                ];

            default:
                return ['status' => 'error', 'message' => 'Công cụ không được hỗ trợ.'];
        }
    }

    /**
     * Tính khoảng cách giữa hai điểm tọa độ bằng công thức Haversine (km)
     */
    private function distanceBetweenCoordinates($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadiusKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadiusKm * $c;

        return $distance;
    }
}
