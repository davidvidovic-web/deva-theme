/**
 * DEVA Account Page JavaScript
 */

// Global variables for account functionality
const devaAccount = {
  init: function () {
    this.initializeTooltips();
    this.initializeProfileForm();
  },

  bindEvents: function () {
    // Profile form related events only
  },























  showNotification: function (message, type = "info") {
    const notification = document.createElement("div");
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
    notification.addEventListener("click", () => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    });
  },

  initializeTooltips: function () {
    // Add tooltips for edit icons
    const editIcons = document.querySelectorAll(".deva-edit");
    editIcons.forEach((icon) => {
      icon.title = "Click to edit";
    });

    // Add tooltips for disabled buttons
    const disabledButtons = document.querySelectorAll(
      ".deva-call-button.deva-disabled"
    );
    disabledButtons.forEach((button) => {
      button.title = devaAccountTranslations.scheduleSessionFirst || "Schedule your session first";
    });
  },

  initializeProfileForm: function () {
    const profileForm = document.getElementById("deva-profile-form");

    if (profileForm) {
      // Store initial form values
      this.storeOriginalFormValues();

      profileForm.addEventListener("submit", (e) => {
        e.preventDefault();

        const formData = new FormData(profileForm);
        formData.append("action", "update_deva_profile");

        // Password validation
        const password = formData.get("user_pass");
        const confirmPassword = formData.get("user_pass_confirm");

        if (password && password !== confirmPassword) {
          this.showNotification(
            "Passwords don't match. Please try again.",
            "error"
          );
          return;
        }

        // Show loading state
        const submitBtn = profileForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML =
          '<span class="dashicons dashicons-update-alt"></span> Updating...';
        submitBtn.disabled = true;

        // Get AJAX URL from account data or construct it
        const ajaxUrl =
          window.devaAccountData?.ajax_url || "/wp-admin/admin-ajax.php";

        // Send AJAX request
        fetch(ajaxUrl, {
          method: "POST",
          body: formData,
        })
          .then((response) => {
            if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
          })
          .then((data) => {
            if (data.success) {
              // Show success message
              this.showNotification(
                data.data.message || devaAccountTranslations.profileSaved || "Profile updated successfully!",
                devaAccountTranslations.success || "success"
              );

              // Reset password fields
              const passwordField = document.getElementById("user_pass");
              const confirmPasswordField =
                document.getElementById("user_pass_confirm");
              if (passwordField) passwordField.value = "";
              if (confirmPasswordField) confirmPasswordField.value = "";

              // Update stored original values
              this.storeOriginalFormValues();

              // Close settings panel after successful update
              setTimeout(() => {
                const settingsSection = document.getElementById(
                  "deva-settings-section"
                );
                if (
                  settingsSection &&
                  settingsSection.classList.contains("deva-settings-open")
                ) {
                  const event = { preventDefault: () => {} };
                  toggleSettings(event);
                }
              }, 1500);
            } else {
              // Show error message
              this.showNotification(
                data.data?.message ||
                  devaAccountTranslations.profileError || "Error updating profile. Please try again.",
                devaAccountTranslations.error || "error"
              );
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            this.showNotification(
              devaAccountTranslations.networkError || "Network error. Please check your connection and try again.",
              devaAccountTranslations.error || "error"
            );
          })
          .finally(() => {
            // Restore button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
          });
      });
    }
  },

  // Store original form values for cancel functionality
  storeOriginalFormValues: function () {
    const profileForm = document.getElementById("deva-profile-form");
    if (!profileForm) return;

    this.originalFormValues = {};
    const formElements = profileForm.querySelectorAll(
      "input, textarea, select"
    );

    formElements.forEach((element) => {
      if (element.type === "checkbox") {
        this.originalFormValues[element.name] = element.checked;
      } else if (element.type !== "password") {
        // Don't store passwords
        this.originalFormValues[element.name] = element.value;
      }
    });
  },

  // Check if form has been modified
  isFormModified: function () {
    const profileForm = document.getElementById("deva-profile-form");
    if (!profileForm || !this.originalFormValues) return false;

    const formElements = profileForm.querySelectorAll(
      "input, textarea, select"
    );

    for (let element of formElements) {
      if (element.type === "password") continue; // Skip password fields

      const currentValue =
        element.type === "checkbox" ? element.checked : element.value;
      const originalValue = this.originalFormValues[element.name];

      if (currentValue !== originalValue) {
        return true;
      }
    }

    return false;
  },

  // Reset form to original values
  resetProfileForm: function () {
    const profileForm = document.getElementById("deva-profile-form");
    if (!profileForm || !this.originalFormValues) return;

    const formElements = profileForm.querySelectorAll(
      "input, textarea, select"
    );

    formElements.forEach((element) => {
      if (element.type === "password") {
        element.value = "";
      } else if (element.type === "checkbox") {
        element.checked = this.originalFormValues[element.name] || false;
      } else {
        element.value = this.originalFormValues[element.name] || "";
      }
    });
  },








};

