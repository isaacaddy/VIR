// API Configuration
const API_BASE_URL = 'http://localhost/VIR/backend/api';

// Helper function to build API URLs
function apiUrl(endpoint) {
    return `${API_BASE_URL}/${endpoint}`;
}