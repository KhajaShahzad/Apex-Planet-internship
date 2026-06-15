/**
 * assets/js/main.js
 * UserHub — Client-Side Logic
 * - Mobile nav toggle
 * - Delete confirmation modal
 * - Password visibility toggle
 * - Password strength indicator
 * - File upload preview
 * - Form validation feedback
 */

document.addEventListener('DOMContentLoaded', () => {

  /* ── Mobile Nav Toggle ──────────────────────────────────── */
  const navToggle = document.getElementById('navToggle');
  const navLinks  = document.getElementById('navLinks');
  if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
      navLinks.classList.toggle('open');
      const isOpen = navLinks.classList.contains('open');
      navToggle.setAttribute('aria-expanded', isOpen);
    });
    // Close on outside click
    document.addEventListener('click', (e) => {
      if (!navToggle.contains(e.target) && !navLinks.contains(e.target)) {
        navLinks.classList.remove('open');
      }
    });
  }

  /* ── Delete Confirmation Modal ──────────────────────────── */
  const deleteModal    = document.getElementById('deleteModal');
  const deleteForm     = document.getElementById('deleteForm');
  const deleteUserName = document.getElementById('deleteUserName');
  const deleteUserId   = document.getElementById('deleteUserId');
  const cancelDelete   = document.getElementById('cancelDelete');

  document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', () => {
      const id   = btn.dataset.id;
      const name = btn.dataset.name;
      if (deleteUserName) deleteUserName.textContent = name;
      if (deleteUserId)   deleteUserId.value = id;
      if (deleteModal)    deleteModal.classList.add('open');
    });
  });

  if (cancelDelete) {
    cancelDelete.addEventListener('click', () => {
      deleteModal.classList.remove('open');
    });
  }
  if (deleteModal) {
    deleteModal.addEventListener('click', (e) => {
      if (e.target === deleteModal) deleteModal.classList.remove('open');
    });
  }

  /* ── Password Visibility Toggle ─────────────────────────── */
  document.querySelectorAll('.toggle-pw').forEach(btn => {
    btn.addEventListener('click', () => {
      const targetId = btn.dataset.target;
      const input    = document.getElementById(targetId);
      if (!input) return;
      input.type = input.type === 'password' ? 'text' : 'password';
      btn.textContent = input.type === 'password' ? '👁' : '🙈';
    });
  });

  /* ── Password Strength Indicator ────────────────────────── */
  const pwInput    = document.getElementById('password');
  const pwStrength = document.getElementById('pwStrength');

  function checkStrength(pw) {
    let score = 0;
    if (pw.length >= 8)  score++;
    if (pw.length >= 12) score++;
    if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) score++;
    if (/\d/.test(pw))   score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;
    return score;
  }

  if (pwInput && pwStrength) {
    pwInput.addEventListener('input', () => {
      const val   = pwInput.value;
      const score = checkStrength(val);
      pwStrength.className = 'pw-strength';
      if (val.length === 0) return;
      if (score <= 2)      pwStrength.classList.add('weak');
      else if (score <= 3) pwStrength.classList.add('medium');
      else                 pwStrength.classList.add('strong');
    });
  }

  /* ── Profile Picture Preview ────────────────────────────── */
  const picInput    = document.getElementById('profile_picture');
  const avatarImg   = document.getElementById('avatarImg');
  const avatarInit  = document.getElementById('avatarInitial');
  const fileName    = document.getElementById('fileName');
  const fileLabel   = document.getElementById('fileLabel');

  if (picInput) {
    picInput.addEventListener('change', () => {
      const file = picInput.files[0];
      if (!file) return;

      // Validate type client-side
      const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
      if (!allowed.includes(file.type)) {
        alert('❌ Only JPG, PNG, GIF, and WEBP images are allowed.');
        picInput.value = '';
        return;
      }
      // Validate size client-side
      if (file.size > 2 * 1024 * 1024) {
        alert('❌ Image must be smaller than 2 MB.');
        picInput.value = '';
        return;
      }

      if (fileName) fileName.textContent = file.name;

      // Live preview
      const reader = new FileReader();
      reader.onload = (e) => {
        if (avatarImg) {
          avatarImg.src = e.target.result;
        } else if (avatarInit) {
          // Replace initials div with an img
          const img = document.createElement('img');
          img.src = e.target.result;
          img.id  = 'avatarImg';
          img.alt = 'Preview';
          avatarInit.replaceWith(img);
        }
      };
      reader.readAsDataURL(file);
    });
  }

  /* ── Auto-dismiss Flash Messages ────────────────────────── */
  document.querySelectorAll('.flash').forEach(flash => {
    setTimeout(() => {
      flash.style.transition = 'opacity .5s ease, max-height .5s ease';
      flash.style.opacity    = '0';
      flash.style.maxHeight  = '0';
      flash.style.overflow   = 'hidden';
      setTimeout(() => flash.remove(), 500);
    }, 4500);
  });

  /* ── Form Submit Loading State ──────────────────────────── */
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', () => {
      const btn = form.querySelector('button[type=submit]');
      if (btn) {
        btn.disabled    = true;
        btn.textContent = '⏳ Processing…';
      }
    });
  });

});
