<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $threadId = SupportMessage::generateThreadId($user->id);
        
        // Get recent messages (last 7 days)
        $recentMessages = SupportMessage::where('thread_id', $threadId)
            ->recent()
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Check if old messages exist
        $hasOldMessages = SupportMessage::where('thread_id', $threadId)
            ->old()
            ->exists();
        
        // Mark admin messages as read when customer views
        SupportMessage::where('thread_id', $threadId)
            ->where('sender_type', 'admin')
            ->where('is_read_by_customer', false)
            ->update([
                'is_read_by_customer' => true,
                'read_at' => now()
            ]);
        
        return view('customer.support.index', compact('recentMessages', 'hasOldMessages', 'threadId'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);
        
        $user = Auth::user();
        $threadId = SupportMessage::generateThreadId($user->id);
        
        // Save customer message
        SupportMessage::create([
            'thread_id' => $threadId,
            'user_id' => $user->id,
            'message' => $request->message,
            'sender_type' => 'customer',
            'is_read_by_customer' => true, // Customer always reads their own messages
            'is_read_by_admin' => false,
        ]);
        
        // Automatically send confirmation message from support system
        SupportMessage::create([
            'thread_id' => $threadId,
            'user_id' => $user->id,
            'admin_id' => null, // System message, no specific admin
            'message' => 'Thank you for your message, our support agent will reply shortly to your message. Thanks',
            'sender_type' => 'admin',
            'is_read_by_customer' => false,
            'is_read_by_admin' => true, // System message is considered read by admin
        ]);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully'
            ]);
        }
        
        return redirect()->route('customer.support.index')
            ->with('success', 'Message sent successfully!');
    }
    
    public function getMessages(Request $request)
    {
        $user = Auth::user();
        $threadId = SupportMessage::generateThreadId($user->id);
        $showOld = $request->query('old', false);
        
        $query = SupportMessage::where('thread_id', $threadId);
        
        if ($showOld) {
            // Return all messages
            $messages = $query->orderBy('created_at', 'asc')->get();
        } else {
            // Return only recent messages
            $messages = $query->recent()->orderBy('created_at', 'asc')->get();
        }
        
        return response()->json([
            'success' => true,
            'messages' => $messages->map(function ($message) {
                // For system messages (admin_id is null), show "Support System"
                $adminName = $message->admin ? $message->admin->name : ($message->sender_type === 'admin' && !$message->admin_id ? 'Support System' : null);
                
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'sender_type' => $message->sender_type,
                    'admin_name' => $adminName,
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                    'created_at_formatted' => $message->created_at->format('M d, Y h:i A'),
                ];
            })
        ]);
    }
    
    public function markAsRead(Request $request)
    {
        $user = Auth::user();
        $threadId = SupportMessage::generateThreadId($user->id);
        
        SupportMessage::where('thread_id', $threadId)
            ->where('sender_type', 'admin')
            ->where('is_read_by_customer', false)
            ->update([
                'is_read_by_customer' => true,
                'read_at' => now()
            ]);
        
        return response()->json(['success' => true]);
    }
    
    public function getUnreadCount()
    {
        $user = Auth::user();
        $threadId = SupportMessage::generateThreadId($user->id);
        
        $count = SupportMessage::where('thread_id', $threadId)
            ->unreadByCustomer()
            ->count();
        
        return response()->json(['count' => $count]);
    }
}
