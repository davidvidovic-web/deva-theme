/**
 * DEVA Amelia Recurring Appointments - Frontend Auto Date Selection
 * Automatically shows weekly recurring dates when customer picks first date
 */

(function($) {
    'use strict';
    
    let devaAmeliaRecurring = {
        config: window.devaAmeliaConfig || {},
        isRecurringService: false,
        selectedDates: [],
        
        init: function() {
            this.log('DEVA Amelia Recurring initialized');
            this.waitForAmelia();
        },
        
        waitForAmelia: function() {
            let attempts = 0;
            let maxAttempts = 100;
            
            let checkAmelia = () => {
                attempts++;
                
                // Check for various Amelia selectors
                if ($('.amelia-app-booking').length > 0 || 
                    $('.amelia-booking').length > 0 ||
                    $('.amelia-step-booking').length > 0 ||
                    $('[id*="amelia"]').length > 0) {
                    this.log('Amelia booking widget detected');
                    setTimeout(() => this.initializeRecurringLogic(), 1000);
                    return;
                }
                
                if (attempts < maxAttempts) {
                    setTimeout(checkAmelia, 300);
                }
            };
            
            setTimeout(checkAmelia, 500);
        },
        
        initializeRecurringLogic: function() {
            this.log('Initializing recurring logic');
            this.monitorServiceSelection();
            this.monitorDateSelection();
            this.addObserver();
        },
        
        addObserver: function() {
            // Use MutationObserver to watch for dynamic changes
            let observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.addedNodes.length > 0) {
                        // Check if service selection changed
                        this.checkServiceSelectionChange();
                        // Check if date selection changed
                        if (this.isRecurringService) {
                            this.checkDateSelectionChange();
                        }
                    }
                });
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['class', 'value']
            });
        },
        
        monitorServiceSelection: function() {
            // Initial check
            this.checkServiceSelectionChange();
            
            // Regular polling as backup
            setInterval(() => this.checkServiceSelectionChange(), 1000);
        },
        
        monitorDateSelection: function() {
            // Initial check
            this.checkDateSelectionChange();
            
            // Regular polling for date changes
            setInterval(() => {
                if (this.isRecurringService) {
                    this.checkDateSelectionChange();
                }
            }, 1000);
        },
        
        checkServiceSelectionChange: function() {
            let serviceSelectors = [
                '.amelia-select-service .el-input__inner',
                '.amelia-select-service input',
                '.amelia-service-name',
                '.el-select__input input',
                '[data-service-name]',
                '.amelia-booking-form select option:selected',
                '.amelia-select-service .el-select__selected'
            ];
            
            let selectedService = null;
            
            for (let selector of serviceSelectors) {
                let elements = $(selector);
                elements.each(function() {
                    let value = $(this).val() || $(this).text() || $(this).attr('data-service-name');
                    if (value && value.trim() && value !== 'Select service') {
                        selectedService = value.trim();
                        return false; // break
                    }
                });
                if (selectedService) break;
            }
            
            if (selectedService && selectedService !== this.lastCheckedService) {
                this.lastCheckedService = selectedService;
                this.isRecurringService = this.checkIfRecurringService(selectedService);
                this.log('Service selected: ' + selectedService + ' | Is recurring: ' + this.isRecurringService);
                
                if (this.isRecurringService) {
                    this.showRecurringNotice();
                } else {
                    this.hideRecurringNotice();
                }
            }
        },
        
        checkDateSelectionChange: function() {
            if (!this.isRecurringService) return;
            
            let dateSelectors = [
                '.amelia-calendar .el-calendar-day.is-selected',
                '.amelia-calendar .selected-date',
                '.amelia-date-picker input',
                '.el-date-editor input',
                '.el-input__inner[placeholder*="date"]',
                '.amelia-booking-form input[type="date"]'
            ];
            
            let selectedDate = null;
            
            for (let selector of dateSelectors) {
                let element = $(selector);
                if (element.length > 0) {
                    selectedDate = element.val() || element.attr('data-date') || element.text();
                    if (selectedDate && selectedDate.trim()) {
                        selectedDate = selectedDate.trim();
                        break;
                    }
                }
            }
            
            if (selectedDate && selectedDate !== this.lastSelectedDate) {
                this.lastSelectedDate = selectedDate;
                this.log('Date selected: ' + selectedDate);
                this.handleDateSelection(selectedDate);
            }
        },
        
        checkIfRecurringService: function(serviceName) {
            if (!serviceName) return false;
            
            let name = serviceName.toLowerCase();
            let keywords = this.config.recurringKeywords || ['weekly', 'recurring', 'course', 'program', 'series', 'package'];
            
            return keywords.some(keyword => name.includes(keyword.toLowerCase()));
        },
        
        handleDateSelection: function(selectedDate) {
            this.log('Handling recurring date selection for: ' + selectedDate);
            
            // Parse the selected date
            let startDate = this.parseDate(selectedDate);
            if (!startDate) {
                this.log('Could not parse selected date: ' + selectedDate);
                return;
            }
            
            // Calculate weekly recurring dates
            let recurringDates = this.calculateRecurringDates(startDate);
            this.selectedDates = [startDate].concat(recurringDates);
            
            this.log('Generated recurring dates: ' + this.selectedDates.map(d => d.toDateString()).join(', '));
            
            // Update the UI to show recurring dates
            this.updateRecurringDatesDisplay();
        },
        
        parseDate: function(dateString) {
            // Clean the date string
            dateString = dateString.replace(/[^\d\/\-\.\s]/g, '').trim();
            
            // Try multiple date formats
            let formats = [
                /^(\d{4})-(\d{1,2})-(\d{1,2})$/, // YYYY-MM-DD
                /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/, // MM/DD/YYYY or DD/MM/YYYY
                /^(\d{1,2})-(\d{1,2})-(\d{4})$/, // MM-DD-YYYY or DD-MM-YYYY
                /^(\d{1,2})\.(\d{1,2})\.(\d{4})$/ // DD.MM.YYYY
            ];
            
            for (let i = 0; i < formats.length; i++) {
                let match = dateString.match(formats[i]);
                if (match) {
                    let year, month, day;
                    
                    if (i === 0) { // YYYY-MM-DD
                        [, year, month, day] = match;
                    } else { // Other formats - assume first number is day for EU format
                        [, day, month, year] = match;
                    }
                    
                    // Validate the date components
                    year = parseInt(year);
                    month = parseInt(month);
                    day = parseInt(day);
                    
                    if (year > 1900 && year < 2100 && month >= 1 && month <= 12 && day >= 1 && day <= 31) {
                        return new Date(year, month - 1, day);
                    }
                }
            }
            
            // Fallback to Date constructor
            let date = new Date(dateString);
            return isNaN(date.getTime()) ? null : date;
        },
        
        calculateRecurringDates: function(startDate) {
            let recurringDates = [];
            let weeks = this.config.recurringWeeks || 4;
            
            for (let i = 1; i <= weeks; i++) {
                let nextDate = new Date(startDate);
                nextDate.setDate(startDate.getDate() + (i * 7));
                recurringDates.push(nextDate);
            }
            
            return recurringDates;
        },
        
        updateRecurringDatesDisplay: function() {
            // Remove existing recurring dates display
            $('.deva-recurring-dates-display').remove();
            
            // Create new display
            let html = '<div class="deva-recurring-dates-display" style="background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #48733d; font-family: Arial, sans-serif;">';
            html += '<div style="font-size: 16px; font-weight: bold; color: #2d5a27; margin-bottom: 10px;">🔄 Weekly Recurring Package</div>';
            html += '<p style="margin: 5px 0; color: #333; font-size: 14px;">Your appointments will be scheduled for:</p>';
            html += '<ul style="margin: 10px 0; padding-left: 20px; color: #555;">';
            
            this.selectedDates.forEach((date, index) => {
                let dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                let dateStr = date.toLocaleDateString('en-US', dateOptions);
                let label = index === 0 ? ' <strong>(Selected)</strong>' : ' <em>(Week ' + index + ')</em>';
                html += '<li style="margin: 5px 0;">' + dateStr + label + '</li>';
            });
            
            html += '</ul>';
            html += '<p style="margin: 10px 0 0 0; font-size: 12px; color: #666; font-style: italic;">All ' + this.selectedDates.length + ' appointments will be scheduled with the same time and provider.</p>';
            html += '</div>';
            
            // Find the best place to insert the display
            let insertAfter = $('.amelia-calendar').first();
            if (insertAfter.length === 0) {
                insertAfter = $('.amelia-date-picker').first();
            }
            if (insertAfter.length === 0) {
                insertAfter = $('.el-calendar').first();
            }
            if (insertAfter.length === 0) {
                insertAfter = $('.amelia-step-booking').first();
            }
            if (insertAfter.length === 0) {
                insertAfter = $('.amelia-app-booking').first();
            }
            
            if (insertAfter.length > 0) {
                insertAfter.after(html);
            }
        },
        
        showRecurringNotice: function() {
            // Remove existing notice
            $('.deva-recurring-service-notice').remove();
            
            // Add notice about recurring service
            let html = '<div class="deva-recurring-service-notice" style="background: #fff3cd; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #ffc107; font-family: Arial, sans-serif;">';
            html += '<div style="font-size: 16px; font-weight: bold; color: #856404; margin-bottom: 8px;">📅 Recurring Service Package</div>';
            html += '<p style="margin: 0; color: #856404; font-size: 14px;">When you select your preferred date, we will automatically show you ' + (this.config.recurringWeeks || 4) + ' weekly appointments that will be scheduled for you.</p>';
            html += '</div>';
            
            // Find the best place to insert the notice
            let insertBefore = $('.amelia-step-booking').first();
            if (insertBefore.length === 0) {
                insertBefore = $('.amelia-app-booking .amelia-booking-form').first();
            }
            if (insertBefore.length === 0) {
                insertBefore = $('.amelia-app-booking').first();
            }
            
            if (insertBefore.length > 0) {
                insertBefore.prepend(html);
            }
        },
        
        hideRecurringNotice: function() {
            $('.deva-recurring-service-notice, .deva-recurring-dates-display').remove();
        },
        
        log: function(message) {
            if (this.config.debug) {
                console.log('[DEVA Amelia Recurring] ' + message);
            }
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        devaAmeliaRecurring.init();
    });
    
})(jQuery);