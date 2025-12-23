<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupportController extends Controller
{
    public function index()
    {
        // Get all customers who have sent messages, with unread count and last message info
        $customers = User::where('user_type', 'customer')
            ->whereHas('supportMessages')
            ->withCount([
                'supportMessages as unread_count' => function ($query) {
                    $query->where('sender_type', 'customer')
                        ->where('is_read_by_admin', false);
                }
            ])
            ->with(['supportMessages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->get()
            ->map(function ($customer) {
                $threadId = SupportMessage::generateThreadId($customer->id);
                $lastMessage = SupportMessage::where('thread_id', $threadId)
                    ->latest()
                    ->first();
                
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'thread_id' => $threadId,
                    'unread_count' => SupportMessage::where('thread_id', $threadId)
                        ->where('sender_type', 'customer')
                        ->where('is_read_by_admin', false)
                        ->count(),
                    'last_message' => $lastMessage ? $lastMessage->message : null,
                    'last_message_time' => $lastMessage ? $lastMessage->created_at : null,
                ];
            })
            ->sortByDesc(function ($customer) {
                // Sort by unread count first, then by last message time
                return [$customer['unread_count'], $customer['last_message_time'] ? $customer['last_message_time']->timestamp : 0];
            })
            ->values();
        
        // Paginate manually
        $perPage = 15;
        $currentPage = request()->get('page', 1);
        $items = $customers->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $total = $customers->count();
        
        $customers = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        
        return view('admin.support.index', compact('customers'));
    }
    
    public function show($threadId)
    {
        // Extract user ID from thread_id (format: user_{user_id})
        $userId = str_replace('user_', '', $threadId);
        $customer = User::findOrFail($userId);
        
        // Get recent messages (last 7 days)
        $recentMessages = SupportMessage::where('thread_id', $threadId)
            ->recent()
            ->orderBy('created_at', 'asc')
            ->with(['user', 'admin'])
            ->get();
        
        // Check if old messages exist
        $hasOldMessages = SupportMessage::where('thread_id', $threadId)
            ->old()
            ->exists();
        
        // Mark customer messages as read when admin views
        SupportMessage::where('thread_id', $threadId)
            ->where('sender_type', 'customer')
            ->where('is_read_by_admin', false)
            ->update([
                'is_read_by_admin' => true,
                'read_at' => now()
            ]);
        
        return view('admin.support.show', compact('customer', 'recentMessages', 'hasOldMessages', 'threadId'));
    }
    
    public function reply(Request $request)
    {
        $request->validate([
            'thread_id' => 'required|string',
            'message' => 'required|string|max:5000',
        ]);
        
        $admin = Auth::user();
        $threadId = $request->thread_id;
        
        // Extract user ID from thread_id
        $userId = str_replace('user_', '', $threadId);
        $customer = User::findOrFail($userId);
        
        SupportMessage::create([
            'thread_id' => $threadId,
            'user_id' => $customer->id,
            'admin_id' => $admin->id,
            'message' => $request->message,
            'sender_type' => 'admin',
            'is_read_by_customer' => false,
            'is_read_by_admin' => true, // Admin always reads their own messages
        ]);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Reply sent successfully'
            ]);
        }
        
        return redirect()->route('admin.support.show', $threadId)
            ->with('success', 'Reply sent successfully!');
    }
    
    public function markAsRead(Request $request)
    {
        $threadId = $request->thread_id;
        
        SupportMessage::where('thread_id', $threadId)
            ->where('sender_type', 'customer')
            ->where('is_read_by_admin', false)
            ->update([
                'is_read_by_admin' => true,
                'read_at' => now()
            ]);
        
        return response()->json(['success' => true]);
    }
    
    public function getUnreadCount()
    {
        $count = SupportMessage::where('sender_type', 'customer')
            ->where('is_read_by_admin', false)
            ->count();
        
        return response()->json(['count' => $count]);
    }
    
    public function getMessages(Request $request, $threadId)
    {
        $showOld = $request->query('old', false);
        
        $query = SupportMessage::where('thread_id', $threadId)
            ->with(['user', 'admin']);
        
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
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'sender_type' => $message->sender_type,
                    'customer_name' => $message->user ? $message->user->name : null,
                    'admin_name' => $message->admin ? $message->admin->name : null,
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                    'created_at_formatted' => $message->created_at->format('M d, Y h:i A'),
                ];
            })
        ]);
    }
}
