<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\Campaign\CampaignController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DonationsAndWithdrawalsController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\Campaigns\AddCampaignController;
use App\Http\Controllers\Campaigns\CampaignCategoriesController;
use App\Http\Controllers\Campaigns\CampaignListingController;
use App\Http\Controllers\Campaigns\CampaignSearchController;
use App\Http\Controllers\Campaigns\CampaignShowController;
use App\Http\Controllers\Campaigns\EditCampaignController;
use App\Http\Controllers\Comments\CommentController;
use App\Http\Controllers\Comments\UserCommentController;
use App\Http\Controllers\Donations\DonationController;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\Home\WidgetController;
use App\Http\Controllers\Payment\FlutterwaveCallbackController;
use App\Http\Controllers\Payment\MonnifyCallbackController;
use App\Http\Controllers\Payment\PaymentStatusController;
use App\Http\Controllers\Payment\PaystackCallbackController;
use App\Http\Controllers\Payment\StripeCallbackController;
use App\Http\Controllers\Rewards\RewardController;
use App\Http\Controllers\Rewards\UserRewardController;
use App\Http\Controllers\Updates\UpdateController;
use App\Http\Controllers\Updates\UserUpdateController;
use App\Http\Controllers\User\Campaign\UserCampaignController;
use App\Http\Controllers\User\UserDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Home Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Authentication Routes
    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'index')->name('login');
        Route::post('/login', 'login')->name('login.store');
    });

    // Registration Routes
    Route::controller(RegisterController::class)->group(function () {
        Route::get('/register', 'index')->name('register');
        Route::post('/register', 'register')->name('register.store');
    });

    // Password Reset Routes
    Route::controller(ForgotPasswordController::class)->group(function () {
        Route::get('/password/reset', 'create')->name('password.request');
        Route::post('/password/email', 'store')->name('password.email');
        Route::get('/password/reset/{token}', 'edit')->name('password.reset');
        Route::post('/password/update', 'update')->name('password.update');
    });

    // Social Login
    Route::controller(SocialLoginController::class)->group(function () {
        Route::get('social/{provider}', 'redirectToProvider')->name('social.redirect');
        Route::get('social/callback/{provider}', 'handleProviderCallback')->name('social.callback');
    });
});

/*
|--------------------------------------------------------------------------
| Logout Routes
|--------------------------------------------------------------------------
*/
Route::prefix('logout')->middleware('auth')->controller(SessionController::class)->group(function () {
    Route::post('/', 'destroy')->name('logout');
    Route::post('/current-session/{sessionId}', 'destroySession')->name('logout.current');
    Route::delete('/all-sessions', 'destroyAllSessions')->name('logout.all');
    Route::post('social/{provider}/disconnect', 'invokeAccount')->name('social.disconnect');
});

/*
|--------------------------------------------------------------------------
| Embedded Widget
|--------------------------------------------------------------------------
*/
Route::prefix('widget')->name('widget.')->group(function () {
    Route::get('/l/{slug}', [WidgetController::class, 'widgetsLarge'])->name('widgets.large');
    Route::get('/s/{slug}', [WidgetController::class, 'widgetsSmall'])->name('widgets.small');
});

/*
|--------------------------------------------------------------------------
| Payment Callback Routes
|--------------------------------------------------------------------------
*/
Route::prefix('callback')->name('callback.')->group(function () {
    Route::get('/paystack', [PaystackCallbackController::class, 'paystack'])->name('paystack');
    Route::get('/flutterwave', [FlutterwaveCallbackController::class, 'flutterwave'])->name('flutterwave');
    Route::get('/monnify', [MonnifyCallbackController::class, 'monnify'])->name('monnify');
    Route::get('/stripe', [StripeCallbackController::class, 'stripe'])->name('stripe');
});

/*
|--------------------------------------------------------------------------
| Payment Error Routes
|--------------------------------------------------------------------------
*/
Route::prefix('payment')->name('payment.')->group(function () {
    Route::get('/failed/{donation}', [PaymentStatusController::class, 'failed'])->name('failed');
    Route::get('/cancelled/{donation}', [PaymentStatusController::class, 'cancelled'])->name('cancelled');
    Route::get('/error/{donation}', [PaymentStatusController::class, 'error'])->name('error');
});

