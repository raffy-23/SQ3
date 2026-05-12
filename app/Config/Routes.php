<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('', ['filter' => 'guest'], static function (RouteCollection $routes): void {
    $routes->get('login', 'Auth\AuthController::showLogin');
    $routes->post('login', 'Auth\AuthController::login');
    $routes->get('register', 'Auth\AuthController::showRegister');
    $routes->post('register', 'Auth\AuthController::register');
    $routes->get('forgot-password', 'Auth\PasswordController::showForgotPassword');
    $routes->post('forgot-password', 'Auth\PasswordController::sendResetLink');
    $routes->get('reset-password/(:segment)', 'Auth\PasswordController::showResetPassword/$1');
    $routes->post('reset-password', 'Auth\PasswordController::resetPassword');
});

$routes->get('two-factor-challenge', 'Auth\TwoFactorChallengeController::show');
$routes->post('two-factor-challenge', 'Auth\TwoFactorChallengeController::store');

$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->post('logout', 'Auth\AuthController::logout');
    $routes->get('email/verify', 'Auth\EmailVerificationController::notice');
    $routes->post('email/verification-notification', 'Auth\EmailVerificationController::send');
    $routes->get('email/verify/(:num)/(:segment)', 'Auth\EmailVerificationController::verify/$1/$2');
    $routes->get('user/confirm-password', 'Auth\ConfirmPasswordController::show');
    $routes->post('user/confirm-password', 'Auth\ConfirmPasswordController::store');
    $routes->post('appearance', 'AppearanceController::update');

    $routes->get('settings', 'Settings\ProfileController::index');
    $routes->get('settings/profile', 'Settings\ProfileController::edit');
    $routes->post('settings/profile', 'Settings\ProfileController::update');
    $routes->delete('settings/profile', 'Settings\ProfileController::destroy');
    $routes->get('settings/appearance', 'Settings\AppearanceController::edit');
});

$routes->group('', ['filter' => 'verified'], static function (RouteCollection $routes): void {
    $routes->get('dashboard', 'DashboardController::index');
    $routes->post('posts', 'PostController::store');
    $routes->get('posts/(:num)', 'PostController::show/$1');
    $routes->patch('posts/(:num)', 'PostController::update/$1');
    $routes->delete('posts/(:num)', 'PostController::destroy/$1');
    $routes->post('posts/(:num)/comments', 'CommentController::store/$1');
    $routes->patch('comments/(:num)', 'CommentController::update/$1');
    $routes->delete('comments/(:num)', 'CommentController::destroy/$1');
    $routes->get('posts/(:num)/hidden-comments', 'CommentHideController::index/$1');
    $routes->post('comments/(:num)/hide', 'CommentHideController::store/$1');
    $routes->post('comments/(:num)/reactions', 'CommentReactionController::store/$1');
    $routes->post('posts/(:num)/reactions', 'ReactionController::store/$1');
    $routes->post('posts/(:num)/save', 'PostSaveController::store/$1');
    $routes->delete('posts/(:num)/save', 'PostSaveController::destroy/$1');
    $routes->post('posts/(:num)/hide', 'PostHideController::store/$1');
    $routes->get('u/(:segment)', 'UserController::show/$1');
    $routes->post('users/(:num)/follow', 'FollowController::store/$1');
    $routes->delete('users/(:num)/follow', 'FollowController::destroy/$1');
    $routes->get('search', 'SearchController::index');
    $routes->get('search/live', 'SearchController::live');
    $routes->post('profile-picture', 'ProfilePictureController::store');
    $routes->delete('profile-picture', 'ProfilePictureController::destroy');
    $routes->post('cover-photo', 'ProfileCoverController::store');
    $routes->delete('cover-photo', 'ProfileCoverController::destroy');

    $routes->get('settings/security', 'Settings\SecurityController::edit');
    $routes->post('settings/security', 'Settings\SecurityController::update');
    $routes->post('settings/two-factor/enable', 'Settings\TwoFactorController::enable');
    $routes->post('settings/two-factor/confirm', 'Settings\TwoFactorController::confirm');
    $routes->post('settings/two-factor/cancel', 'Settings\TwoFactorController::cancel');
    $routes->post('settings/two-factor/disable', 'Settings\TwoFactorController::disable');

    $routes->get('api/posts/(:num)/reactions', 'ReactionController::index/$1');
    $routes->get('api/notifications', 'NotificationController::index');
    $routes->post('notifications/(:segment)/read', 'NotificationController::markAsRead/$1');
    $routes->post('notifications/read-all', 'NotificationController::markAllAsRead');
    $routes->get('api/recommendations', 'RecommendationController::index');
});
