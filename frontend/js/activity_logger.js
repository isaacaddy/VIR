// Activity Logger Utility
// This utility helps prevent malformed activity logs

class ActivityLogger {
    constructor(apiUrl = 'http://localhost/VIR/backend/api/activity_logs.php') {
        this.apiUrl = apiUrl;
    }

    // Validate log data before sending
    validateLogData(action, userEmail, details = null) {
        const errors = [];

        // Check for undefined or null values
        if (!action || action === 'undefined' || typeof action !== 'string') {
            errors.push('Action is required and must be a valid string');
        }

        if (!userEmail || userEmail === 'undefined' || typeof userEmail !== 'string') {
            errors.push('User email is required and must be a valid string');
        }

        // Check for malformed patterns
        if (action && (action.includes('undefined') || action.includes('tableystem'))) {
            errors.push('Action contains invalid data patterns');
        }

        if (details && (details.includes('undefined') || details.includes('Invalid Date'))) {
            errors.push('Details contain invalid data patterns');
        }

        // Length validation
        if (action && action.length > 100) {
            errors.push('Action is too long (max 100 characters)');
        }

        if (userEmail && userEmail.length > 100) {
            errors.push('User email is too long (max 100 characters)');
        }

        if (details && details.length > 500) {
            errors.push('Details are too long (max 500 characters)');
        }

        return errors;
    }

    // Safe log function that validates data before sending
    async logActivity(action, userEmail, details = null) {
        try {
            // Validate input
            const errors = this.validateLogData(action, userEmail, details);
            if (errors.length > 0) {
                console.error('Activity log validation failed:', errors);
                return {
                    status: 'error',
                    message: 'Validation failed: ' + errors.join(', ')
                };
            }

            // Clean the data
            const cleanData = {
                action: String(action).trim(),
                user_email: String(userEmail).trim(),
                details: details ? String(details).trim() : null
            };

            // Send to API
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(cleanData)
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Failed to log activity');
            }

            return result;

        } catch (error) {
            console.error('Failed to log activity:', error);
            return {
                status: 'error',
                message: error.message
            };
        }
    }

    // Convenience methods for common log types
    async logLogin(userEmail) {
        return this.logActivity('User Login', userEmail, 'User logged in successfully');
    }

    async logLogout(userEmail) {
        return this.logActivity('User Logout', userEmail, 'User logged out');
    }

    async logRecordCreated(userEmail, recordType = 'record') {
        return this.logActivity('Record Created', userEmail, `New ${recordType} created`);
    }

    async logRecordUpdated(userEmail, recordType = 'record') {
        return this.logActivity('Record Updated', userEmail, `${recordType} updated`);
    }

    async logRecordDeleted(userEmail, recordType = 'record') {
        return this.logActivity('Record Deleted', userEmail, `${recordType} deleted`);
    }

    async logOwnershipTransfer(userEmail, vehicleInfo = '') {
        const details = vehicleInfo ? `Vehicle ownership transferred: ${vehicleInfo}` : 'Vehicle ownership transferred';
        return this.logActivity('Ownership Transfer', userEmail, details);
    }

    async logSystemBackup(userEmail) {
        return this.logActivity('System Backup', userEmail, 'Database backup created successfully');
    }
}

// Create a global instance
const activityLogger = new ActivityLogger();

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ActivityLogger;
}