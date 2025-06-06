<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Comment;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Campaign $campaign)
    {
        // Comments associated with this campaign
        $comments = $campaign->comments()->latest()->paginate(5);

        return Inertia::render('Admin/Comments/Index', [
            'comments' => $comments,
            'campaign' => $campaign,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campaign $campaign, Comment $comment): RedirectResponse
    {
        try {
            $comment->delete();
            return redirect()->route('admin.campaigns.comments.index', $campaign->id)
                ->with('success', 'Comments deleted successfully');

        } catch (Exception $e) {
            return back()->with('error', 'Error deleting comment: ' . $e->getMessage());
        }
    }
}
