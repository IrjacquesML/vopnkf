<?php

use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LessonController as AdminLessonController;
use App\Http\Controllers\Admin\PrayerController as AdminPrayerController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\TranslateController;
use App\Http\Controllers\Api\VerseController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\PrayerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/connexion', [LoginController::class, 'show'])->name('connexion');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/inscription', [RegisterController::class, 'show'])->name('inscription');
Route::post('/register', [RegisterController::class, 'store'])->name('register');

/*
|--------------------------------------------------------------------------
| API (web middleware for CSRF-friendly fetch from same origin)
|--------------------------------------------------------------------------
*/
Route::get('/api/versets', [VerseController::class, 'show'])->name('api.versets');
Route::middleware('auth')->group(function () {
    Route::post('/api/traduire', [TranslateController::class, 'store'])->name('api.traduire');
});

/*
|--------------------------------------------------------------------------
| Authenticated user area
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [LessonController::class, 'dashboard'])->name('dashboard');
    Route::get('/lecons/{id}', [LessonController::class, 'show'])->name('lessons.show');
    Route::post('/lecons/{id}/terminer', [LessonController::class, 'complete'])->name('lessons.complete');

    Route::post('/quiz', [QuizController::class, 'store'])->name('quiz.store');
    Route::get('/lecons/{lecon}/resultats', [QuizController::class, 'results'])->name('lessons.results');

    Route::get('/historique', [HistoryController::class, 'index'])->name('history.index');

    Route::get('/prieres', [PrayerController::class, 'index'])->name('prayers.index');
    Route::get('/prieres/nouvelle', [PrayerController::class, 'create'])->name('prayers.create');
    Route::post('/prieres', [PrayerController::class, 'store'])->name('prayers.store');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profil/photo', [ProfileController::class, 'updatePhoto'])->name('profile.updatePhoto');
});

/*
|--------------------------------------------------------------------------
| Admin auth
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'show'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login']);
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/utilisateurs', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/utilisateurs/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::delete('/utilisateurs/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [AdminCategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{categorie}/edit', [AdminCategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{categorie}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{categorie}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/lecons', [AdminLessonController::class, 'index'])->name('lessons.index');
        Route::get('/lecons/create', [AdminLessonController::class, 'create'])->name('lessons.create');
        Route::get('/lecons/import', [AdminLessonController::class, 'importForm'])->name('lessons.import');
        Route::post('/lecons/import', [AdminLessonController::class, 'importStore'])->name('lessons.import.store');
        Route::get('/lecons/import-questionnaire', [AdminLessonController::class, 'importQuestionnaireForm'])->name('lessons.import.questionnaire');
        Route::post('/lecons/import-questionnaire', [AdminLessonController::class, 'importQuestionnaireStore'])->name('lessons.import.questionnaire.store');
        Route::post('/lecons', [AdminLessonController::class, 'store'])->name('lessons.store');
        Route::get('/lecons/{lesson}', [AdminLessonController::class, 'show'])->name('lessons.show');
        Route::get('/lecons/{lesson}/edit', [AdminLessonController::class, 'edit'])->name('lessons.edit');
        Route::put('/lecons/{lesson}', [AdminLessonController::class, 'update'])->name('lessons.update');
        Route::delete('/lecons/{lesson}', [AdminLessonController::class, 'destroy'])->name('lessons.destroy');

        Route::post('/lecons/{lesson}/questions', [AdminLessonController::class, 'storeQuestion'])->name('lessons.questions.store');
        Route::put('/lecons/{lesson}/questions/{question}', [AdminLessonController::class, 'updateQuestion'])->name('lessons.questions.update');
        Route::delete('/lecons/{lesson}/questions/{question}', [AdminLessonController::class, 'destroyQuestion'])->name('lessons.questions.destroy');

        Route::post('/lecons/{lesson}/questions/{question}/options', [AdminLessonController::class, 'storeOption'])->name('lessons.questions.options.store');
        Route::put('/lecons/{lesson}/questions/{question}/options/{option}', [AdminLessonController::class, 'updateOption'])->name('lessons.questions.options.update');
        Route::delete('/lecons/{lesson}/questions/{question}/options/{option}', [AdminLessonController::class, 'destroyOption'])->name('lessons.questions.options.destroy');

        Route::get('/prieres', [AdminPrayerController::class, 'index'])->name('prayers.index');
        Route::get('/prieres/{prayer}', [AdminPrayerController::class, 'show'])->name('prayers.show');
        Route::patch('/prieres/{prayer}/statut', [AdminPrayerController::class, 'updateStatus'])->name('prayers.updateStatus');
        Route::delete('/prieres/{prayer}', [AdminPrayerController::class, 'destroy'])->name('prayers.destroy');

        Route::get('/rapports/statistiques', [AdminReportController::class, 'statistiques'])->name('reports.statistiques');
        Route::get('/rapports/palmares', [AdminReportController::class, 'palmares'])->name('reports.palmares');
        Route::get('/rapports/certificats', [AdminReportController::class, 'certificat'])->name('reports.certificat');

        Route::get('/parametres/bible', [AdminSettingsController::class, 'bibleApi'])->name('settings.bible');
        Route::get('/parametres/certificat', [AdminSettingsController::class, 'certificate'])->name('settings.certificate');
        Route::put('/parametres/certificat', [AdminSettingsController::class, 'updateCertificate'])->name('settings.certificate.update');
    });
});
