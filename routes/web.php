<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SetFamilyLocale;
use App\Http\Middleware\EnsureFamilyPortalAccess;
use App\Http\Controllers\Family\AuthController;
use App\Http\Controllers\Family\DashboardController;
use App\Http\Controllers\Family\FamilyGroupController;
use App\Http\Controllers\Family\FamilyPersonController;
use App\Http\Controllers\Family\FamilyTreeController;
use App\Http\Controllers\Family\FamilyAgendaController;
use App\Http\Controllers\Family\FamilyPhotoAlbumController;


// Portfolio
Route::redirect('/', '/en');

Route::get('/{locale}', function (string $locale) {
    return view('home');
})
    ->whereIn('locale', ['en', 'id'])
    ->middleware(SetLocale::class)
    ->name('home');

// ATCS Subang Case Study
Route::get('/{locale}/projects/atcs-subang', function (string $locale) {
    return view('projects.atcs-subang');
})
    ->whereIn('locale', ['en', 'id'])
    ->middleware(SetLocale::class)
    ->name('projects.atcs-subang');

/*
|--------------------------------------------------------------------------
| FAMILY PORTAL
|--------------------------------------------------------------------------
*/

Route::prefix('family')
    ->name('family.')
    ->middleware(SetFamilyLocale::class)
    ->group(function () {

        // Guest routes
        Route::get('/login', [AuthController::class, 'showLogin'])
            ->name('login');

        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1')
            ->name('login.submit');

        // Authenticated and authorized routes
        Route::middleware([
            'auth:family',
            EnsureFamilyPortalAccess::class,
        ])->group(function () {

            // Dashboard
            Route::get('/dashboard', [DashboardController::class, 'index'])
                ->name('dashboard');

            // Family Group creation
            Route::get('/groups/create', [FamilyGroupController::class, 'create'])
                ->name('groups.create');

            Route::post('/groups', [FamilyGroupController::class, 'store'])
                ->name('groups.store');

            // Family Group detail
            Route::get('/groups/{groupId}', [FamilyGroupController::class, 'show'])
                ->whereNumber('groupId')
                ->name('groups.show');

            // Family Group settings
            Route::put('/groups/{groupId}/type', [FamilyGroupController::class, 'changeType'])
                ->whereNumber('groupId')
                ->name('groups.type');

            Route::put('/groups/{groupId}/status', [FamilyGroupController::class, 'changeStatus'])
                ->whereNumber('groupId')
                ->name('groups.status');

            Route::post('/groups/{groupId}/archive', [FamilyGroupController::class, 'archive'])
                ->whereNumber('groupId')
                ->name('groups.archive');

            Route::post('/groups/{groupId}/restore', [FamilyGroupController::class, 'restore'])
                ->whereNumber('groupId')
                ->name('groups.restore');

            // Membership management
            Route::get('/groups/{groupId}/members/create', [FamilyGroupController::class, 'createMember'])
                ->whereNumber('groupId')
                ->name('groups.members.create');

            Route::post('/groups/{groupId}/members', [FamilyGroupController::class, 'storeMember'])
                ->whereNumber('groupId')
                ->name('groups.members.store');

            Route::patch('/groups/{groupId}/members/{membershipId}/status', [FamilyGroupController::class, 'changeMemberStatus'])
                ->whereNumber('groupId')
                ->whereNumber('membershipId')
                ->name('groups.members.status');

            // Family Group edit
            Route::get('/groups/{groupId}/edit', [FamilyGroupController::class, 'edit'])
                ->whereNumber('groupId')
                ->name('groups.edit');

            Route::put('/groups/{groupId}', [FamilyGroupController::class, 'update'])
                ->whereNumber('groupId')
                ->name('groups.update');

            // Family Group people management
            Route::get('/groups/{groupId}/people', [FamilyPersonController::class, 'index'])
                ->whereNumber('groupId')
                ->name('groups.people.index');

            // Private Family Profile Photo
            Route::get(
                '/groups/{groupId}/people/{personId}/photo',
                [FamilyPersonController::class, 'photo']
            )
                ->whereNumber('groupId')
                ->whereNumber('personId')
                ->name('groups.people.photo');

            Route::get('/groups/{groupId}/people/{personId}', [FamilyPersonController::class, 'show'])
                ->whereNumber('groupId')
                ->whereNumber('personId')
                ->name('groups.people.show');

            // Family relationships
            Route::get('/groups/{groupId}/people/{personId}/relationships/create', [FamilyPersonController::class, 'createRelationship'])
                ->whereNumber('groupId')
                ->whereNumber('personId')
                ->name('groups.people.relationships.create');

                        // Edit Family Relationship
            Route::get('/groups/{groupId}/people/{personId}/relationships/{relationshipId}/edit', [FamilyPersonController::class, 'editRelationship'])
                ->whereNumber('groupId')
                ->whereNumber('personId')
                ->whereNumber('relationshipId')
                ->name('groups.people.relationships.edit');

            // Update Family Relationship
            Route::put('/groups/{groupId}/people/{personId}/relationships/{relationshipId}', [FamilyPersonController::class, 'updateRelationship'])
                ->whereNumber('groupId')
                ->whereNumber('personId')
                ->whereNumber('relationshipId')
                ->name('groups.people.relationships.update');

            Route::post('/groups/{groupId}/people/{personId}/relationships', [FamilyPersonController::class, 'storeRelationship'])
                ->whereNumber('groupId')
                ->whereNumber('personId')
                ->name('groups.people.relationships.store');

            Route::get('/groups/{groupId}/people/create', [FamilyPersonController::class, 'create'])
                ->whereNumber('groupId')
                ->name('groups.people.create');

            Route::post('/groups/{groupId}/people', [FamilyPersonController::class, 'store'])
                ->whereNumber('groupId')
                ->name('groups.people.store');

            Route::get('/groups/{groupId}/people/{personId}/edit', [FamilyPersonController::class, 'edit'])
                ->whereNumber('groupId')
                ->whereNumber('personId')
                ->name('groups.people.edit');

            Route::put('/groups/{groupId}/people/{personId}', [FamilyPersonController::class, 'update'])
                ->whereNumber('groupId')
                ->whereNumber('personId')
                ->name('groups.people.update');

            // Family Tree
            Route::get('/groups/{groupId}/tree', [FamilyTreeController::class, 'index'])
                ->whereNumber('groupId')
                ->name('groups.tree');

             // Family Agenda
            Route::get('/agendas', [FamilyAgendaController::class, 'index'])
                ->name('agendas.index');

            Route::get('/agendas/create', [FamilyAgendaController::class, 'create'])
                ->name('agendas.create');

            Route::post('/agendas', [FamilyAgendaController::class, 'store'])
                ->name('agendas.store');

            Route::get('/agendas/{agendaId}', [FamilyAgendaController::class, 'show'])
                ->whereNumber('agendaId')
                ->name('agendas.show');

            Route::get('/groups/{groupId}/agendas', [FamilyAgendaController::class, 'group'])
                ->whereNumber('groupId')
                ->name('groups.agendas.index');

            Route::get('/agendas/{agendaId}/edit', [FamilyAgendaController::class, 'edit'])
                ->whereNumber('agendaId')
                ->name('agendas.edit');

            Route::put('/agendas/{agendaId}', [FamilyAgendaController::class, 'update'])
                ->whereNumber('agendaId')
                ->name('agendas.update');

            Route::post('/agendas/{agendaId}/cancel', [FamilyAgendaController::class, 'cancel'])
                ->whereNumber('agendaId')
                ->name('agendas.cancel');

            Route::patch('/agendas/{agendaId}/respond', [FamilyAgendaController::class, 'respond'])
                ->whereNumber('agendaId')
                ->name('agendas.respond');

            // Family Photo Albums
            Route::prefix('photo-albums')->name('photo-albums.')->group(function () {

            // Foto Favorit Saya
            Route::get('/favorites', [
                FamilyPhotoAlbumController::class,
                'favorites'
            ])->name('favorites');


            // Sampah album foto
            Route::get('/trash', [
                FamilyPhotoAlbumController::class,
                'trash',
            ])->name('trash');

            // Kosongkan Sampah
            Route::delete('/trash/empty', [
                FamilyPhotoAlbumController::class,
                'emptyTrash',
            ])->name('trash.empty');

            // Restore album
            Route::post('/{albumId}/restore', [
                FamilyPhotoAlbumController::class,
                'restoreAlbum',
            ])->whereNumber('albumId')->name('restore');

            // Hapus permanen album
            Route::delete('/{albumId}/force-delete', [
                FamilyPhotoAlbumController::class,
                'forceDeleteAlbum',
            ])->whereNumber('albumId')->name('force-delete');

            // Restore foto
            Route::post('/{albumId}/photos/{photoId}/restore', [
                FamilyPhotoAlbumController::class,
                'restorePhoto',
            ])->whereNumber('albumId')
            ->whereNumber('photoId')
            ->name('photos.restore');

            // Hapus permanen foto
            Route::delete('/{albumId}/photos/{photoId}/force-delete', [
                FamilyPhotoAlbumController::class,
                'forceDeletePhoto',
            ])->whereNumber('albumId')
            ->whereNumber('photoId')
            ->name('photos.force-delete');


            Route::get('/', [FamilyPhotoAlbumController::class, 'index'])
                ->name('index');

            Route::get('/create', [FamilyPhotoAlbumController::class, 'create'])
                ->name('create');

            Route::post('/', [FamilyPhotoAlbumController::class, 'store'])
                ->name('store');
            Route::delete('/{albumId}', [FamilyPhotoAlbumController::class, 'destroy'])
                ->name('destroy');
           Route::get('/{albumId}', [FamilyPhotoAlbumController::class, 'show'])
            ->whereNumber('albumId')
            ->name('show');
            // Akses foto privat
            Route::get('/{albumId}/photos/{photoId}/{variant?}', [
                FamilyPhotoAlbumController::class,
                'photo',
            ])
                ->whereNumber('albumId')
                ->whereNumber('photoId')
                ->whereIn('variant', ['thumbnail', 'preview'])
                ->name('photos.show');
            Route::post('/{albumId}/photos', [FamilyPhotoAlbumController::class, 'upload'])
                ->name('photos.upload');

            // Hapus satu foto
            Route::delete('/{albumId}/photos/{photoId}', [
                FamilyPhotoAlbumController::class,
                'destroyPhoto'
            ])->whereNumber('albumId')
            ->whereNumber('photoId')
            ->name('photos.destroy');

            // Hapus beberapa foto sekaligus
            Route::delete('/{albumId}/photos', [
                FamilyPhotoAlbumController::class,
                'destroyPhotos'
            ])->whereNumber('albumId')
            ->name('photos.bulk-destroy');

            // Jadikan foto sebagai cover album
            Route::patch('/{albumId}/photos/{photoId}/cover', [
                FamilyPhotoAlbumController::class,
                'setCover'
            ])->whereNumber('albumId')
            ->whereNumber('photoId')
            ->name('photos.cover');

            Route::patch('/{albumId}/photos/{photoId}', [
                FamilyPhotoAlbumController::class,
                'updatePhoto'
            ])->whereNumber('albumId')
            ->whereNumber('photoId')
            ->name('photos.update');

            Route::post('/{albumId}/photos/{photoId}/favorite', [
            FamilyPhotoAlbumController::class,
            'toggleFavorite'
            ])->whereNumber('albumId')
            ->whereNumber('photoId')
            ->name('photos.favorite.toggle');



             });

            // Logout
            Route::post('/logout', [AuthController::class, 'logout'])
                ->name('logout');
        });
    });
