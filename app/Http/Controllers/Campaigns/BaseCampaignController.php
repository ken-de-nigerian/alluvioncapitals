<?php

namespace App\Http\Controllers\Campaigns;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Category;
use Illuminate\Http\Request;

abstract class BaseCampaignController extends Controller
{
    /**
     * @return mixed
     */
    protected function getCategories()
    {
        return Category::where('status', 'active')
            ->select('id', 'name', 'slug', 'image')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array
     */
    protected function getMinMaxGoals()
    {
        return [
            'min' => Campaign::min('goal') ?? 0,
            'max' => Campaign::max('goal') ?? 50000
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function getFilters(Request $request)
    {
        return [
            'goal_min' => $request->input('goal_min'),
            'goal_max' => $request->input('goal_max'),
            'deadline' => $request->input('deadline'),
            'category' => $request->input('category'),
            'tab' => $request->input('tab', 'all')
        ];
    }
}
