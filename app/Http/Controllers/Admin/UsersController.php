<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::select('id', 'avatar', 'first_name', 'last_name', 'status', 'role')
                ->when($request->search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%");
                    });
                })
                ->when($request->sort === 'oldest', function ($query) {
                    $query->oldest();
                }, function ($query) {
                    $query->latest();
                })
                ->paginate(10)
                ->withQueryString(),
            'filters' => [
                'search' => $request->search,
                'sort' => $request->sort
            ]
        ]);
    }
}
