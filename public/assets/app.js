/**
 * BraVaa Fitness — Main JavaScript Application
 * Vanilla JS (DOM, Events, Client Validation, Fetch API / JSON Endpoint)
 * Student: NIYONKURU SHALOM (224011591)
 */

document.addEventListener('DOMContentLoaded', () => {
  const BASE_URL = getBaseUrl();
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  /* ── 1. Helper to determine base application URL ── */
  function getBaseUrl() {
    const metaBase = document.querySelector('meta[name="base-url"]')?.getAttribute('content');
    if (metaBase) return metaBase;
    const path = window.location.pathname;
    if (path.includes('/online-gym/public')) return '/online-gym/public';
    return '';
  }

  /* ── 2. Mobile Navigation Toggle ── */
  const navToggleBtn = document.getElementById('nav-toggle-btn');
  const navMenu = document.getElementById('nav-menu');

  if (navToggleBtn && navMenu) {
    navToggleBtn.addEventListener('click', () => {
      const isOpen = navMenu.classList.toggle('open');
      navToggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    // Close when clicking outside
    document.addEventListener('click', (e) => {
      if (!navToggleBtn.contains(e.target) && !navMenu.contains(e.target)) {
        navMenu.classList.remove('open');
        navToggleBtn.setAttribute('aria-expanded', 'false');
      }
    });
  }

  /* ── 3. Auto-Dismiss Flash Messages ── */
  const flash = document.querySelector('.flash');
  if (flash) {
    const closeBtn = flash.querySelector('.flash-close');
    if (closeBtn) {
      closeBtn.addEventListener('click', () => dismissFlash(flash));
    }

    setTimeout(() => {
      dismissFlash(flash);
    }, 4500);
  }

  function dismissFlash(el) {
    if (!el) return;
    el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
    el.style.opacity = '0';
    el.style.transform = 'translateY(-8px)';
    setTimeout(() => el.remove(), 400);
  }

  /* ── 4. Quick-Fill Demo Credentials on Login Page ── */
  document.querySelectorAll('.btn-demo-fill').forEach(btn => {
    btn.addEventListener('click', () => {
      const userField = document.getElementById('identifier');
      const passField = document.getElementById('password');
      if (userField && passField) {
        userField.value = btn.dataset.user || '';
        passField.value = btn.dataset.pass || '';
        userField.focus();
      }
    });
  });

  /* ── 5. Delete & Cancel Confirmation Dialogs ── */
  document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', (e) => {
      const label = form.dataset.label || 'this record';
      const msg = `Are you sure you want to proceed with: "${label}"?\n\nThis action cannot be undone.`;
      if (!window.confirm(msg)) {
        e.preventDefault();
      }
    });
  });

  /* ── 6. Client-Side Form Validation (Registration) ── */
  const regForm = document.getElementById('register-form');
  if (regForm) {
    const usernameInput = document.getElementById('reg-username');
    const emailInput = document.getElementById('reg-email');
    const passwordInput = document.getElementById('reg-password');

    const usernameError = document.getElementById('reg-username-error');
    const emailError = document.getElementById('reg-email-error');
    const passwordError = document.getElementById('reg-password-error');

    regForm.addEventListener('submit', (e) => {
      let isValid = true;

      // Validate Username
      if (!usernameInput.value.trim()) {
        usernameError.textContent = 'Please enter a username.';
        usernameInput.classList.add('is-invalid');
        isValid = false;
      } else if (usernameInput.value.trim().length < 3) {
        usernameError.textContent = 'Username must be at least 3 characters.';
        usernameInput.classList.add('is-invalid');
        isValid = false;
      } else {
        usernameError.textContent = '';
        usernameInput.classList.remove('is-invalid');
      }

      // Validate Email
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailInput.value.trim()) {
        emailError.textContent = 'Please enter your email address.';
        emailInput.classList.add('is-invalid');
        isValid = false;
      } else if (!emailRegex.test(emailInput.value.trim())) {
        emailError.textContent = 'Please enter a valid email address.';
        emailInput.classList.add('is-invalid');
        isValid = false;
      } else {
        emailError.textContent = '';
        emailInput.classList.remove('is-invalid');
      }

      // Validate Password
      if (!passwordInput.value) {
        passwordError.textContent = 'Please choose a password.';
        passwordInput.classList.add('is-invalid');
        isValid = false;
      } else if (passwordInput.value.length < 6) {
        passwordError.textContent = 'Password must be at least 6 characters.';
        passwordInput.classList.add('is-invalid');
        isValid = false;
      } else {
        passwordError.textContent = '';
        passwordInput.classList.remove('is-invalid');
      }

      if (!isValid) {
        e.preventDefault();
      }
    });
  }

  /* ── 7. DOM Live Search for Classes Table ── */
  setupTableFilter('classes-search-input', 'classes-table');
  setupTableFilter('trainers-search-input', 'trainers-table');
  setupTableFilter('bookings-search-input', 'bookings-table');
  setupTableFilter('users-search-input', 'users-table');

  function setupTableFilter(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);

    if (input && table) {
      input.addEventListener('input', () => {
        const query = input.value.toLowerCase().trim();
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
          const haystack = (row.dataset.search || row.textContent).toLowerCase();
          const match = query === '' || haystack.includes(query);
          row.classList.toggle('hidden', !match);
        });
      });
    }
  }

  /* ── 8. Async Fetch API Feature: Live Search for Classes (JSON Endpoint) ── */
  const homeSearchInput = document.getElementById('home-live-search');
  const homeSearchSpinner = document.getElementById('home-search-spinner');
  const homeClassesGrid = document.getElementById('home-classes-grid');

  if (homeSearchInput && homeClassesGrid) {
    let debounceTimer = null;

    homeSearchInput.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      const query = homeSearchInput.value.trim();

      if (homeSearchSpinner) homeSearchSpinner.style.display = 'inline-block';

      debounceTimer = setTimeout(() => {
        fetchClassesApi(query);
      }, 300);
    });

    async function fetchClassesApi(q) {
      try {
        const url = `${BASE_URL}/api/classes?q=${encodeURIComponent(q)}`;
        const res = await fetch(url, {
          headers: { 'Accept': 'application/json' }
        });

        if (!res.ok) throw new Error('Network error');
        const data = await res.json();

        if (data.success && Array.isArray(data.classes)) {
          renderHomeClasses(data.classes);
        }
      } catch (err) {
        console.error('Fetch error:', err);
      } finally {
        if (homeSearchSpinner) homeSearchSpinner.style.display = 'none';
      }
    }

    function renderHomeClasses(classes) {
      if (classes.length === 0) {
        homeClassesGrid.innerHTML = `
          <div class="empty-state-box" style="grid-column: 1 / -1;">
            <p>No matching fitness classes found for your search query.</p>
          </div>
        `;
        return;
      }

      homeClassesGrid.innerHTML = classes.map(c => {
        const pct = c.capacity > 0 ? Math.min(100, Math.round((c.booked_count / c.capacity) * 100)) : 0;
        const fillClass = pct >= 100 ? 'fill-full' : (pct >= 80 ? 'fill-warning' : 'fill-primary');
        const statusBadge = c.is_full 
          ? `<span class="badge-status badge-danger">FULL</span>`
          : `<span class="badge-status badge-success">${c.remaining_spots} SPOTS LEFT</span>`;

        const actionBtn = c.is_full
          ? `<button type="button" class="btn-card btn-disabled" disabled>Fully Booked</button>`
          : `<button type="button" class="btn-card btn-book-ajax" data-class-id="${c.class_id}" data-class-name="${escapeHtml(c.name)}">
               Book This Class &rarr;
             </button>`;

        return `
          <div class="class-card" data-class-id="${c.class_id}">
            <div class="class-card-header">
              <span class="class-category-badge">${escapeHtml(c.trainer_specialization)}</span>
              ${statusBadge}
            </div>
            <h3 class="class-title">${escapeHtml(c.name)}</h3>
            <div class="class-meta-info">
              <div class="meta-row">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span>${escapeHtml(c.formatted_time)}</span>
              </div>
              <div class="meta-row">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                <span>Coach: <strong>${escapeHtml(c.trainer_name)}</strong></span>
              </div>
            </div>
            <div class="capacity-bar-container">
              <div class="capacity-bar-labels">
                <span>Booked: ${c.booked_count} / ${c.capacity}</span>
                <span>${pct}%</span>
              </div>
              <div class="progress-track">
                <div class="progress-fill ${fillClass}" style="width: ${pct}%;"></div>
              </div>
            </div>
            <div class="class-card-footer">
              ${actionBtn}
            </div>
          </div>
        `;
      }).join('');

      bindBookingButtons();
    }
  }

  /* ── 9. Async Fetch API Feature: Instant Modal Booking ── */
  const bookingModal = document.getElementById('booking-modal');
  const modalClassTitle = document.getElementById('modal-class-title');
  const modalClassDetails = document.getElementById('modal-class-details');
  const modalAlertBox = document.getElementById('modal-alert-box');
  const modalConfirmBtn = document.getElementById('modal-confirm-btn');
  const modalCancelBtn = document.getElementById('modal-cancel-btn');
  const modalCloseBtn = document.getElementById('modal-close-btn');
  const modalSpinner = document.getElementById('modal-spinner');

  let activeBookingClassId = null;
  let activeCardElement = null;

  function bindBookingButtons() {
    document.querySelectorAll('.btn-book-ajax').forEach(btn => {
      btn.removeEventListener('click', onBookBtnClick);
      btn.addEventListener('click', onBookBtnClick);
    });
  }

  function onBookBtnClick(e) {
    const btn = e.currentTarget;
    activeBookingClassId = btn.dataset.classId;
    activeCardElement = btn.closest('.class-card');
    const className = btn.dataset.className || 'this fitness class';

    if (bookingModal) {
      modalClassTitle.textContent = 'Confirm Reservation';
      modalClassDetails.innerHTML = `You are about to reserve a spot in <strong>${escapeHtml(className)}</strong>. Are you ready?`;
      modalAlertBox.style.display = 'none';
      modalAlertBox.className = 'modal-alert';
      modalConfirmBtn.disabled = false;
      modalConfirmBtn.querySelector('.btn-text').textContent = 'Confirm Booking';
      bookingModal.style.display = 'flex';
    }
  }

  bindBookingButtons();

  if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);
  if (modalCancelBtn) modalCancelBtn.addEventListener('click', closeModal);

  if (bookingModal) {
    bookingModal.addEventListener('click', (e) => {
      if (e.target === bookingModal) closeModal();
    });
  }

  function closeModal() {
    if (bookingModal) bookingModal.style.display = 'none';
    activeBookingClassId = null;
  }

  if (modalConfirmBtn) {
    modalConfirmBtn.addEventListener('click', async () => {
      if (!activeBookingClassId) return;

      modalConfirmBtn.disabled = true;
      if (modalSpinner) modalSpinner.style.display = 'inline-block';
      modalAlertBox.style.display = 'none';

      try {
        const response = await fetch(`${BASE_URL}/api/bookings/create`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': CSRF_TOKEN
          },
          body: JSON.stringify({
            class_id: parseInt(activeBookingClassId, 10),
            csrf_token: CSRF_TOKEN
          })
        });

        const result = await response.json();

        if (response.status === 401 && result.require_login) {
          window.location.href = `${BASE_URL}/login`;
          return;
        }

        if (result.success) {
          modalAlertBox.className = 'modal-alert alert-success';
          modalAlertBox.style.background = '#ecfdf5';
          modalAlertBox.style.color = '#065f46';
          modalAlertBox.style.display = 'block';
          modalAlertBox.textContent = result.message || 'Booking confirmed!';

          // Update active card in DOM
          if (activeCardElement) {
            const footer = activeCardElement.querySelector('.class-card-footer');
            if (footer) {
              footer.innerHTML = `
                <span class="btn-card btn-already-booked">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                  Already Booked
                </span>
              `;
            }
          }

          setTimeout(() => {
            closeModal();
            window.location.href = `${BASE_URL}/bookings`;
          }, 1200);

        } else {
          modalAlertBox.className = 'modal-alert alert-error';
          modalAlertBox.style.background = '#fef2f2';
          modalAlertBox.style.color = '#991b1b';
          modalAlertBox.style.display = 'block';
          modalAlertBox.textContent = result.message || 'Booking failed.';
          modalConfirmBtn.disabled = false;
        }

      } catch (err) {
        modalAlertBox.className = 'modal-alert alert-error';
        modalAlertBox.style.background = '#fef2f2';
        modalAlertBox.style.color = '#991b1b';
        modalAlertBox.style.display = 'block';
        modalAlertBox.textContent = 'A network error occurred. Please try again.';
        modalConfirmBtn.disabled = false;
      } finally {
        if (modalSpinner) modalSpinner.style.display = 'none';
      }
    });
  }

  // Utility to prevent XSS in client-rendered HTML
  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

});
