// =========================================
// NEXUS TOP UP - MAIN JAVASCRIPT
// =========================================

document.addEventListener('DOMContentLoaded', () => {

  // ---- Navbar scroll effect ----
  const navbar = document.querySelector('.navbar');
  if (navbar) {
    window.addEventListener('scroll', () => {
      navbar.classList.toggle('scrolled', window.scrollY > 20);
    });
  }

  // ---- Flash message auto-dismiss ----
  const flash = document.querySelector('.flash-message');
  if (flash) {
    setTimeout(() => flash.remove(), 4000);
  }

  // ---- Package selection ----
  const packageCards = document.querySelectorAll('.package-card');
  const packageInput = document.getElementById('selected_package_id');

  packageCards.forEach(card => {
    card.addEventListener('click', () => {
      packageCards.forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');
      if (packageInput) {
        packageInput.value = card.dataset.packageId;
        updateOrderSummary();
      }
    });
  });

  // ---- Payment method selection ----
  const paymentCards = document.querySelectorAll('.payment-card');
  const paymentInput = document.getElementById('selected_payment_id');

  paymentCards.forEach(card => {
    card.addEventListener('click', () => {
      paymentCards.forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');
      if (paymentInput) {
        paymentInput.value = card.dataset.paymentId;
        updateOrderSummary();
      }
    });
  });

  // ---- Order summary update ----
  function updateOrderSummary() {
    const selectedPackage = document.querySelector('.package-card.selected');
    const selectedPayment = document.querySelector('.payment-card.selected');

    const summaryPackage = document.getElementById('summary-package');
    const summaryFee = document.getElementById('summary-fee');
    const summaryTotal = document.getElementById('summary-total');

    if (selectedPackage && summaryPackage) {
      const price = parseFloat(selectedPackage.dataset.price) || 0;
      const name = selectedPackage.dataset.name;
      const fee = selectedPayment ? parseFloat(selectedPayment.dataset.fee) || 0 : 0;
      const feeType = selectedPayment ? selectedPayment.dataset.feeType : 'fixed';
      
      let feeAmount = feeType === 'percent' ? price * (fee / 100) : fee;
      let total = price + feeAmount;

      summaryPackage.textContent = name + ' - ' + formatRupiah(price);
      if (summaryFee) summaryFee.textContent = formatRupiah(feeAmount);
      if (summaryTotal) summaryTotal.textContent = formatRupiah(total);
    }
  }

  // ---- Format Rupiah ----
  function formatRupiah(amount) {
    return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
  }

  // ---- Modals ----
  window.openModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
  };

  window.closeModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.remove('active');
      document.body.style.overflow = '';
    }
  };

  // Close modal on overlay click
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
      }
    });
  });

  // ---- Scroll reveal animations ----
  const revealElements = document.querySelectorAll('.reveal');
  if (revealElements.length > 0) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('revealed');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    revealElements.forEach(el => observer.observe(el));
  }

  // ---- Smooth counter animation ----
  const counters = document.querySelectorAll('[data-count]');
  counters.forEach(counter => {
    const target = parseInt(counter.dataset.count);
    const suffix = counter.dataset.suffix || '';
    let current = 0;
    const duration = 1500;
    const increment = target / (duration / 16);

    const observer = new IntersectionObserver((entries) => {
      if (entries[0].isIntersecting) {
        const timer = setInterval(() => {
          current = Math.min(current + increment, target);
          counter.textContent = Math.floor(current).toLocaleString('id-ID') + suffix;
          if (current >= target) clearInterval(timer);
        }, 16);
        observer.unobserve(counter);
      }
    });

    observer.observe(counter);
  });

  // ---- Checkout form validation ----
  const checkoutForm = document.getElementById('checkout-form');
  if (checkoutForm) {
    checkoutForm.addEventListener('submit', (e) => {
      const gameUserId = document.getElementById('game_user_id')?.value?.trim();
      const packageId = document.getElementById('selected_package_id')?.value;
      const paymentId = document.getElementById('selected_payment_id')?.value;

      if (!gameUserId) {
        e.preventDefault();
        showNotif('Masukkan User ID game kamu!', 'error');
        return;
      }

      if (!packageId) {
        e.preventDefault();
        showNotif('Pilih paket top up terlebih dahulu!', 'error');
        return;
      }

      if (!paymentId) {
        e.preventDefault();
        showNotif('Pilih metode pembayaran!', 'error');
        return;
      }
    });
  }

  // ---- Show notification ----
  window.showNotif = function(message, type = 'info') {
    const existing = document.querySelector('.dynamic-flash');
    if (existing) existing.remove();

    const notif = document.createElement('div');
    notif.className = `flash-message flash-${type} dynamic-flash`;
    notif.innerHTML = `
      <span>${type === 'error' ? '✕' : '✓'}</span>
      <span>${message}</span>
    `;
    document.body.appendChild(notif);
    setTimeout(() => notif.remove(), 4000);
  };

  // ---- Mobile menu ----
  const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
  const mobileNav = document.querySelector('.mobile-nav');
  if (mobileMenuBtn && mobileNav) {
    mobileMenuBtn.addEventListener('click', () => {
      mobileNav.classList.toggle('open');
    });
  }

  // ---- Copy to clipboard ----
  window.copyText = function(text) {
    navigator.clipboard.writeText(text).then(() => {
      showNotif('Berhasil disalin!', 'success');
    });
  };

});
