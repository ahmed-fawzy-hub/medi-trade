<?php
use App\Http\Controllers\BannerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SeoTagsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PrivacyPolicyeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\VideoController;

// ✅ Routes عامة (Website) — بدون تحقق
// ✅ اختبار الاتصال
Route::get('/test', fn () => response()->json(['message' => 'API working']));

// ✅ Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ✅ Website Routes
Route::prefix('website')->group(function () {
    Route::get('/services', [ServiceController::class, 'publicWebsiteServices']);
    Route::get('/services/{slug}', [ServiceController::class, 'showActiveBySlug']);
    
    Route::get('/blogs', [BlogController::class, 'availableBlogs']);
    Route::get('/blogs/{slug}', [BlogController::class, 'showActiveBySlug']);
    
    Route::get('/partners/category/{slug}/active', [PartnerController::class, 'getActiveByCategory']);

    Route::get('/images', [MediaController::class, 'publicImages']);
    Route::get('/videos', [MediaController::class, 'publicVideos']);

    Route::post('/contact', [ContactController::class, 'store']);

Route::get('/public-about-us', [AboutUsController::class, 'showWithServices']); // Public website
    Route::get('/privacy-policy', [PrivacyPolicyeController::class, 'index']);

    Route::get('/home', [HomeController::class, 'index']);
});

// ✅ Dashboard Routes
Route::prefix('dashboard')->middleware('auth:api')->group(function () {
    // Services
    Route::get('/services', [ServiceController::class, 'index']);
    Route::post('/services', [ServiceController::class, 'store']);
    Route::post('/services/{id}/update', [ServiceController::class, 'update']);
    Route::post('/services/{id}/toggle', [ServiceController::class, 'toggleStatus']);
        Route::get('services/{id}', [ServiceController::class, 'showById']);


    // Blogs
    Route::prefix('blogs')->group(function () {
        Route::get('/', [BlogController::class, 'index']);
        Route::post('/', [BlogController::class, 'store']);
        Route::post('/{id}/update', [BlogController::class, 'update']);
        Route::post('/{id}/toggle', [BlogController::class, 'toggleStatus']);
        Route::get('/show/{id}', [BlogController::class, 'showById']);

    });

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::post('/categories/{category}/update', [CategoryController::class, 'update']);
    Route::post('/categories/{category}/toggle', [CategoryController::class, 'toggleActive']);
        Route::get('/categories/{id}', [CategoryController::class, 'show']);            // عرض كاتيجوري واحدة


    // Contacts
    Route::get('/contact', [ContactController::class, 'index']);
    Route::get('/contact/{id}', [ContactController::class, 'show']);
    Route::post('/contact/{id}/seen', [ContactController::class, 'markAsSeen']);


Route::prefix('partners')->group(function () {
    
    // إنشاء شريك جديد
    Route::post('/', [PartnerController::class, 'store']);

    // تحديث شريك
    Route::post('/{partner}/update', [PartnerController::class, 'update']);

    // تفعيل/إلغاء تفعيل شريك
    Route::post('/{partner}/toggle', [PartnerController::class, 'toggleActive']);

    // جلب كل الشركاء حسب التصنيف
    Route::get('/category/{categoryId}', [PartnerController::class, 'getAllByCategory']);

    // جلب الشركاء المفعّلين فقط حسب التصنيف واللغة

});


    // Media
    Route::prefix('media')->group(function () {
        Route::get('/', [MediaController::class, 'index']);
        Route::post('/', [MediaController::class, 'store']);
        Route::post('/{id}/update', [MediaController::class, 'update']);
        Route::post('/{id}/toggle', [MediaController::class, 'toggleVisibility']);
    });

    // Sliders
    Route::prefix('sliders')->group(function () {
        Route::get('/', [\App\Http\Controllers\SliderController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\SliderController::class, 'store']);
        Route::post('/{id}/update', [\App\Http\Controllers\SliderController::class, 'update']);
        Route::post('/{id}/toggle', [\App\Http\Controllers\SliderController::class, 'toggleStatus']);
    });
Route::prefix('seo-tag')->group(function () {
    Route::get('/', [SeoTagsController::class, 'index']);          // Get all seo tags
    Route::post('/', [SeoTagsController::class, 'store']);          // Create seo tag
    Route::get('{id}', [SeoTagsController::class, 'show']);         // Get one seo tag
    Route::post('{id}', [SeoTagsController::class, 'update']);      // Update seo tag
});

Route::prefix('banners')->group(function () {
    Route::get('/', [BannerController::class, 'index']); // ✅ عرض كل البانرات
    Route::post('/', [BannerController::class, 'store']); // إنشاء بانر
    Route::get('{page}', [BannerController::class, 'show']); // عرض بانر حسب الصفحة
    Route::post('{page}', [BannerController::class, 'update']); // تحديث بانر
});

    // Contact Info
    Route::get('/contact-info', [\App\Http\Controllers\ContactInfoController::class, 'show']);
    Route::post('/contact-info', [\App\Http\Controllers\ContactInfoController::class, 'update']);

    // Privacy Policy
    Route::post('/privacy-policy', [PrivacyPolicyeController::class, 'update']);
    Route::get('/about-us', [AboutUsController::class, 'show']); // Dashboard
Route::post('/about-us/update', [AboutUsController::class, 'update']);

});


