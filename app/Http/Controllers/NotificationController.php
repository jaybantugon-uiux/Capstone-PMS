<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display all notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $notifications = $user->notifications()
            ->whereIn('type', [
                'App\Notifications\ExpenseLiquidationNotification'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('finance.notifications.index', compact('notifications'));
    }
    
    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, $notificationId)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($notificationId);
        
        $notification->markAsRead();
        
        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->back()->with('success', 'Notification marked as read');
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        
        $user->unreadNotifications()
            ->whereIn('type', [
                'App\Notifications\ExpenseLiquidationNotification'
            ])
            ->update(['read_at' => now()]);
            
        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->back()->with('success', 'All notifications marked as read');
    }
    
    /**
     * Delete a notification
     */
    public function destroy(Request $request, $notificationId)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($notificationId);
        
        $notification->delete();
        
        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->back()->with('success', 'Notification deleted');
    }
}
