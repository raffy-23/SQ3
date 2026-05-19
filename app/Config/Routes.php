<?php

use CodeIgniter\Router\RouteCollection;

/**
 * SideQuest Route Definitions
 *
 * Routes are organized into four tiers based on access level:
 *   1. Public        — accessible by anyone
 *   2. Guest-only    — redirects away if already logged in
 *   3. Auth-required — must be logged in (email not required to be verified)
 *   4. Verified      — must be logged in AND have a verified email
 *
 * @var RouteCollection $routes
 */

// ──────────────────────────────────────────────────────────
// PUBLIC ROUTES
// Accessible by anyone, logged in or not.
// ──────────────────────────────────────────────────────────

$routes->get('/', 'Home::index'); // Welcome / landing page

// Two-factor auth challenge sits outside guest/auth filters so
// it is reachable immediately after login before the session is
// fully established.
$routes->get('two-factor-challenge', 'Auth\TwoFactorChallengeController::show');
$routes->post('two-factor-challenge', 'Auth\TwoFactorChallengeController::store');


// ──────────────────────────────────────────────────────────
// GUEST-ONLY ROUTES  (filter: guest)
// Authenticated users are redirected away from these pages.
// ──────────────────────────────────────────────────────────

$routes->group('', ['filter' => 'guest'], static function (RouteCollection $routes): void {
    // Login
    $routes->get('login', 'Auth\AuthController::showLogin');
    $routes->post('login', 'Auth\AuthController::login');

    // Registration
    $routes->get('register', 'Auth\AuthController::showRegister');
    $routes->post('register', 'Auth\AuthController::register');

    // Password reset flow
    $routes->get('forgot-password', 'Auth\PasswordController::showForgotPassword');
    $routes->post('forgot-password', 'Auth\PasswordController::sendResetLink');
    $routes->get('reset-password/(:segment)', 'Auth\PasswordController::showResetPassword/$1');
    $routes->post('reset-password', 'Auth\PasswordController::resetPassword');
});


// ──────────────────────────────────────────────────────────
// AUTHENTICATED ROUTES  (filter: auth)
// Requires login. Email verification is NOT required here.
// ──────────────────────────────────────────────────────────

$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    // Logout
    $routes->post('logout', 'Auth\AuthController::logout');

    // Email verification notices & confirmation
    $routes->get('email/verify', 'Auth\EmailVerificationController::notice');
    $routes->post('email/verification-notification', 'Auth\EmailVerificationController::send');
    $routes->get('email/verify/(:num)/(:segment)', 'Auth\EmailVerificationController::verify/$1/$2');

    // Password confirmation gate (before sensitive actions)
    $routes->get('user/confirm-password', 'Auth\ConfirmPasswordController::show');
    $routes->post('user/confirm-password', 'Auth\ConfirmPasswordController::store');

    // Theme / appearance toggle (dark mode, etc.)
    $routes->post('appearance', 'AppearanceController::update');

    // User settings — profile editing & account deletion
    $routes->get('settings', 'Settings\ProfileController::index');
    $routes->get('settings/profile', 'Settings\ProfileController::edit');
    $routes->post('settings/profile', 'Settings\ProfileController::update');
    $routes->delete('settings/profile', 'Settings\ProfileController::destroy');
    $routes->get('settings/appearance', 'Settings\AppearanceController::edit');
});


// ──────────────────────────────────────────────────────────
// VERIFIED ROUTES  (filter: verified)
// Requires login + verified email. Core app functionality.
// ──────────────────────────────────────────────────────────

