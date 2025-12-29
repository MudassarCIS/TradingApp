<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'message' => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|mimes:jpeg,jpg,png,gif,pdf,doc,docx|max:10240', // 10MB max
        ]);
        
        // At least message or attachment must be provided
        if (!$request->has('message') && !$request->hasFile('attachment')) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide a message or attachment'
                ], 422);
            }
            return redirect()->route('customer.support.index')
                ->with('error', 'Please provide a message or attachment');
        }
        
        $user = Auth::user();
        $threadId = SupportMessage::generateThreadId($user->id);
        
        $attachmentPath = null;
        $attachmentName = null;
        $attachmentType = null;
        
        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $extension = $file->getClientOriginalExtension();
            $originalName = $file->getClientOriginalName();
            
            // Determine file type
            $imageExtensions = ['jpeg', 'jpg', 'png', 'gif'];
            $pdfExtensions = ['pdf'];
            $wordExtensions = ['doc', 'docx'];
            
            if (in_array(strtolower($extension), $imageExtensions)) {
                $attachmentType = 'image';
            } elseif (in_array(strtolower($extension), $pdfExtensions)) {
                $attachmentType = 'pdf';
            } elseif (in_array(strtolower($extension), $wordExtensions)) {
                $attachmentType = 'word';
            }
            
            // Store file in public/support-attachments directory
            $filename = Str::random(40) . '.' . $extension;
            $path = $file->storeAs('support-attachments', $filename, 'public');
            $attachmentPath = $path;
            $attachmentName = $originalName;
        }
        
        // Save customer message
        SupportMessage::create([
            'thread_id' => $threadId,
            'user_id' => $user->id,
            'message' => $request->message ?? '',
            'attachment' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_type' => $attachmentType,
            'sender_type' => 'customer',
            'is_read_by_customer' => true, // Customer always reads their own messages
            'is_read_by_admin' => false,
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
                
                $attachmentUrl = null;
                if ($message->attachment) {
                    $attachmentUrl = Storage::url($message->attachment);
                }
                
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'attachment' => $attachmentUrl,
                    'attachment_name' => $message->attachment_name,
                    'attachment_type' => $message->attachment_type,
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
    
    public function viewAttachment($id)
    {
        $user = Auth::user();
        $message = SupportMessage::findOrFail($id);
        
        // Verify user has access to this message
        $threadId = SupportMessage::generateThreadId($user->id);
        if ($message->thread_id !== $threadId) {
            abort(403, 'Unauthorized access');
        }
        
        if (!$message->attachment || !Storage::disk('public')->exists($message->attachment)) {
            abort(404, 'File not found');
        }
        
        $filePath = Storage::disk('public')->path($message->attachment);
        $mimeType = Storage::disk('public')->mimeType($message->attachment);
        
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $message->attachment_name . '"',
        ]);
    }
}
