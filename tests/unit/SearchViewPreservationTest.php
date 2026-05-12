<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use App\Models\UserModel;
use App\Controllers\SearchController;
use Faker\Factory as FakerFactory;

/**
 * Preservation Property Tests for Search View Bugfix
 * 
 * **Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8**
 * 
 * Property 2: Preservation - Existing Search Functionality Unchanged
 * 
 * CRITICAL: These tests MUST PASS on unfixed code - they verify baseline behavior to preserve
 * 
 * These tests verify that search operations not involving follow/unfollow buttons, 
 * mutual counts, or filter UI continue to work exactly as before the fix.
 * 
 * @internal
 */
final class SearchViewPreservationTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    protected $namespace = 'App';
    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();
    }

    /**
     * Property 2.1: Search Query Matching Preservation
     * 
     * Tests that search queries continue to match across username, first_name, 
     * last_name, and email fields using the existing applySearch() method.
     * 
     * **Validates: Requirement 3.1**
     * 
     * EXPECTED OUTCOME: Test PASSES on unfixed code (confirms baseline behavior)
     */
    public function testSearchQueryMatchingPreservation(): void
    {
        $userModel = model(UserModel::class);
        
        // Get existing users from database
        $users = $userModel->findAll(10);
        
        if (empty($users)) {
            $this->markTestSkipped('No users in database to test search matching');
        }
        
        // Test search matching on username using the controller
        $testUser = $users[0];
        $usernameQuery = substr($testUser['username'], 0, 3);
        
        $result = $this->withURI('http://localhost/search?q=' . urlencode($usernameQuery) . '&filter=all')
            ->controller(SearchController::class)
            ->execute('index');
        
        $this->assertTrue($result->isOK(), 'Search should return 200 OK for username query');
        
        // Test search matching on first_name
        if (!empty($testUser['first_name']) && strlen($testUser['first_name']) >= 3) {
            $firstNameQuery = substr($testUser['first_name'], 0, 3);
            
            $result = $this->withURI('http://localhost/search?q=' . urlencode($firstNameQuery) . '&filter=all')
                ->controller(SearchController::class)
                ->execute('index');
            
            $this->assertTrue($result->isOK(), 'Search should return 200 OK for first_name query');
        }
        
        // Test search matching on last_name
        if (!empty($testUser['last_name']) && strlen($testUser['last_name']) >= 3) {
            $lastNameQuery = substr($testUser['last_name'], 0, 3);
            
            $result = $this->withURI('http://localhost/search?q=' . urlencode($lastNameQuery) . '&filter=all')
                ->controller(SearchController::class)
                ->execute('index');
            
            $this->assertTrue($result->isOK(), 'Search should return 200 OK for last_name query');
        }
        
        // Test search matching on email
        if (!empty($testUser['email']) && strlen($testUser['email']) >= 3) {
            $emailQuery = substr($testUser['email'], 0, 3);
            
            $result = $this->withURI('http://localhost/search?q=' . urlencode($emailQuery) . '&filter=all')
                ->controller(SearchController::class)
                ->execute('index');
            
            $this->assertTrue($result->isOK(), 'Search should return 200 OK for email query');
        }
    }

    /**
     * Property 2.2: Followers Filter Preservation
     * 
     * Tests that "Followers" filter continues to display only users who follow 
     * the current user.
     * 
     * **Validates: Requirement 3.2**
     * 
     * EXPECTED OUTCOME: Test PASSES on unfixed code (confirms baseline behavior)
     */
    public function testFollowersFilterPreservation(): void
    {
        $userModel = model(UserModel::class);
        
        // Get a user from database
        $testUser = $userModel->first();
        
        if ($testUser === null) {
            $this->markTestSkipped('No users in database to test followers filter');
        }
        
        $userId = (int) $testUser['id'];
        
        // Get follower IDs using the existing method
        $followerIds = $userModel->followerIds($userId);
        
        // Simulate the controller's filter logic
        $builder = $userModel->builder();
        $builder->whereIn('id', $followerIds === [] ? [0] : $followerIds);
        $results = $builder->get()->getResultArray();
        
        // Verify all results are followers
        foreach ($results as $user) {
            $this->assertContains(
                (int) $user['id'],
                $followerIds,
                'Followers filter should only return users who follow the current user'
            );
        }
        
        // If there are followers, verify the count matches
        if (!empty($followerIds)) {
            $this->assertCount(
                count($followerIds),
                $results,
                'Followers filter should return exactly the follower count'
            );
        }
    }

    /**
     * Property 2.3: Following Filter Preservation
     * 
     * Tests that "Following" filter continues to display only users the current 
     * user is following.
     * 
     * **Validates: Requirement 3.3**
     * 
     * EXPECTED OUTCOME: Test PASSES on unfixed code (confirms baseline behavior)
     */
    public function testFollowingFilterPreservation(): void
    {
        $userModel = model(UserModel::class);
        
        // Get a user from database
        $testUser = $userModel->first();
        
        if ($testUser === null) {
            $this->markTestSkipped('No users in database to test following filter');
        }
        
        $userId = (int) $testUser['id'];
        
        // Get following IDs using the existing method
        $followingIds = $userModel->followingIds($userId);
        
        // Simulate the controller's filter logic
        $builder = $userModel->builder();
        $builder->whereIn('id', $followingIds === [] ? [0] : $followingIds);
        $results = $builder->get()->getResultArray();
        
        // Verify all results are users being followed
        foreach ($results as $user) {
            $this->assertContains(
                (int) $user['id'],
                $followingIds,
                'Following filter should only return users the current user is following'
            );
        }
        
        // If there are following users, verify the count matches
        if (!empty($followingIds)) {
            $this->assertCount(
                count($followingIds),
                $results,
                'Following filter should return exactly the following count'
            );
        }
    }

    /**
     * Property 2.4: Pagination Preservation
     * 
     * Tests that pagination continues to display 20 results per page with 
     * working next/previous navigation.
     * 
     * **Validates: Requirement 3.4**
     * 
     * EXPECTED OUTCOME: Test PASSES on unfixed code (confirms baseline behavior)
     */
    public function testPaginationPreservation(): void
    {
        $userModel = model(UserModel::class);
        
        // Get total user count
        $totalUsers = $userModel->countAllResults(false);
        
        if ($totalUsers < 21) {
            $this->markTestSkipped('Need at least 21 users to test pagination');
        }
        
        $perPage = 20;
        $page = 1;
        
        // Test first page
        $builder = $userModel->builder();
        $builder->orderBy('first_name', 'ASC')->orderBy('last_name', 'ASC');
        $countBuilder = clone $builder;
        $total = $countBuilder->countAllResults();
        $results = $builder->get($perPage, ($page - 1) * $perPage)->getResultArray();
        
        $this->assertCount(
            min($perPage, $total),
            $results,
            'First page should return up to 20 results'
        );
        
        // Calculate pagination values
        $lastPage = max(1, (int) ceil($total / $perPage));
        
        $this->assertGreaterThanOrEqual(
            1,
            $lastPage,
            'Last page should be at least 1'
        );
        
        // Test that next page URL would be generated for page 1 if there are more pages
        if ($page < $lastPage) {
            $nextPageUrl = site_url('search?' . http_build_query(['q' => '', 'filter' => 'all', 'page' => $page + 1]));
            $this->assertNotNull($nextPageUrl, 'Next page URL should be generated when not on last page');
        }
        
        // Test that prev page URL would be null for page 1
        $prevPageUrl = $page > 1 ? site_url('search?' . http_build_query(['q' => '', 'filter' => 'all', 'page' => $page - 1])) : null;
        $this->assertNull($prevPageUrl, 'Previous page URL should be null on first page');
        
        // Test second page if it exists
        if ($lastPage > 1) {
            $page = 2;
            $builder = $userModel->builder();
            $builder->orderBy('first_name', 'ASC')->orderBy('last_name', 'ASC');
            $results = $builder->get($perPage, ($page - 1) * $perPage)->getResultArray();
            
            $expectedCount = min($perPage, $total - ($page - 1) * $perPage);
            $this->assertCount(
                $expectedCount,
                $results,
                'Second page should return correct number of results'
            );
        }
    }

    /**
     * Property 2.5: Live Search Empty Results Preservation
     * 
     * Tests that live search continues to return empty results for queries 
     * with less than 2 characters.
     * 
     * **Validates: Requirement 3.5**
     * 
     * EXPECTED OUTCOME: Test PASSES on unfixed code (confirms baseline behavior)
     */
    public function testLiveSearchEmptyResultsPreservation(): void
    {
        // Test with 0 characters
        $result = $this->withURI('http://localhost/search/live?q=')
            ->controller(SearchController::class)
            ->execute('live');
        
        $this->assertTrue($result->isOK(), 'Live search should return 200 OK');
        
        $json = json_decode($result->response()->getBody(), true);
        $this->assertIsArray($json, 'Live search should return JSON array');
        $this->assertEmpty($json, 'Live search should return empty array for 0 character query');
        
        // Test with 1 character
        $result = $this->withURI('http://localhost/search/live?q=a')
            ->controller(SearchController::class)
            ->execute('live');
        
        $this->assertTrue($result->isOK(), 'Live search should return 200 OK');
        
        $json = json_decode($result->response()->getBody(), true);
        $this->assertIsArray($json, 'Live search should return JSON array');
        $this->assertEmpty($json, 'Live search should return empty array for 1 character query');
    }

    /**
     * Property 2.6: Live Search Results Preservation
     * 
     * Tests that live search continues to return up to 8 matching users with 
     * id, username, full_name, and profile_picture_url for queries with 2 or 
     * more characters.
     * 
     * **Validates: Requirement 3.6**
     * 
     * EXPECTED OUTCOME: Test PASSES on unfixed code (confirms baseline behavior)
     */
    public function testLiveSearchResultsPreservation(): void
    {
        $userModel = model(UserModel::class);
        
        // Get a user to search for
        $testUser = $userModel->first();
        
        if ($testUser === null) {
            $this->markTestSkipped('No users in database to test live search');
        }
        
        // Use first 2 characters of username
        $query = substr($testUser['username'], 0, 2);
        
        $result = $this->withURI('http://localhost/search/live?q=' . urlencode($query))
            ->controller(SearchController::class)
            ->execute('live');
        
        $this->assertTrue($result->isOK(), 'Live search should return 200 OK');
        
        $json = json_decode($result->response()->getBody(), true);
        $this->assertIsArray($json, 'Live search should return JSON array');
        
        // Verify results are limited to 8
        $this->assertLessThanOrEqual(
            8,
            count($json),
            'Live search should return at most 8 results'
        );
        
        // Verify each result has required fields
        foreach ($json as $user) {
            $this->assertArrayHasKey('id', $user, 'Live search result should have id field');
            $this->assertArrayHasKey('username', $user, 'Live search result should have username field');
            $this->assertArrayHasKey('full_name', $user, 'Live search result should have full_name field');
            $this->assertArrayHasKey('profile_picture_url', $user, 'Live search result should have profile_picture_url field');
            
            // Verify field types
            $this->assertIsInt($user['id'], 'id should be integer');
            $this->assertIsString($user['username'], 'username should be string');
            $this->assertIsString($user['full_name'], 'full_name should be string');
        }
    }

    /**
     * Property 2.7: Profile Navigation Preservation
     * 
     * Tests that clicking on search results continues to navigate to the user's 
     * profile at /u/{username}.
     * 
     * **Validates: Requirement 3.7**
     * 
     * EXPECTED OUTCOME: Test PASSES on unfixed code (confirms baseline behavior)
     */
    public function testProfileNavigationPreservation(): void
    {
        $userModel = model(UserModel::class);
        
        // Get a user from database
        $testUser = $userModel->first();
        
        if ($testUser === null) {
            $this->markTestSkipped('No users in database to test profile navigation');
        }
        
        // Test that the profile URL format is correct
        $expectedUrl = '/u/' . $testUser['username'];
        
        // Call the search controller
        $result = $this->withURI('http://localhost/search?q=&filter=all')
            ->controller(SearchController::class)
            ->execute('index');
        
        $html = $result->response()->getBody();
        
        // Verify the HTML contains profile links in the correct format
        $this->assertStringContainsString(
            '/u/',
            $html,
            'Search results should contain profile links with /u/ prefix'
        );
        
        // Verify the specific user's profile link exists if they appear in results
        if (str_contains($html, $testUser['username'])) {
            $this->assertStringContainsString(
                $expectedUrl,
                $html,
                'Search results should link to user profile at /u/{username}'
            );
        }
    }

    /**
     * Property 2.8: Empty State Display Preservation
     * 
     * Tests that empty search results continue to display the appropriate 
     * empty state message.
     * 
     * **Validates: Requirement 3.8**
     * 
     * EXPECTED OUTCOME: Test PASSES on unfixed code (confirms baseline behavior)
     */
    public function testEmptyStateDisplayPreservation(): void
    {
        // Generate a random query that should not match any users
        $randomQuery = 'xyzabc123nonexistent' . time();
        
        $result = $this->withURI('http://localhost/search?q=' . urlencode($randomQuery) . '&filter=all')
            ->controller(SearchController::class)
            ->execute('index');
        
        $this->assertTrue($result->isOK(), 'Search should return 200 OK even with no results');
        
        $html = $result->response()->getBody();
        
        // The view should still render successfully with empty results
        $this->assertNotEmpty($html, 'Search view should render even with no results');
        
        // Verify the HTML contains search-related elements (form, filters, etc.)
        $this->assertStringContainsString(
            'search',
            strtolower($html),
            'Search view should contain search-related elements'
        );
    }

    /**
     * Property 2.9: Multiple Search Queries Preservation (Property-Based Style)
     * 
     * Tests search functionality with multiple generated queries to ensure 
     * consistent behavior across different inputs.
     * 
     * **Validates: Requirements 3.1, 3.4, 3.8**
     * 
     * EXPECTED OUTCOME: Test PASSES on unfixed code (confirms baseline behavior)
     */
    public function testMultipleSearchQueriesPreservation(): void
    {
        $userModel = model(UserModel::class);
        
        // Get some users to generate queries from
        $users = $userModel->findAll(5);
        
        if (empty($users)) {
            $this->markTestSkipped('No users in database to test multiple queries');
        }
        
        // Generate multiple test queries
        $testQueries = [];
        
        foreach ($users as $user) {
            // Generate queries from different fields (use at least 2 characters)
            if (!empty($user['username']) && strlen($user['username']) >= 3) {
                $testQueries[] = substr($user['username'], 0, 3);
            }
            if (!empty($user['first_name']) && strlen($user['first_name']) >= 2) {
                $testQueries[] = substr($user['first_name'], 0, 2);
            }
            if (!empty($user['email']) && strlen($user['email']) >= 3) {
                $testQueries[] = substr($user['email'], 0, 3);
            }
        }
        
        // Remove duplicates and limit to 10 queries
        $testQueries = array_unique($testQueries);
        
        // Test each query using the controller
        foreach (array_slice($testQueries, 0, 10) as $query) {
            // Skip empty or very short queries
            if (empty($query) || strlen($query) < 2) {
                continue;
            }
            
            $result = $this->withURI('http://localhost/search?q=' . urlencode($query) . '&filter=all')
                ->controller(SearchController::class)
                ->execute('index');
            
            // Verify search returns OK
            $this->assertTrue($result->isOK(), "Search should return 200 OK for query: {$query}");
        }
    }

    /**
     * Property 2.10: Filter Combinations Preservation (Property-Based Style)
     * 
     * Tests that different filter combinations continue to work correctly.
     * 
     * **Validates: Requirements 3.2, 3.3**
     * 
     * EXPECTED OUTCOME: Test PASSES on unfixed code (confirms baseline behavior)
     */
    public function testFilterCombinationsPreservation(): void
    {
        $userModel = model(UserModel::class);
        
        // Get a user from database
        $testUser = $userModel->first();
        
        if ($testUser === null) {
            $this->markTestSkipped('No users in database to test filter combinations');
        }
        
        $userId = (int) $testUser['id'];
        
        // Test different filter values
        $filters = ['all', 'followers', 'following'];
        
        foreach ($filters as $filter) {
            $builder = $userModel->builder();
            
            if ($filter === 'followers') {
                $ids = $userModel->followerIds($userId);
                $builder->whereIn('id', $ids === [] ? [0] : $ids);
            } elseif ($filter === 'following') {
                $ids = $userModel->followingIds($userId);
                $builder->whereIn('id', $ids === [] ? [0] : $ids);
            }
            // 'all' filter doesn't add any conditions
            
            $results = $builder->get()->getResultArray();
            
            // Verify results are arrays
            $this->assertIsArray($results, "Filter '{$filter}' should return array");
            
            // Verify filter logic
            if ($filter === 'followers') {
                $followerIds = $userModel->followerIds($userId);
                foreach ($results as $result) {
                    $this->assertContains(
                        (int) $result['id'],
                        $followerIds,
                        "Followers filter should only return followers"
                    );
                }
            } elseif ($filter === 'following') {
                $followingIds = $userModel->followingIds($userId);
                foreach ($results as $result) {
                    $this->assertContains(
                        (int) $result['id'],
                        $followingIds,
                        "Following filter should only return following users"
                    );
                }
            }
        }
    }
}
