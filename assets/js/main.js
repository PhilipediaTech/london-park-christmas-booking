/**
 * London Community Park Christmas Event Booking System
 * Main JavaScript File - Updated
 */

// Mobile Menu Toggle Function
function toggleMobileMenu() {
  const navLinks = document.getElementById("navLinks");
  const menuBtn = document.querySelector(".mobile-menu-btn");

  if (navLinks) {
    navLinks.classList.toggle("active");
    menuBtn.classList.toggle("active");
  }
}

// Close mobile menu when clicking outside
document.addEventListener("click", function (e) {
  const navLinks = document.getElementById("navLinks");
  const menuBtn = document.querySelector(".mobile-menu-btn");

  if (navLinks && navLinks.classList.contains("active")) {
    if (
      !e.target.closest(".nav-links") &&
      !e.target.closest(".mobile-menu-btn")
    ) {
      navLinks.classList.remove("active");
      if (menuBtn) menuBtn.classList.remove("active");
    }
  }
});

// Close mobile menu when window is resized to desktop
window.addEventListener("resize", function () {
  const navLinks = document.getElementById("navLinks");
  const menuBtn = document.querySelector(".mobile-menu-btn");

  if (window.innerWidth > 768 && navLinks) {
    navLinks.classList.remove("active");
    if (menuBtn) menuBtn.classList.remove("active");
  }
});

// DOM Ready
document.addEventListener("DOMContentLoaded", function () {
  // Auto-hide alerts after 5 seconds
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach(function (alert) {
    setTimeout(function () {
      alert.style.transition = "opacity 0.5s ease, transform 0.5s ease";
      alert.style.opacity = "0";
      alert.style.transform = "translateY(-10px)";
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
        removeError(field);

        if (!field.value.trim()) {
          isValid = false;
          showError(field, "This field is required");
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
      if (password && password.value !== this.value) {
        this.setCustomValidity("Passwords do not match");
      } else {
        this.setCustomValidity("");
      }
    });
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

  // Add hover effects to buttons
  const buttons = document.querySelectorAll(".btn");
  buttons.forEach(function (btn) {
    btn.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-3px)";
    });
    btn.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0)";
    });
  });

  console.log("🎄 London Community Park - System Loaded! 🎄");
});

// Helper Functions
function showError(field, message) {
  field.style.borderColor = "#c41e3a";
  field.style.boxShadow = "0 0 0 3px rgba(196, 30, 58, 0.1)";

  let errorMsg = field.parentNode.querySelector(".error-message");
  if (!errorMsg) {
    errorMsg = document.createElement("span");
    errorMsg.className = "error-message";
    errorMsg.style.cssText =
      "color: #c41e3a; font-size: 0.85rem; display: block; margin-top: 5px;";
    field.parentNode.appendChild(errorMsg);
  }
  errorMsg.textContent = message;
}

function removeError(field) {
  field.style.borderColor = "#e0e0e0";
  field.style.boxShadow = "none";
  const errorMsg = field.parentNode.querySelector(".error-message");
  if (errorMsg) {
    errorMsg.remove();
  }
}

function formatCurrency(amount) {
  return "£" + parseFloat(amount).toFixed(2);
}
