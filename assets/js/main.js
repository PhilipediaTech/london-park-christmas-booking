/**
 * London Community Park Christmas Event Booking System
 * Main JavaScript File - Complete with Slider
 */

// ===== SLIDER VARIABLES (GLOBAL SCOPE) =====
let currentSlide = 0;
let slides = [];
let dots = [];
let autoSlideInterval;

// ===== SLIDER FUNCTIONS (GLOBAL SCOPE) =====
function changeSlide(direction) {
  if (!slides.length) return;

  // Remove active class from current slide and dot
  slides[currentSlide].classList.remove("active");
  dots[currentSlide].classList.remove("active");

  // Calculate new slide index
  currentSlide = currentSlide + direction;

  // Loop around
  if (currentSlide >= slides.length) {
    currentSlide = 0;
  } else if (currentSlide < 0) {
    currentSlide = slides.length - 1;
  }

  // Add active class to new slide and dot
  slides[currentSlide].classList.add("active");
  dots[currentSlide].classList.add("active");

  // Reset auto-slide timer
  resetAutoSlide();
}

function goToSlide(index) {
  if (!slides.length || index === currentSlide) return;

  // Remove active class from current slide and dot
  slides[currentSlide].classList.remove("active");
  dots[currentSlide].classList.remove("active");

  // Set new slide
  currentSlide = index;

  // Add active class to new slide and dot
  slides[currentSlide].classList.add("active");
  dots[currentSlide].classList.add("active");

  // Reset auto-slide timer
  resetAutoSlide();
}

function startAutoSlide() {
  if (!slides.length) return;
  autoSlideInterval = setInterval(function () {
    changeSlide(1);
  }, 5000); // Change slide every 5 seconds
}

function stopAutoSlide() {
  clearInterval(autoSlideInterval);
}

function resetAutoSlide() {
  stopAutoSlide();
  startAutoSlide();
}

function initSlider() {
  slides = document.querySelectorAll(".slide");
  dots = document.querySelectorAll(".dot");

  if (slides.length === 0) return; // Exit if no slider on page

  // Start auto-slide
  startAutoSlide();

  // Pause on hover
  const sliderContainer = document.querySelector(".slider-container");
  if (sliderContainer) {
    sliderContainer.addEventListener("mouseenter", stopAutoSlide);
    sliderContainer.addEventListener("mouseleave", startAutoSlide);
  }

  console.log(
    "🎄 Hero Slider Initialized with " + slides.length + " slides! 🎄"
  );
}

// ===== MOBILE MENU TOGGLE =====
function toggleMobileMenu() {
  const navLinks = document.getElementById("navLinks");
  const menuBtn = document.querySelector(".mobile-menu-btn");

  if (navLinks) {
    navLinks.classList.toggle("active");
    if (menuBtn) menuBtn.classList.toggle("active");
  }
}

// ===== DOM READY =====
document.addEventListener("DOMContentLoaded", function () {
  // Initialize slider
  initSlider();

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
      this.style.transform = "translateY(-2px)";
    });
    btn.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0)";
    });
  });

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

  console.log("🎄 London Community Park - System Loaded! 🎄");
});

// ===== KEYBOARD NAVIGATION FOR SLIDER =====
document.addEventListener("keydown", function (e) {
  if (!slides.length) return;

  if (e.key === "ArrowLeft") {
    changeSlide(-1);
  } else if (e.key === "ArrowRight") {
    changeSlide(1);
  }
});

// ===== TOUCH SWIPE SUPPORT FOR MOBILE =====
let touchStartX = 0;
let touchEndX = 0;

document.addEventListener("touchstart", function (e) {
  const sliderContainer = document.querySelector(".slider-container");
  if (sliderContainer && sliderContainer.contains(e.target)) {
    touchStartX = e.changedTouches[0].screenX;
  }
});

document.addEventListener("touchend", function (e) {
  const sliderContainer = document.querySelector(".slider-container");
  if (sliderContainer && sliderContainer.contains(e.target)) {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
  }
});

function handleSwipe() {
  if (touchEndX < touchStartX - 50) {
    // Swipe left
    changeSlide(1);
  }
  if (touchEndX > touchStartX + 50) {
    // Swipe right
    changeSlide(-1);
  }
}

// ===== HELPER FUNCTIONS =====
function showError(field, message) {
  field.style.borderColor = "#D4A5A5";
  field.style.boxShadow = "0 0 0 3px rgba(212, 165, 165, 0.1)";

  let errorMsg = field.parentNode.querySelector(".error-message");
  if (!errorMsg) {
    errorMsg = document.createElement("span");
    errorMsg.className = "error-message";
    errorMsg.style.cssText =
      "color: #D4A5A5; font-size: 0.85rem; display: block; margin-top: 5px;";
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

// Format card number with spaces
document.addEventListener("DOMContentLoaded", function () {
  const cardNumberInput = document.getElementById("card_number");
  if (cardNumberInput) {
    cardNumberInput.addEventListener("input", function (e) {
      let value = e.target.value.replace(/\s+/g, "").replace(/[^0-9]/gi, "");
      let formatted = value.match(/.{1,4}/g)?.join(" ") || value;
      e.target.value = formatted;
    });
  }

  // Format expiry date
  const expiryInput = document.getElementById("expiry_date");
  if (expiryInput) {
    expiryInput.addEventListener("input", function (e) {
      let value = e.target.value.replace(/\D/g, "");
      if (value.length >= 2) {
        value = value.substring(0, 2) + "/" + value.substring(2, 4);
      }
      e.target.value = value;
    });
  }

  // Only numbers for CVV
  const cvvInput = document.getElementById("cvv");
  if (cvvInput) {
    cvvInput.addEventListener("input", function (e) {
      e.target.value = e.target.value.replace(/\D/g, "");
    });
  }
});
