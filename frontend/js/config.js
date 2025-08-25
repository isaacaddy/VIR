// API Configuration for Docker environment
const API_CONFIG = {
    BASE_URL: 'http://localhost:8000',
    ENDPOINTS: {
        DASHBOARD_STATS: '/api/dashboard_stats.php',
        USER_INFO: '/api/user_info.php',
        SAVE_OWNERSHIP: '/api/save_ownership.php',
        UPDATE_OWNERSHIP: '/api/update_ownership.php',
        GET_RECORDS: '/api/get_record.php',
        VEHICLE_REGISTRATION: '/api/vehicle_registration.php',
        VEHICLE_STATISTICS: '/api/vehicle_statistics.php',
        VEHICLE_HISTORY: '/api/vehicle_history.php',
        ACTIVITY_LOGS: '/api/activity_logs.php',
        USERS: '/api/users.php',
        LOGOUT: '/api/logout.php',
        DELETE_RECORD: '/api/delete_record.php',
        UPDATE_RECORD: '/api/update_record.php',
        SEARCH_RECORDS: '/api/search_records.php',
        TEST_DATABASE: '/api/test_database.php'
    }
};

// Helper function to build full API URLs
function getApiUrl(endpoint) {
    return API_CONFIG.BASE_URL + endpoint;
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { API_CONFIG, getApiUrl };
}