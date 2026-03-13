<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\TermController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\StoreSettingsController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\PhotoUploadServiceController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ShippingCompanyController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\ReturnController as AdminReturnController;
use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\ProductBulkExcelController;
use App\Http\Controllers\Admin\HomeSectionController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\ProductCommentController;
use App\Http\Controllers\Admin\TrafficController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\Admin\LiveVisitorController;
use App\Http\Controllers\Admin\RegionSalesReportController;
use App\Http\Controllers\Admin\LogController;

Route::get('login', [LoginController::class, 'showLoginForm'])->name('auth.login');
Route::post('login', [LoginController::class, 'login'])->name('login.submit');


Route::middleware(['admin.auth', 'admin.permission'])->group(function () {
	Route::get('/', [DashboardController::class, 'index'])->name('home');
	Route::post('logout', [LoginController::class, 'logout'])->name('logout');
	Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
	Route::get('dashboard/metrics', [DashboardController::class, 'metrics'])->name('dashboard.metrics');
	Route::get('deny', [LoginController::class, 'deny'])->name('auth.deny');

	// Settings
	Route::get('site-settings', [SiteSettingsController::class, 'index'])->name('site-settings.index');
	Route::put('site-settings', [SiteSettingsController::class, 'update'])->name('site-settings.update');
	Route::get('store-settings', [StoreSettingsController::class, 'index'])->name('store-settings.index');
	Route::put('store-settings', [StoreSettingsController::class, 'update'])->name('store-settings.update');
	Route::post('banks', [BankController::class, 'store'])->name('banks.store');
	Route::put('banks/{bank}', [BankController::class, 'update'])->name('banks.update');
	Route::delete('banks/{bank}', [BankController::class, 'destroy'])->name('banks.destroy');
	Route::patch('banks/{bank}/status', [BankController::class, 'toggleStatus'])->name('banks.status');
	Route::get('shipping-companies', [ShippingCompanyController::class, 'index'])->name('shipping-companies.index');
	Route::post('shipping-companies', [ShippingCompanyController::class, 'store'])->name('shipping-companies.store');
	Route::put('shipping-companies/{id}', [ShippingCompanyController::class, 'update'])->name('shipping-companies.update');
	Route::delete('shipping-companies/{id}', [ShippingCompanyController::class, 'destroy'])->name('shipping-companies.destroy');
	Route::get('shipping-companies/{id}/orders', [ShippingCompanyController::class, 'orders'])->name('shipping-companies.orders');

	// Products
	Route::get('products', [ProductController::class, 'index'])->name('products.index');
	Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
	Route::post('products', [ProductController::class, 'store'])->name('products.store');
	Route::get('products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
	Route::put('products/{id}', [ProductController::class, 'update'])->name('products.update');
	Route::delete('products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

	// Product comments
	Route::get('product-comments', [ProductCommentController::class, 'index'])->name('product-comments.index');
	Route::get('product-comments/pending', [ProductCommentController::class, 'pending'])->name('product-comments.pending');
	Route::get('product-comments/approved', [ProductCommentController::class, 'approved'])->name('product-comments.approved');
	Route::get('product-comments/rejected', [ProductCommentController::class, 'rejected'])->name('product-comments.rejected');
	Route::post('product-comments/bulk-action', [ProductCommentController::class, 'bulkAction'])->name('product-comments.bulk-action');
	Route::get('product-comments/{id}', [ProductCommentController::class, 'show'])->name('product-comments.show');
	Route::put('product-comments/{id}', [ProductCommentController::class, 'update'])->name('product-comments.update');
	Route::delete('product-comments/{id}', [ProductCommentController::class, 'destroy'])->name('product-comments.destroy');

	// Products bulk excel
	Route::get('products/bulk-excel', [ProductBulkExcelController::class, 'index'])->name('products.bulk-excel');
	Route::get('products/bulk-excel/download', [ProductBulkExcelController::class, 'download'])->name('products.bulk-excel.download');
	Route::post('products/bulk-excel/upload', [ProductBulkExcelController::class, 'upload'])->name('products.bulk-excel.upload');
	Route::post('products/bulk-excel/validate', [ProductBulkExcelController::class, 'validateImport'])->name('products.bulk-excel.validate');
	Route::post('products/bulk-excel/restore/{backup}', [ProductBulkExcelController::class, 'restore'])->name('products.bulk-excel.restore');
	Route::post('products/bulk-excel/verify-password', [ProductBulkExcelController::class, 'verifyPassword'])->name('products.bulk-excel.verify-password');
	Route::get('/products/bulk-excel/check-status', [ProductBulkExcelController::class, 'checkStatus'])->name('products.bulk-excel.check-status');
	Route::post('/products/bulk-excel/cancel', [ProductBulkExcelController::class, 'cancelImport'])->name('products.bulk-excel.cancel');
	Route::post('products/bulk-excel/process-chunk/{job}', [ProductBulkExcelController::class, 'processChunk'])->name('products.bulk-excel.process-chunk');

	// Categories
	Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
	Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
	Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
	Route::get('categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
	Route::put('categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
	Route::delete('categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
	Route::get('categories/{id}/products', [ProductController::class, 'byCategory'])->name('categories.products');
	Route::delete('categories/{id}/products', [CategoryController::class, 'destroyProducts'])->name('categories.products.clear');
	Route::delete('categories/{category}/products/{product}', [CategoryController::class, 'removeProduct'])->name('categories.products.destroy');

	// Attributes & Terms
	Route::get('attributes', [AttributeController::class, 'index'])->name('attributes.index');
	Route::post('attributes', [AttributeController::class, 'store'])->name('attributes.store');
	Route::put('attributes/{id}', [AttributeController::class, 'update'])->name('attributes.update');
	Route::delete('attributes/{id}', [AttributeController::class, 'destroy'])->name('attributes.destroy');
	Route::get('terms', [TermController::class, 'index'])->name('terms.index');
	Route::post('terms', [TermController::class, 'store'])->name('terms.store');
	Route::put('terms/{id}', [TermController::class, 'update'])->name('terms.update');
	Route::delete('terms/{id}', [TermController::class, 'destroy'])->name('terms.destroy');

	// Slider
	Route::get('slider', [SliderController::class, 'index'])->name('slider.index');
	Route::post('slider', [SliderController::class, 'store'])->name('slider.store');
	Route::put('slider/{id}', [SliderController::class, 'update'])->name('slider.update');
	Route::delete('slider/{id}', [SliderController::class, 'destroy'])->name('slider.destroy');

	// Blog
	Route::get('blog', [BlogController::class, 'index'])->name('blog.index');
	Route::post('blog', [BlogController::class, 'store'])->name('blog.store');
	Route::put('blog/{id}', [BlogController::class, 'update'])->name('blog.update');
	Route::delete('blog/{id}', [BlogController::class, 'destroy'])->name('blog.destroy');

	// Menu
	Route::get('menu', [MenuController::class, 'index'])->name('menu.index');
	Route::post('menu', [MenuController::class, 'store'])->name('menu.store');
	Route::put('menu/{id}', [MenuController::class, 'update'])->name('menu.update');
	Route::delete('menu/{id}', [MenuController::class, 'destroy'])->name('menu.destroy');

	// Stock
	Route::get('stock/low', [StockController::class, 'low'])->name('stock.low');
	Route::get('stock/out', [StockController::class, 'out'])->name('stock.out');

	// Orders
	Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
	Route::get('orders/new', [OrderController::class, 'new'])->name('orders.new');
	Route::get('orders/pending', [OrderController::class, 'pending'])->name('orders.pending');
	Route::get('orders/canceled', [OrderController::class, 'canceled'])->name('orders.canceled');
	Route::get('orders/completed', [OrderController::class, 'completed'])->name('orders.completed');
	Route::get('orders/customer/{customerId}', [OrderController::class, 'customerOrders'])->name('orders.customer');
	Route::get('orders/{id}/edit', [OrderController::class, 'edit'])->name('orders.edit');
	Route::put('orders/{id}', [OrderController::class, 'update'])->name('orders.update');
	Route::post('orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
	Route::get('orders/{id}/payment-status-wire', [OrderController::class, 'paymentStatusWire'])->name('orders.payment-status-wire');
	Route::get('orders/{id}/report-pdf', [OrderController::class, 'reportOrderToPdf'])->name('orders.report-pdf');
	Route::delete('orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');
	Route::get('orders/{id}', [OrderController::class, 'show'])->name('orders.show');

	// Returns
	Route::get('returns', [AdminReturnController::class, 'index'])->name('returns.index');
	Route::get('returns/pending', [AdminReturnController::class, 'pending'])->name('returns.pending');
	Route::get('returns/processed', [AdminReturnController::class, 'processed'])->name('returns.processed');
	Route::get('returns/status/{status}', [AdminReturnController::class, 'status'])->name('returns.status');
	Route::get('returns/{return}', [AdminReturnController::class, 'show'])->name('returns.show');
	Route::patch('returns/{return}', [AdminReturnController::class, 'update'])->name('returns.update');

	// Customers
	Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
	Route::get('customers/{id}', [CustomerController::class, 'show'])->name('customers.show');
	Route::put('customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
	Route::post('customers/{id}/verification', [CustomerController::class, 'sendVerification'])->name('customers.verification.send');

	// Notifications
	Route::prefix('notifications')->name('notifications.')->group(function () {
		Route::get('/web', [AdminNotificationController::class, 'webIndex'])->name('web.index');
		Route::get('/web/history', [AdminNotificationController::class, 'webHistory'])->name('web.history');
		Route::post('/web/send', [AdminNotificationController::class, 'sendWebNotification'])->name('web.send');
		Route::post('/web/clear-all', [AdminNotificationController::class, 'webClearAll'])->name('web.clear-all');
		Route::post('/web/delete-selected', [AdminNotificationController::class, 'webDeleteSelected'])->name('web.delete-selected');
	});

	// Announcements
	Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
	Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
	Route::post('announcements/{id}', [AnnouncementController::class, 'update'])->name('announcements.update');
	Route::delete('announcements/{id}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

	// Campaigns
	Route::get('campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
	Route::post('campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
	Route::put('campaigns/{id}', [CampaignController::class, 'update'])->name('campaigns.update');
	Route::delete('campaigns/{id}', [CampaignController::class, 'destroy'])->name('campaigns.destroy');

	// Photo upload service
	Route::get('photos/list', [PhotoUploadServiceController::class, 'list'])->name('photos.list');
	Route::post('photos/upload', [PhotoUploadServiceController::class, 'upload'])->name('photos.upload');
	Route::post('photos/order', [PhotoUploadServiceController::class, 'changeOrder'])->name('photos.order');
	Route::delete('photos/delete', [PhotoUploadServiceController::class, 'delete'])->name('photos.delete');

	// Account
	Route::get('account', [AccountController::class, 'index'])->name('account.index');
	Route::put('account', [AccountController::class, 'update'])->name('account.update');

	// Reports (menu parent)
	Route::redirect('reports', 'traffic')->name('reports.index');

	// Traffic
	Route::get('traffic', [TrafficController::class, 'index'])->name('traffic.index');

	// Sales Reports
	Route::get('sales-reports', [SalesReportController::class, 'index'])->name('sales-reports.index');
	Route::get('region-reports', [RegionSalesReportController::class, 'index'])->name('region-reports.index');

	// Theme
	Route::prefix('theme')->name('theme.')->group(function () {
		Route::get('/selection', [ThemeController::class, 'selection'])->name('selection');
		Route::post('/selection', [ThemeController::class, 'updateSelection'])->name('updateSelection');
		Route::get('/colors', [ThemeController::class, 'index'])->name('index');
		Route::put('/colors', [ThemeController::class, 'update'])->name('update');
	});

	// Home Sections
	Route::get('home-sections', [HomeSectionController::class, 'index'])->name('home-sections.index');
	Route::get('home-sections/get-items', [HomeSectionController::class, 'getItems'])->name('home-sections.get-items');
	Route::get('home-sections/create', [HomeSectionController::class, 'create'])->name('home-sections.create');
	Route::post('home-sections', [HomeSectionController::class, 'store'])->name('home-sections.store');
	Route::get('home-sections/{id}/edit', [HomeSectionController::class, 'edit'])->name('home-sections.edit');
	Route::put('home-sections/{id}', [HomeSectionController::class, 'update'])->name('home-sections.update');
	Route::delete('home-sections/{id}', [HomeSectionController::class, 'destroy'])->name('home-sections.destroy');
	Route::post('home-sections/sort', [HomeSectionController::class, 'sort'])->name('home-sections.sort');
	Route::patch('home-sections/{id}/status', [HomeSectionController::class, 'toggleStatus'])->name('home-sections.status');

	// Promotions
	Route::get('promotions', [PromotionController::class, 'index'])->name('promotions.index');
	Route::get('promotions/create', [PromotionController::class, 'create'])->name('promotions.create');
	Route::post('promotions', [PromotionController::class, 'store'])->name('promotions.store');
	Route::get('promotions/get-items', [PromotionController::class, 'getItems'])->name('promotions.get-items');
	Route::get('promotions/{id}/edit', [PromotionController::class, 'edit'])->name('promotions.edit');
	Route::put('promotions/{id}', [PromotionController::class, 'update'])->name('promotions.update');
	Route::delete('promotions/{id}', [PromotionController::class, 'destroy'])->name('promotions.destroy');
	Route::patch('promotions/{id}/status', [PromotionController::class, 'toggleStatus'])->name('promotions.status');
	Route::patch('promotions/{id}/public', [PromotionController::class, 'togglePublic'])->name('promotions.public');

	// Users & permissions (super admin yönetimi)
	Route::get('users', [UserController::class, 'index'])->name('users.index');
	Route::get('users/create', [UserController::class, 'create'])->name('users.create');
	Route::post('users', [UserController::class, 'store'])->name('users.store');
	Route::get('users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
	Route::put('users/{id}', [UserController::class, 'update'])->name('users.update');
	Route::delete('users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

	// Logs
	Route::get('logs/admin', [LogController::class, 'adminLogs'])->name('logs.admin');
	// Route::get('logs/admin/export', [LogController::class, 'exportAdminLogs'])->name('logs.admin.export');
	Route::get('logs/customer', [LogController::class, 'customerLogs'])->name('logs.customer');
	// Route::get('logs/customer/export', [LogController::class, 'exportCustomerLogs'])->name('logs.customer.export');

	// Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
	// Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
});