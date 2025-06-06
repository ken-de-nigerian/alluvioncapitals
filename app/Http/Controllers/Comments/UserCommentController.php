<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Inertia\Inertia;

class UserCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Campaign $campaign)
    {
        // Comments associated with this campaign
        $comments = $campaign->comments()->latest()->paginate(5);

        return Inertia::render('User/Comments/Index', [
            'comments' => $comments,
            'campaign' => $campaign,
        ]);
    }
}
