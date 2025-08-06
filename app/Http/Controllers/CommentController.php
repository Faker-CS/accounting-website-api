<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\NewComment;
use App\Events\DeleteComment;

class CommentController extends Controller
{
    public function store(Request $request, $taskId)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        $task = Task::findOrFail($taskId);
        
        $comment = $task->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->content
        ]);

        // Load the user relationship for the response
        $comment->load('user');

        // Broadcast the new comment event
        broadcast(new NewComment($comment))->toOthers();

        return response()->json($comment);
    }

    public function index($taskId)
    {
        $task = Task::findOrFail($taskId);
        $comments = $task->comments()->with('user')->latest()->get();
        
        return response()->json($comments);
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        
        // Only allow the comment owner to delete
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $taskId = $comment->task_id;
        $comment->delete();
        
        // Broadcast the delete event
        broadcast(new DeleteComment($id, $taskId))->toOthers();
        
        return response()->json(['message' => 'Comment deleted successfully']);
    }
} 