/*
|--------------------------------------------------------------------------
| Campaign Routes
|--------------------------------------------------------------------------
*/
Route::prefix('campaigns')->name('campaigns.')->group(function () {
    // Add Campaign Routes
    Route::prefix('add')->name('add.')
        ->middleware('auth')
        ->controller(AddCampaignController::class)->group(function () {
        // Add Campaign Details
        Route::get('/details', 'addDetails')->name('details');
        Route::post('/details/store', 'storeDetails')->name('store.details');

        // Add Campaign Contact Info
        Route::get('/contact-info', 'addContactInfo')->name('contact.info');
        Route::post('/contact-info/store', 'storeContactInfo')->name('contact.info.store');

        // Add Campaign Photos & Videos
        Route::get('/photos-videos', 'addPhotosAndVideos')->name('photos.videos');
        Route::post('/photos-videos/store', 'storePhotosAndVideos')->name('photos.videos.store');

        // Add Supporting Documents
        Route::get('/supporting-documents', 'addSupportingDocuments')->name('supporting.documents');
        Route::post('/supporting-documents/store', 'storeSupportingDocuments')->name('supporting.documents.store');

        // Publish Added Campaign
        Route::get('/publish', 'publishCampaigns')->name('publish');
        Route::post('/publish/live', 'storePublishedCampaigns')->name('publish.live');
    });

    // Edit Campaign Routes
    Route::prefix('edit')->name('edit.')
        ->middleware(['auth', 'can:edit-campaign,campaign'])
        ->controller(EditCampaignController::class)->group(function () {
        // Edit Campaign Details
        Route::get('/details/{campaign}', 'editDetails')->name('details');
        Route::post('/store/details/{campaign}', 'storeDetails')->name('details.store');

        // Edit Campaign Contact Info
        Route::get('/contact-info/{campaign}', 'editContactInfo')->name('contact.info');
        Route::post('/contact-info/store/{campaign}', 'storeContactInfo')->name('contact.info.store');

        // Edit Campaign Photos & Videos
        Route::get('/photos-videos/{campaign}', 'editPhotosAndVideos')->name('photos.videos');
        Route::post('/photos-videos/store/{campaign}', 'storePhotosAndVideos')->name('photos.videos.store');

        // Edit Supporting Documents
        Route::get('/supporting-documents/{campaign}', 'editSupportingDocuments')->name('supporting.documents');
        Route::post('/supporting-documents/store/{campaign}', 'storeSupportingDocuments')->name('supporting.documents.store');

        // Publish Edited Campaign
        Route::get('/publish/{campaign}', 'editCampaigns')->name('publish');
        Route::post('/publish/live/{campaign}', 'storeEditedCampaigns')->name('publish.live');
    });

    // Campaign Listing
    Route::get('/', [CampaignListingController::class, 'index'])->name('index');
    Route::get('/show/{slug}', [CampaignShowController::class, 'showCampaigns'])->name('show');

    // Search
    Route::get('/search', CampaignSearchController::class)->name('campaigns.search');

    // Make donation
    Route::get('/donate/{slug}', [DonationController::class, 'donate'])->name('donate');
    Route::post('/donate/make-payment', [DonationController::class, 'makePayment'])->name('donate.make.payment');

    // Donation status
    Route::get('/status/success', [DonationController::class, 'success'])->name('status.success');
});

