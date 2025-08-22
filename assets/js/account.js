/**
 * DEVA Account Page JavaScript
 */

// Global variables for account functionality
const devaAccount = {
    init: function() {
        this.bindEvents();
        this.initializeTooltips();
        this.initializeProfileForm();
    },

    bindEvents: function() {
        // Schedule next session
        const scheduleBtn = document.querySelector('.deva-schedule-next');
        if (scheduleBtn) {
            scheduleBtn.addEventListener('click', this.scheduleNextSession);
        }

        // Edit session handlers
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('deva-edit')) {
                const sessionId = e.target.getAttribute('data-session-id');
                const editType = e.target.getAttribute('data-edit-type');
                
                if (editType === 'time') {
                    devaAccount.editSessionTime(sessionId);
                } else if (editType === 'date') {
                    devaAccount.editSessionDate(sessionId);
                }
            }
        });

        // Calendar event handlers
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('deva-icon') && e.target.classList.contains('dashicons-bell')) {
                const sessionId = e.target.getAttribute('data-session-id');
                devaAccount.addToCalendar(sessionId);
            }
        });
    },

    scheduleNextSession: function() {
        // Check if user has active program
        const hasProgram = document.querySelector('.deva-program-box:not(.deva-no-program)');
        
        if (!hasProgram) {
            devaAccount.showNotification('Please purchase a program first to schedule sessions.', 'warning');
            return;
        }

        // Open scheduling modal or redirect to booking page
        const bookingUrl = devaAccountData.booking_url || '#';
        
        if (bookingUrl === '#') {
            devaAccount.openSchedulingModal();
        } else {
            window.location.href = bookingUrl;
        }
    },

    editSessionTime: function(sessionId) {
        const currentTime = document.querySelector(`[data-session-id="${sessionId}"] .deva-session-time`);
        const timeText = currentTime ? currentTime.textContent : '';
        
        const newTime = prompt('Enter new session time (e.g., 2:00–2:30 GMT+0:00):', timeText.replace('Time: ', ''));
        
        if (newTime && newTime.trim()) {
            // AJAX call to update session time
            this.updateSessionData(sessionId, 'time', newTime.trim());
        }
    },

    editSessionDate: function(sessionId) {
        const currentDate = document.querySelector(`[data-session-id="${sessionId}"] .deva-session-date`);
        const dateText = currentDate ? currentDate.textContent : '';
        
        const newDate = prompt('Enter new session date (e.g., 15th JUN):', dateText.replace('Date: ', ''));
        
        if (newDate && newDate.trim()) {
            // AJAX call to update session date
            this.updateSessionData(sessionId, 'date', newDate.trim());
        }
    },

    updateSessionData: function(sessionId, field, value) {
        if (!devaAccountData.ajax_url || !devaAccountData.nonce) {
            console.error('AJAX configuration missing');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'deva_update_session');
        formData.append('session_id', sessionId);
        formData.append('field', field);
        formData.append('value', value);
        formData.append('nonce', devaAccountData.nonce);

        fetch(devaAccountData.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the display
                const element = document.querySelector(`[data-session-id="${sessionId}"] .deva-session-${field}`);
                if (element) {
                    const iconClass = field === 'time' ? 'dashicons-clock' : 'dashicons-calendar-alt';
                    const label = field === 'time' ? 'Time:' : 'Date:';
                    element.innerHTML = `<span class="dashicons ${iconClass}"></span> ${label} ${value} <span class="deva-edit dashicons dashicons-edit" data-session-id="${sessionId}" data-edit-type="${field}"></span>`;
                }
                this.showNotification('Session updated successfully!', 'success');
            } else {
                this.showNotification(data.message || 'Failed to update session.', 'error');
            }
        })
        .catch(error => {
            console.error('Error updating session:', error);
            this.showNotification('An error occurred while updating the session.', 'error');
        });
    },

    addToCalendar: function(sessionId) {
        const sessionCard = document.querySelector(`[data-session-id="${sessionId}"]`);
        if (!sessionCard) return;

        const timeElement = sessionCard.querySelector('.deva-session-time');
        const dateElement = sessionCard.querySelector('.deva-session-date');
        const titleElement = sessionCard.querySelector('h4');

        const time = timeElement ? timeElement.textContent.replace('Time: ', '').trim() : '';
        const date = dateElement ? dateElement.textContent.replace('Date: ', '').trim() : '';
        const title = titleElement ? titleElement.textContent : 'DEVA Session';

        // Create calendar event
        const calendarData = {
            title: title,
            start: this.parseDateTime(date, time),
            description: 'DEVA wellness session',
            location: 'Online'
        };

        // Generate calendar URLs
        const googleUrl = this.generateGoogleCalendarUrl(calendarData);
        const icsData = this.generateICSData(calendarData);

        // Show calendar options
        this.showCalendarOptions(googleUrl, icsData);
    },

    parseDateTime: function(date, time) {
        // Parse date and time strings into ISO format
        // This is a simplified parser - in production, use a proper date library
        const currentYear = new Date().getFullYear();
        return new Date(`${date} ${currentYear} ${time.split('–')[0]}`).toISOString();
    },

    generateGoogleCalendarUrl: function(event) {
        const baseUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE';
        const params = new URLSearchParams({
            text: event.title,
            dates: event.start.replace(/[-:]/g, '').replace('.000Z', 'Z'),
            details: event.description,
            location: event.location
        });
        return `${baseUrl}&${params.toString()}`;
    },

    generateICSData: function(event) {
        const ics = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//DEVA//Account Page//EN',
            'BEGIN:VEVENT',
            `DTSTART:${event.start.replace(/[-:]/g, '').replace('.000Z', 'Z')}`,
            `SUMMARY:${event.title}`,
            `DESCRIPTION:${event.description}`,
            `LOCATION:${event.location}`,
            'END:VEVENT',
            'END:VCALENDAR'
        ].join('\r\n');

        return 'data:text/calendar;charset=utf8,' + encodeURIComponent(ics);
    },

    showCalendarOptions: function(googleUrl, icsData) {
        const modal = document.createElement('div');
        modal.className = 'deva-calendar-modal';
        modal.innerHTML = `
            <div class="deva-calendar-modal-content">
                <h3>Add to Calendar</h3>
                <div class="deva-calendar-options">
                    <a href="${googleUrl}" target="_blank" class="deva-calendar-option">
                        <span class="dashicons dashicons-calendar-alt"></span> Google Calendar
                    </a>
                    <a href="${icsData}" download="session.ics" class="deva-calendar-option">
                        <span class="dashicons dashicons-download"></span> Download ICS File
                    </a>
                </div>
                <button class="deva-close-modal">Close</button>
            </div>
        `;

        document.body.appendChild(modal);

        // Close modal functionality
        modal.querySelector('.deva-close-modal').addEventListener('click', () => {
            document.body.removeChild(modal);
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        });
    },

    joinCall: function(meetingUrl) {
        if (meetingUrl && meetingUrl !== '#') {
            window.open(meetingUrl, '_blank');
        } else {
            this.showNotification('Meeting link not available yet.', 'info');
        }
    },

    openSchedulingModal: function() {
        const modal = document.createElement('div');
        modal.className = 'deva-scheduling-modal';
        modal.innerHTML = `
            <div class="deva-scheduling-modal-content">
                <h3>Schedule Your Next Session</h3>
                <form class="deva-scheduling-form">
                    <div class="deva-form-group">
                        <label>Preferred Date:</label>
                        <input type="date" name="preferred_date" required>
                    </div>
                    <div class="deva-form-group">
                        <label>Preferred Time:</label>
                        <select name="preferred_time" required>
                            <option value="">Select time...</option>
                            <option value="09:00">9:00 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="14:00">2:00 PM</option>
                            <option value="15:00">3:00 PM</option>
                            <option value="16:00">4:00 PM</option>
                        </select>
                    </div>
                    <div class="deva-form-group">
                        <label>Notes (optional):</label>
                        <textarea name="notes" placeholder="Any specific requirements or notes..."></textarea>
                    </div>
                    <div class="deva-form-actions">
                        <button type="submit" class="deva-schedule-submit">Schedule Session</button>
                        <button type="button" class="deva-cancel-schedule">Cancel</button>
                    </div>
                </form>
            </div>
        `;

        document.body.appendChild(modal);

        // Form submission
        modal.querySelector('.deva-scheduling-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitSchedulingRequest(new FormData(e.target));
            document.body.removeChild(modal);
        });

        // Cancel button
        modal.querySelector('.deva-cancel-schedule').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
    },

    submitSchedulingRequest: function(formData) {
        formData.append('action', 'deva_schedule_session');
        formData.append('nonce', devaAccountData.nonce);

        fetch(devaAccountData.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification('Session scheduling request submitted successfully!', 'success');
                // Optionally reload the page to show the new session
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                this.showNotification(data.message || 'Failed to schedule session.', 'error');
            }
        })
        .catch(error => {
            console.error('Error scheduling session:', error);
            this.showNotification('An error occurred while scheduling the session.', 'error');
        });
    },

    showNotification: function(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `deva-notification deva-notification-${type}`;
        notification.textContent = message;

        // Add to page
        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);

        // Click to dismiss
        notification.addEventListener('click', () => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        });
    },

    initializeTooltips: function() {
        // Add tooltips for edit icons
        const editIcons = document.querySelectorAll('.deva-edit');
        editIcons.forEach(icon => {
            icon.title = 'Click to edit';
        });

        // Add tooltips for disabled buttons
        const disabledButtons = document.querySelectorAll('.deva-call-button.deva-disabled');
        disabledButtons.forEach(button => {
            button.title = 'Schedule your session first';
        });
    },

    initializeProfileForm: function() {
        const profileForm = document.getElementById('deva-profile-form');
        
        if (profileForm) {
            profileForm.addEventListener('submit', (e) => {
                e.preventDefault();
                
                const formData = new FormData(profileForm);
                formData.append('action', 'update_deva_profile');
                
                // Password validation
                const password = formData.get('user_pass');
                const confirmPassword = formData.get('user_pass_confirm');
                
                if (password && password !== confirmPassword) {
                    this.showNotification("Passwords don't match. Please try again.", 'error');
                    return;
                }
                
                // Show loading state
                const submitBtn = profileForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="dashicons dashicons-update-alt"></span> Updating...';
                submitBtn.disabled = true;
                
                // Get AJAX URL from account data or construct it
                const ajaxUrl = window.devaAccountData?.ajax_url || '/wp-admin/admin-ajax.php';
                
                // Send AJAX request
                fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        this.showNotification(data.data.message || 'Profile updated successfully!', 'success');
                        
                        // Reset password fields
                        const passwordField = document.getElementById('user_pass');
                        const confirmPasswordField = document.getElementById('user_pass_confirm');
                        if (passwordField) passwordField.value = '';
                        if (confirmPasswordField) confirmPasswordField.value = '';
                    } else {
                        // Show error message
                        this.showNotification(data.data.message || 'Error updating profile. Please try again.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.showNotification('Network error. Please check your connection and try again.', 'error');
                })
                .finally(() => {
                    // Restore button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            });
        }
    }
};

// Global functions for inline event handlers
function scheduleNextSession() {
    devaAccount.scheduleNextSession();
}

function editSessionTime(sessionId) {
    devaAccount.editSessionTime(sessionId);
}

function editSessionDate(sessionId) {
    devaAccount.editSessionDate(sessionId);
}

function addToCalendar(sessionId) {
    devaAccount.addToCalendar(sessionId);
}

function joinCall(meetingUrl) {
    devaAccount.joinCall(meetingUrl);
}

function toggleSettings(event) {
    event.preventDefault();
    
    const settingsSection = document.getElementById('deva-settings-section');
    const settingsButton = document.querySelector('.deva-settings-toggle');
    const arrow = settingsButton ? settingsButton.querySelector('.deva-settings-arrow') : null;
    
    if (settingsSection.classList.contains('deva-settings-open')) {
        // Hide settings
        settingsSection.classList.remove('deva-settings-open');
        if (arrow) {
            arrow.classList.remove('dashicons-arrow-up');
            arrow.classList.add('dashicons-arrow-down');
        }
    } else {
        // Show settings
        settingsSection.classList.add('deva-settings-open');
        if (arrow) {
            arrow.classList.remove('dashicons-arrow-down');
            arrow.classList.add('dashicons-arrow-up');
        }
        
        // Smooth scroll to settings section after animation
        setTimeout(() => {
            settingsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 300);
    }
}

function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    const toggle = field.parentElement.querySelector('.deva-password-toggle');
    if (!toggle) return;
    
    const showText = toggle.querySelector('.show-text');
    const hideText = toggle.querySelector('.hide-text');
    
    if (!showText || !hideText) return;
    
    if (field.type === 'password') {
        field.type = 'text';
        showText.style.display = 'none';
        hideText.style.display = 'inline';
    } else {
        field.type = 'password';
        showText.style.display = 'inline';
        hideText.style.display = 'none';
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    devaAccount.init();
});
