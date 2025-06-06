<?php

namespace App\Http\Controllers\Campaigns;

use App\Http\Controllers\Controller;
use App\Mail\CampaignCreated;
use App\Models\Campaign;
use App\Models\Category;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Intervention\Image\Laravel\Facades\Image;

class AddCampaignController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function addDetails(Request $request)
    {
        $categories = Category::select(['id', 'name'])
            ->orderBy('name')
            ->get();

        // Get session data if exists
        $sessionDetails = $request->session()->get('campaign.add.details', []);

        return Inertia::render('Campaign/Create/AddCampaignDetails', [
            'categories' => $categories,
            'old' => $sessionDetails
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeDetails(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'goal' => 'required|numeric|min:1',
            'expires_at' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    try {
                        $date = Carbon::parse($value);
                        if ($date->isPast()) {
                            $fail('The expiration date must be in the future.');
                        }
                    } catch (Exception $e) {
                        $fail('The expiration date is not a valid date: ' . $e->getMessage());
                    }
                },
            ],
            'summary' => 'required',
        ]);

        // Store the validated data in session
        $request->session()->put('campaign.add.details', [
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'category_id' => $validated['category_id'],
            'goal' => $validated['goal'],
            'expires_at' => Carbon::parse($validated['expires_at'])->format('Y-m-d'),
            'summary' => $validated['summary'],
        ]);

        return redirect()->route('campaigns.add.contact.info');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function addContactInfo(Request $request)
    {
        // Check if campaign details exist in session
        if (!$request->session()->has('campaign.add.details')) {
            return redirect()
                ->route('campaigns.add.details')
                ->with('error', 'Please complete campaign details first');
        }

        // Get session data if exists
        $sessionDetails = $request->session()->get('campaign.add.contact-info', []);

        return Inertia::render('Campaign/Create/AddCampaignContactInfo', [
            'old' => $sessionDetails
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeContactInfo(Request $request)
    {
        // Check if campaign details exist in session
        if (!$request->session()->has('campaign.add.details')) {
            return redirect()
                ->route('campaigns.add.details')
                ->with('error', 'Please complete campaign details first');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone_number' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'country_id' => 'required',
            'state' => 'required|string|max:255',
            'state_id' => 'required',
            'city' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        // Store the validated data in session
        $request->session()->put('campaign.add.contact-info', [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'country' => $validated['country'],
            'country_id' => $validated['country_id'],
            'state' => $validated['state'],
            'state_id' => $validated['state_id'],
            'city' => $validated['city'],
            'address' => $validated['address'],
        ]);

        // Redirect to the next step with the campaign ID
        return redirect()->route('campaigns.add.photos.videos');
    }

    /**
     * Show the form for adding photos and videos.
     */
    public function addPhotosAndVideos(Request $request)
    {
        // Check if previous steps are completed
        if (!$request->session()->has('campaign.add.details') ||
            !$request->session()->has('campaign.add.contact-info')) {
            return redirect()
                ->route('campaigns.add.details')
                ->with('error', 'Please complete previous steps first');
        }

        // Get session data if exists
        $sessionData = $request->session()->get('campaign.add.media', []);

        return Inertia::render('Campaign/Create/AddCampaignPhotosAndVideos', [
            'media' => $sessionData
        ]);
    }

    /**
     * Store photos and videos in session.
     */
    public function storePhotosAndVideos(Request $request)
    {
        // Validate previous steps
        if (!$request->session()->has('campaign.add.details') ||
            !$request->session()->has('campaign.add.contact-info')) {
            return redirect()
                ->route('campaigns.add.details')
                ->with('error', 'Please complete previous steps first');
        }

        $validated = $request->validate([
            'photos' => 'required_without:existing_photos|array|min:1',
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'existing_photos' => 'sometimes|array',
            'existing_photos.*' => 'sometimes|string',
            'campaign_video' => 'nullable|url',
        ]);

        // Handle file uploads
        $photos = [];

        // Store new photos with full URLs
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $storedPath = $photo->store('temp/campaigns', 'public');
                $photos[] = Storage::disk('public')->url($storedPath);
            }
        }

        // Keep existing photos (assuming these are already full URLs)
        if ($request->has('existing_photos')) {
            $photos = array_merge($photos, $request->input('existing_photos'));
        }

        // Store media data in session
        $request->session()->put('campaign.add.media', [
            'campaign_images' => $photos,
            'campaign_video' => $validated['campaign_video'] ?? null,
        ]);

        return redirect()->route('campaigns.add.supporting.documents');
    }

    /**
     * Show the form for adding supporting documents.
     */
    public function addSupportingDocuments(Request $request)
    {
        // Check if previous steps are completed
        if (!$request->session()->has('campaign.add.details') ||
            !$request->session()->has('campaign.add.contact-info') ||
            !$request->session()->has('campaign.add.media')) {
            return redirect()
                ->route('campaigns.add.details')
                ->with('error', 'Please complete previous steps first');
        }

        // Get session data if exists
        $sessionData = $request->session()->get('campaign.add.documents', []);

        return Inertia::render('Campaign/Create/AddCampaignSupportingDocuments', [
            'documents' => $sessionData
        ]);
    }

    /**
     * Store supporting documents (images and files) in session.
     */
    public function storeSupportingDocuments(Request $request)
    {
        // Check if previous steps are completed
        if (!$request->session()->has('campaign.add.details') ||
            !$request->session()->has('campaign.add.contact-info') ||
            !$request->session()->has('campaign.add.media')) {
            return redirect()
                ->route('campaigns.add.details')
                ->with('error', 'Please complete previous steps first');
        }

        // Validate input
        $request->validate([
            'documents' => 'required_without:existing_documents|array|min:1',
            'documents.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:5120',
            'existing_documents' => 'sometimes|array',
            'existing_documents.*' => 'string'
        ]);

        // Get existing documents from input or default to empty
        $documents = [];

        // Handle new uploads
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $storedPath = $file->store('temp/documents', 'public');
                $documents[] = Storage::disk('public')->url($storedPath);
            }
        }

        // Keep existing documents (assuming these are already full URLs)
        if ($request->has('existing_documents')) {
            $documents = array_merge($documents, $request->input('existing_documents'));
        }

        // Store in session
        $request->session()->put('campaign.add.documents', [
            'supporting_documents' => $documents
        ]);

        return redirect()->route('campaigns.add.publish');
    }

    /**
     * Show the final form to create the campaign from session data.
     */
    public function publishCampaigns(Request $request)
    {
        // Check if previous steps are completed
        if (!$request->session()->has('campaign.add.details') ||
            !$request->session()->has('campaign.add.contact-info') ||
            !$request->session()->has('campaign.add.media') ||
            !$request->session()->has('campaign.add.documents')) {
            return redirect()
                ->route('campaigns.add.details')
                ->with('error', 'Please complete previous steps first');
        }

        return Inertia::render('Campaign/Create/PublishCampaigns');
    }

    /**
     * Final method to create the campaign from session data.
     */
    public function storePublishedCampaigns(Request $request)
    {
        // Check if previous steps are completed
        if (!$request->session()->has('campaign.add.details') ||
            !$request->session()->has('campaign.add.contact-info') ||
            !$request->session()->has('campaign.add.media') ||
            !$request->session()->has('campaign.add.documents')) {
            return redirect()
                ->route('campaigns.add.details')
                ->with('error', 'Please complete previous steps first');
        }

        try {

            $request->validate([
                'selected_plan' => 'required|json',
                'selected_addons' => 'nullable|json'
            ]);

            // Get all session data
            $details = $request->session()->get('campaign.add.details');
            $contactInfo = $request->session()->get('campaign.add.contact-info');
            $media = $request->session()->get('campaign.add.media', []);
            $documents = $request->session()->get('campaign.add.documents', []);

            // Extract file URLs from the session
            $campaignImageUrls = $media['campaign_images'] ?? [];
            $supportingDocumentUrls = $documents['supporting_documents'] ?? [];

            // Extract promotion details
            $hasAdPromotion = [
                'selected_plan' => json_decode($request->input('selected_plan', '{}'), true),
                'selected_addons' => json_decode($request->input('selected_addons', '[]'), true)
            ];

            // Ensure directories exist
            Storage::disk('public')->makeDirectory('campaign-images');
            Storage::disk('public')->makeDirectory('supporting-documents');

            // Process and move campaign images with resizing
            $finalImageUrls = [];
            foreach ($campaignImageUrls as $url) {
                // Extract relative path from URL
                $path = parse_url($url, PHP_URL_PATH);
                $path = str_replace('/storage/', '', $path);

                if (Storage::disk('public')->exists($path)) {
                    $filename = basename($path);
                    $newPath = 'campaign-images/' . $filename;

                    // Read and resize the image
                    $image = Image::read(Storage::disk('public')->get($path));
                    $image->resize(1120, 630);
                    Storage::disk('public')->put($newPath, $image->encode());

                    // Delete temporary file
                    Storage::disk('public')->delete($path);

                    // Store only the final public URL
                    $finalImageUrls[] = asset('storage/' . $newPath);
                }
            }

            // Move supporting documents
            $finalDocumentUrls = [];
            foreach ($supportingDocumentUrls as $url) {
                // Extract relative path from URL
                $path = parse_url($url, PHP_URL_PATH);
                $path = str_replace('/storage/', '', $path);

                if (Storage::disk('public')->exists($path)) {
                    $filename = basename($path);
                    $newPath = 'supporting-documents/' . $filename;
                    Storage::disk('public')->move($path, $newPath);

                    // Store only the final public URL
                    $finalDocumentUrls[] = asset('storage/' . $newPath);
                }
            }

            // Create campaign with only URLs
            Campaign::create(array_merge(
                $details,
                $contactInfo,
                [
                    'campaign_images' => json_encode($finalImageUrls),
                    'campaign_video' => $media['campaign_video'] ?? null,
                    'supporting_documents' => json_encode($finalDocumentUrls),
                    'has_ad_promotion' => json_encode($hasAdPromotion),
                    'user_id' => Auth::user()->id
                ]
            ));

            // Clear session data
            $request->session()->forget([
                'campaign.add.details',
                'campaign.add.contact-info',
                'campaign.add.media',
                'campaign.add.documents',
            ]);

            // Send email
            if (config('settings.email_notification')){
                Mail::mailer(config('settings.email_provider'))->to(Auth::user()->email)->send(new CampaignCreated(Auth::user()));
            }

            if (Auth::user()->role == 'admin'){
                return redirect()
                    ->route('admin.campaigns.index')
                    ->with('success', 'Campaign created successfully!');
            }else{
                return redirect()
                    ->route('user.campaigns.index')
                    ->with('success', 'Campaign created successfully!');
            }
        } catch (Exception $e) {
            Log::error('Campaign creation failed: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Failed to create campaign. Please try again.');
        }
    }
}