/*
|--------------------------------------------------------------------------
| Campaign Category Routes
|--------------------------------------------------------------------------
*/
Route::prefix('categories')->name('categories.')->group(function () {
    Route::get('/', [CampaignCategoriesController::class, 'categories'])->name('index');
    Route::get('/show/{slug}', [CampaignCategoriesController::class, 'showCategories'])->name('show');
});

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
*/
Route::prefix('user')->name('user.')
    ->middleware(['auth', 'can:access-user-dashboard'])
    ->controller(UserDashboardController::class)->group(function () {
    Route::get('/dashboard', 'index')->name('dashboard');

    // Account Profile
    Route::get('/profile', 'profilePage')->name('profile');
    Route::patch('/profile', 'updateProfile')->name('profile.update');

    Route::get('/profile/security', 'securityPage')->name('profile.security');

    // Profile Picture
    Route::post('/picture/update', 'updateProfilePicture')->name('picture.update');
    Route::delete('/picture/remove', 'removeProfilePicture')->name('picture.remove');

    // Password
    Route::patch('/password/update', 'updatePassword')->name('password.update');

    // Notification Settings
    Route::get('/profile/notification-settings', 'notificationSettings')->name('notification.settings');
    Route::post('/profile/notifications', 'updateNotifications')->name('update.notifications');

    // User Campaigns
    Route::prefix('campaigns')->name('campaigns.')->group(function () {
        // Campaign Routes
        Route::controller(UserCampaignController::class)->group(function () {
            // Show Campaigns
            Route::get('/', 'index')->name('index');
            Route::get('/archived', 'archived')->name('archived');

            // Mark Campaign As Complete
            Route::post('/complete', 'update')->name('complete');

            // Soft Delete Campaigns
            Route::post('/archive', 'archive')->name('archive');
            Route::post('/restore', 'restore')->name('restore');

            // Permanently Delete Campaigns
            Route::delete('/destroy', 'destroy')->name('destroy');
        });

        // Rewards
        Route::prefix('rewards')->name('rewards.')
            ->controller(UserRewardController::class)->group(function () {
            Route::get('/{campaign}', 'index')->name('index');

            // Add Reward
            Route::get('/create/{campaign}', 'create')->name('add');
            Route::post('/store/{campaign}', 'store')->name('store');

            // Edit Reward
            Route::get('/edit/{reward}', 'edit')->name('edit');
            Route::post('/update/{reward}', 'update')->name('update');

            // Delete Reward
            Route::delete('/delete/campaigns/{campaign}/rewards/{reward}', 'destroy')->name('delete');
        });

        // Updates
        Route::prefix('updates')->name('updates.')
            ->controller(UserUpdateController::class)->group(function () {
            Route::get('/{campaign}', 'index')->name('index');

            // Add Update
            Route::get('/create/{campaign}', 'create')->name('add');
            Route::post('/store/{campaign}', 'store')->name('store');

            // Edit Update
            Route::get('/edit/{update}', 'edit')->name('edit');
            Route::post('/update/{update}', 'update')->name('modify');

            // Delete Update
            Route::delete('/delete/campaigns/{campaign}/updates/{update}', 'destroy')->name('delete');
        });

        // Comments
        Route::prefix('comments')->name('comments.')
            ->controller(UserCommentController::class)->group(function () {
            Route::get('/{campaign}', 'index')->name('index');

            // Delete Comment
            Route::delete('/delete/campaigns/{campaign}/comments/{comment}', 'destroy')->name('delete');
        });
    });

    // Donations & Withdrawals
    Route::prefix('payments')->name('payments.')
        ->controller(\App\Http\Controllers\User\DonationsAndWithdrawalsController::class)->group(function () {
        // Donations
        Route::get('/donations', 'index')->name('donations');

        // Withdrawals
        Route::get('/withdrawals', 'withdrawals')->name('withdrawals');
        Route::post('/request/withdrawal', 'requestWithdrawal')->name('request.withdrawal');

        // Resolve & Add Account
        Route::post('/resolve/account', 'resolveAccount')
            ->name('resolve.account')
            ->middleware('throttle:10,1');
        Route::post('/add/account', 'addAccount')->name('add.account');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')
    ->middleware(['auth', 'can:access-admin-dashboard'])
    ->controller(AdminDashboardController::class)->group(function () {
    Route::get('/dashboard', 'index')->name('dashboard');

    // Account Profile
    Route::get('/profile', 'profilePage')->name('profile');
    Route::patch('/profile', 'updateProfile')->name('profile.update');

    Route::get('/profile/security', 'securityPage')->name('profile.security');

    // Profile Picture
    Route::post('/picture/update', 'updateProfilePicture')->name('picture.update');
    Route::delete('/picture/remove', 'removeProfilePicture')->name('picture.remove');

    // Password
    Route::patch('/password/update', 'updatePassword')->name('password.update');

    // Test Email
    Route::get('/profile/test-email', 'testEmail')->name('test.email');
    Route::post('/profile/send/test-email', 'sendTest')->name('send.test.email');

    // Notifications Settings
    Route::post('/profile/notifications', 'updateNotifications')->name('update.notifications');

    // Admin Campaigns
    Route::prefix('campaigns')->name('campaigns.')->group(function () {
        // Campaign Routes
        Route::controller(CampaignController::class)->group(function () {
            // Show Campaigns
            Route::get('/', 'index')->name('index');
            Route::get('/archived', 'archived')->name('archived');

            // Mark Campaign As Complete
            Route::post('/complete', 'update')->name('complete');

            // Soft Delete Campaigns
            Route::post('/archive', 'archive')->name('archive');
            Route::post('/restore', 'restore')->name('restore');

            // Permanently Delete Campaigns
            Route::delete('/destroy', 'destroy')->name('destroy');
        });

        // Rewards
        Route::prefix('rewards')->name('rewards.')
            ->controller(RewardController::class)->group(function () {
            Route::get('/{campaign}', 'index')->name('index');

            // Add Reward
            Route::get('/create/{campaign}', 'create')->name('add');
            Route::post('/store/{campaign}', 'store')->name('store');

            // Edit Reward
            Route::get('/edit/{reward}', 'edit')->name('edit');
            Route::post('/update/{reward}', 'update')->name('update');

            // Delete Reward
            Route::delete('/delete/campaigns/{campaign}/rewards/{reward}', 'destroy')->name('delete');
        });

        // Updates
        Route::prefix('updates')->name('updates.')
            ->controller(UpdateController::class)->group(function () {
            Route::get('/{campaign}', 'index')->name('index');

            // Add Update
            Route::get('/create/{campaign}', 'create')->name('add');
            Route::post('/store/{campaign}', 'store')->name('store');

            // Edit Update
            Route::get('/edit/{update}', 'edit')->name('edit');
            Route::post('/update/{update}', 'update')->name('modify');

            // Delete Update
            Route::delete('/delete/campaigns/{campaign}/updates/{update}', 'destroy')->name('delete');
        });

        // Comments
        Route::prefix('comments')->name('comments.')
            ->controller(CommentController::class)->group(function () {
            Route::get('/{campaign}', 'index')->name('index');

            // Delete Comment
            Route::delete('/delete/campaigns/{campaign}/comments/{comment}', 'destroy')->name('delete');
        });
    });

    // Campaign Categories
    Route::prefix('categories')->name('categories.')
        ->controller(CategoryController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/archived', 'archived')->name('archived');

        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');

        Route::get('/show/{slug}', 'show')->name('show');
        Route::get('/show/archived/{slug}', 'showArchived')->name('show.archived');

        Route::get('/edit/{category}', 'edit')->name('edit');
        Route::post('/update/{category}', 'update')->name('update');

        Route::post('/archive', 'archive')->name('archive');
        Route::post('/restore', 'restore')->name('restore');

        Route::delete('/destroy', 'destroy')->name('destroy');
    });

    // Donations & Withdrawals
    Route::prefix('payments')->name('payments.')
        ->controller(DonationsAndWithdrawalsController::class)->group(function () {
        // Donations
        Route::get('/donations', 'index')->name('donations');

        // Withdrawals
        Route::get('/withdrawals', 'withdrawals')->name('withdrawals');
        Route::post('/withdrawals/approve/{withdrawal}', 'approveWithdrawal')->name('withdrawal.approve');
        Route::post('/withdrawals/reject/{withdrawal}', 'rejectWithdrawal')->name('withdrawal.reject');
    });

    // Users
    Route::prefix('users')->name('users.')
        ->controller(UsersController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/banned', 'banned')->name('banned');
        Route::get('/profile/{user}', 'profile')->name('view.profile');

        // User Campaigns
        Route::get('/campaigns', 'index')->name('campaigns');
        Route::get('/approve/{campaign}', 'approveCampaign')->name('campaign.approve');
        Route::post('/reject/{campaign}', 'rejectCampaign')->name('campaign.reject');
    });
});