$routes->group('', ['filter' => 'verified'], static function (RouteCollection $routes): void {

    // ── Feed ──────────────────────────────────────────────
    $routes->get('feed', 'FeedController::index');                     // Main news feed
    $routes->get('dashboard', static fn() => redirect()->to(site_url('feed'))); // Legacy redirect

    // ── Posts ─────────────────────────────────────────────
    $routes->post('posts', 'PostController::store');              // Create a new post
    $routes->get('posts/(:num)', 'PostController::show/$1');      // View a single post
    $routes->patch('posts/(:num)', 'PostController::update/$1');  // Edit a post
    $routes->delete('posts/(:num)', 'PostController::destroy/$1'); // Delete a post
    $routes->post('posts/(:num)/share', 'PostController::share/$1'); // Share / re-post

    // ── Post interactions ─────────────────────────────────
    $routes->post('posts/(:num)/reactions', 'ReactionController::store/$1');   // React to a post (like, love, etc.)
    $routes->post('posts/(:num)/save', 'PostSaveController::store/$1');        // Save a post
    $routes->delete('posts/(:num)/save', 'PostSaveController::destroy/$1');    // Unsave a post
    $routes->post('posts/(:num)/hide', 'PostHideController::store/$1');        // Hide a post from the feed

    // ── Comments ──────────────────────────────────────────
    $routes->post('posts/(:num)/comments', 'CommentController::store/$1');  // Add a comment to a post
    $routes->patch('comments/(:num)', 'CommentController::update/$1');       // Edit a comment
    $routes->delete('comments/(:num)', 'CommentController::destroy/$1');     // Delete a comment
    $routes->post('comments/(:num)/reactions', 'CommentReactionController::store/$1'); // React to a comment

    // Hidden comments (moderation / collapsed threads)
    $routes->get('posts/(:num)/hidden-comments', 'CommentHideController::index/$1');  // List hidden comments for a post
    $routes->post('comments/(:num)/hide', 'CommentHideController::store/$1');         // Hide a specific comment

    // ── User profiles & social graph ─────────────────────
    $routes->get('u/(:segment)', 'UserController::show/$1');           // Public profile page  (/u/username)
    $routes->post('users/(:num)/follow', 'FollowController::store/$1');    // Follow a user
    $routes->delete('users/(:num)/follow', 'FollowController::destroy/$1'); // Unfollow a user

    // ── Search ────────────────────────────────────────────
    $routes->get('search', 'SearchController::index');       // Full search results page
    $routes->get('search/live', 'SearchController::live');   // Live / typeahead search (AJAX)

    // ── Profile media ─────────────────────────────────────
    $routes->post('profile-picture', 'ProfilePictureController::store');    // Upload profile picture
    $routes->delete('profile-picture', 'ProfilePictureController::destroy'); // Remove profile picture
    $routes->post('cover-photo', 'ProfileCoverController::store');          // Upload cover photo
    $routes->delete('cover-photo', 'ProfileCoverController::destroy');      // Remove cover photo

    // ── Settings (security & 2FA) ─────────────────────────
    $routes->get('settings/security', 'Settings\SecurityController::edit');
    $routes->post('settings/security', 'Settings\SecurityController::update');     // Change password
    $routes->post('settings/two-factor/enable', 'Settings\TwoFactorController::enable');   // Start 2FA setup
    $routes->post('settings/two-factor/confirm', 'Settings\TwoFactorController::confirm'); // Confirm 2FA code
    $routes->post('settings/two-factor/cancel', 'Settings\TwoFactorController::cancel');   // Cancel 2FA setup
    $routes->post('settings/two-factor/disable', 'Settings\TwoFactorController::disable'); // Turn off 2FA

    // ── Internal JSON / AJAX API ──────────────────────────
    $routes->get('api/posts/(:num)/reactions', 'ReactionController::index/$1');        // Reaction breakdown for a post
    $routes->get('api/notifications', 'NotificationController::index');                // Notification list (AJAX)
    $routes->post('notifications/(:segment)/read', 'NotificationController::markAsRead/$1'); // Mark one notification read
    $routes->post('notifications/read-all', 'NotificationController::markAllAsRead');  // Mark all notifications read
    $routes->get('api/recommendations', 'RecommendationController::index');            // "People you may know" suggestions
});
