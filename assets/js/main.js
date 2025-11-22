/**
 * London Community Park Christmas Event Booking System
 * Main JavaScript File
 */

// Wait for DOM to be fully loaded
document.addEventListener("DOMContentLoaded", function () {
  // Auto-hide alerts after 5 seconds
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach(function (alert) {
    setTimeout(function () {
      alert.style.transition = "opacity 0.5s ease";
      alert.style.opacity = "0";
      setTimeout(function () {
        alert.remove();
      }, 500);
    }, 5000);
  });

  // Confirm delete actions
  const deleteButtons = document.querySelectorAll(
    ".btn-delete, [data-confirm]"
  );
  deleteButtons.forEach(function (button) {
    button.addEventListener("click", function (e) {
      const message =
        this.getAttribute("data-confirm") ||
        "Are you sure you want to delete this item?";
      if (!confirm(message)) {
        e.preventDefault();
      }
    });
  });

  // Form validation
  const forms = document.querySelectorAll("form[data-validate]");
  forms.forEach(function (form) {
    form.addEventListener("submit", function (e) {
      const requiredFields = form.querySelectorAll("[required]");
      let isValid = true;

      requiredFields.forEach(function (field) {
        if (!field.value.trim()) {
          isValid = false;
          field.style.borderColor = "#c41e3a";

          // Add error message if not exists
          let errorMsg = field.parentNode.querySelector(".error-message");
          if (!errorMsg) {
            errorMsg = document.createElement("span");
            errorMsg.className = "error-message";
            errorMsg.style.color = "#c41e3a";
            errorMsg.style.fontSize = "0.85rem";
            errorMsg.textContent = "This field is required";
            field.parentNode.appendChild(errorMsg);
          }
        } else {
          field.style.borderColor = "#ddd";
          const errorMsg = field.parentNode.querySelector(".error-message");
          if (errorMsg) {
            errorMsg.remove();
          }
        }
      });

      if (!isValid) {
        e.preventDefault();
      }
    });
  });

  // Password match validation
  const passwordConfirm = document.querySelector(
    'input[name="confirm_password"]'
  );
  if (passwordConfirm) {
    passwordConfirm.addEventListener("input", function () {
      const password = document.querySelector('input[name="password"]');
      if (password.value !== this.value) {
        this.setCustomValidity("Passwords do not match");
      } else {
        this.setCustomValidity("");
      }
    });
  }

  // Ticket quantity calculator
  const ticketInputs = document.querySelectorAll(".ticket-quantity");
  if (ticketInputs.length > 0) {
    ticketInputs.forEach(function (input) {
      input.addEventListener("change", calculateTotal);
    });
  }

  // Calculate total for booking
  function calculateTotal() {
    let total = 0;
    let totalTickets = 0;

    ticketInputs.forEach(function (input) {
      const quantity = parseInt(input.value) || 0;
      const price = parseFloat(input.getAttribute("data-price")) || 0;
      total += quantity * price;
      totalTickets += quantity;
    });

    const totalElement = document.getElementById("total-amount");
    const ticketCountElement = document.getElementById("ticket-count");

    if (totalElement) {
      totalElement.textContent = "£" + total.toFixed(2);
    }
    if (ticketCountElement) {
      ticketCountElement.textContent = totalTickets;
    }

    // Check max tickets (8 per booking)
    if (totalTickets > 8) {
      alert("Maximum 8 tickets per booking");
      event.target.value = 0;
      calculateTotal();
    }
  }

  // Image preview for file uploads
  const imageInput = document.querySelector(
    'input[type="file"][accept*="image"]'
  );
  if (imageInput) {
    imageInput.addEventListener("change", function () {
      const preview = document.getElementById("image-preview");
      if (preview && this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
          preview.src = e.target.result;
          preview.style.display = "block";
        };
        reader.readAsDataURL(this.files[0]);
      }
    });
  }

  // Mobile menu toggle
  const menuToggle = document.querySelector(".menu-toggle");
  const navLinks = document.querySelector(".nav-links");
  if (menuToggle && navLinks) {
    menuToggle.addEventListener("click", function () {
      navLinks.classList.toggle("active");
    });
  }

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute("href"));
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }
    });
  });

  // Add Christmas sparkle effect on buttons
  const buttons = document.querySelectorAll(".btn");
  buttons.forEach(function (btn) {
    btn.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-2px) scale(1.02)";
    });
    btn.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0) scale(1)";
    });
  });

  console.log(
    "🎄 London Community Park - Christmas Event Booking System Loaded! 🎄"
  );
});

/**
 * Format currency
 * @param {number} amount - The amount to format
 * @returns {string} - Formatted currency string
 */
function formatCurrency(amount) {
  return "£" + parseFloat(amount).toFixed(2);
}

/**
 * Show loading spinner
 * @param {HTMLElement} element - The element to show loading in
 */
function showLoading(element) {
  element.innerHTML = '<div class="loading">Loading... 🎅</div>';
}

/**
 * Hide loading spinner
 * @param {HTMLElement} element - The element to hide loading from
 * @param {string} content - The content to replace loading with
 */
function hideLoading(element, content) {
  element.innerHTML = content;
}
