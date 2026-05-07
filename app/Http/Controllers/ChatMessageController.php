<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\ChatMessage;
use App\Events\ChatMessageSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatMessageController extends Controller
{
    /**
     * Fetch chat history for an order.
     */
    public function index($orderId)
    {
        $order = Order::findOrFail($orderId);
        
        // Authorization check
        if (!$this->canAccessChat($order)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = ChatMessage::where('order_id', $orderId)
            ->with('user:id,name,role,avatar')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    /**
     * Store and broadcast a new message.
     */
    public function store(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        // Authorization check
        if (!$this->canAccessChat($order)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'nullable|string',
            'image' => 'nullable|image|max:5120', // Max 5MB
        ]);

        if (!$request->message && !$request->hasFile('image')) {
            return response()->json(['error' => 'Message or image is required'], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('chat_images', 'public');
        }

        $message = ChatMessage::create([
            'order_id' => $orderId,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'image_path' => $imagePath ? '/storage/' . $imagePath : null,
        ]);

        // Broadcast the event
        broadcast(new ChatMessageSent($message))->toOthers();

        return response()->json($message->load('user:id,name,role,avatar'));
    }

    /**
     * Helper method to check if current user can access the chat for this order.
     * Allowed: Customer, Shipper assigned to the order, Restaurant owner of the order's products.
     */
    private function canAccessChat(Order $order)
    {
        $user = Auth::user();
        if (!$user) return false;

        // Is Customer?
        if ($user->id === $order->user_id) return true;

        // Is Shipper?
        if ($order->shipper && $order->shipper->user_id === $user->id) return true;

        return false;
    }
}