function cancelProfileEdit(event) {
  event.preventDefault();

  const profileForm = document.getElementById("deva-profile-form");
  if (!profileForm) return;

  // Confirm cancellation if form has been modified
  if (devaAccount.isFormModified()) {
    if (
      !confirm(
        "Are you sure you want to cancel? Any unsaved changes will be lost."
      )
    ) {
      return;
    }
  }

  // Reset form to original values
  devaAccount.resetProfileForm();

  // Close settings panel
  toggleSettings(event);
}

function toggleSettings(event) {
  event.preventDefault();

  const settingsSection = document.getElementById("deva-settings-section");
  const settingsButton = document.querySelector(".deva-settings-toggle");
  const arrow = settingsButton
    ? settingsButton.querySelector(".deva-settings-arrow")
    : null;

  if (settingsSection.classList.contains("deva-settings-open")) {
    // Hide settings
    settingsSection.classList.remove("deva-settings-open");
    if (arrow) {
      arrow.classList.remove("dashicons-arrow-up");
      arrow.classList.add("dashicons-arrow-down");
    }

    // Reset form when closing
    devaAccount.resetProfileForm();
  } else {
    // Show settings
    settingsSection.classList.add("deva-settings-open");
    if (arrow) {
      arrow.classList.remove("dashicons-arrow-down");
      arrow.classList.add("dashicons-arrow-up");
    }

    // Store original form values
    devaAccount.storeOriginalFormValues();

    // Smooth scroll to settings section after animation
    setTimeout(() => {
      settingsSection.scrollIntoView({ behavior: "smooth", block: "start" });
    }, 300);
  }
}

function cancelProfileEdit(event) {
  event.preventDefault();

  const profileForm = document.getElementById("deva-profile-form");
  if (!profileForm) return;

  // Confirm cancellation if form has been modified
  if (devaAccount.isFormModified()) {
    if (
      !confirm(
        "Are you sure you want to cancel? Any unsaved changes will be lost."
      )
    ) {
      return;
    }
  }

  // Reset form to original values
  devaAccount.resetProfileForm();

  // Close settings panel
  toggleSettings(event);
}

function toggleSettings(event) {
  event.preventDefault();

  const settingsSection = document.getElementById("deva-settings-section");
  const settingsButton = document.querySelector(".deva-settings-toggle");
  const arrow = settingsButton
    ? settingsButton.querySelector(".deva-settings-arrow")
    : null;

  if (settingsSection.classList.contains("deva-settings-open")) {
    // Hide settings
    settingsSection.classList.remove("deva-settings-open");
    if (arrow) {
      arrow.classList.remove("dashicons-arrow-up");
      arrow.classList.add("dashicons-arrow-down");
    }

    // Reset form when closing
    devaAccount.resetProfileForm();
  } else {
    // Show settings
    settingsSection.classList.add("deva-settings-open");
    if (arrow) {
      arrow.classList.remove("dashicons-arrow-down");
      arrow.classList.add("dashicons-arrow-up");
    }

    // Store original form values
    devaAccount.storeOriginalFormValues();

    // Smooth scroll to settings section after animation
    setTimeout(() => {
      settingsSection.scrollIntoView({ behavior: "smooth", block: "start" });
    }, 300);
  }
}



function togglePasswordVisibility(fieldId) {
  const field = document.getElementById(fieldId);
  if (!field) return;

  const toggle = field.parentElement.querySelector(".deva-password-toggle");
  if (!toggle) return;

  const showText = toggle.querySelector(".show-text");
  const hideText = toggle.querySelector(".hide-text");

  if (!showText || !hideText) return;

  if (field.type === "password") {
    field.type = "text";
    showText.style.display = "none";
    hideText.style.display = "inline";
  } else {
    field.type = "password";
    showText.style.display = "inline";
    hideText.style.display = "none";
  }
}

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
  devaAccount.init();
});
