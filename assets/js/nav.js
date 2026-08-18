// ── Glorikar — Role-Based Nav Renderer ─────────────────────
import { getCachedUser, logout } from './auth.js';

// SVG icon helpers (Lucide-style outline icons, inline for no external dep)
const icons = {
  home: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>`,
  bookings: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`,
  bell: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>`,
  user: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`,
  map: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>`,
  users: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
  logout: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>`,
  chevron: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>`,
  dashboard: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>`,
};

const clientNav = [
  { href: '/client/home.php',        icon: 'home',     label: 'Home'          },
  { href: '/client/my-bookings.php', icon: 'bookings', label: 'Bookings'      },
  { href: '/shared/notifications.php', icon: 'bell',   label: 'Notifications' },
  { href: '/shared/profile.php',     icon: 'user',     label: 'Profile'       },
];

const adminNav = [
  { href: '/admin/dashboard.php',    icon: 'dashboard', label: 'Dashboard'     },
  { href: '/admin/schedule.php',     icon: 'map',       label: 'Route Map'     },
  { href: '/admin/technicians.php',  icon: 'users',     label: 'Teams'         },
  { href: '/shared/notifications.php', icon: 'bell',    label: 'Notifications' },
];

function isActive(href) {
  return window.location.pathname === href ||
         window.location.pathname.endsWith(href);
}

function initials(name = '') {
  return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
}

/** Call on every page that has #bottom-nav and/or #sidebar */
export function renderNav() {
  const user  = getCachedUser();
  if (!user) return;

  const items = user.role === 'admin' ? adminNav : clientNav;
  const currentPath = window.location.pathname;

  // ── Bottom nav ───────────────────────────────────────────
  const bottomNav = document.getElementById('bottom-nav');
  if (bottomNav) {
    bottomNav.innerHTML = items.map(item => `
      <a href="${item.href}"
         class="nav-item${isActive(item.href) ? ' active' : ''}"
         aria-label="${item.label}">
        ${icons[item.icon]}
        <span class="nav-item-label">${item.label}</span>
      </a>
    `).join('');
  }

  // ── Sidebar ──────────────────────────────────────────────
  const sidebar = document.getElementById('sidebar');
  if (sidebar) {
    sidebar.innerHTML = `
      <div class="sidebar-brand">
        <div class="sidebar-brand-name">Glorikar Engineering</div>
        <div class="sidebar-brand-role">${user.role === 'admin' ? 'Admin' : 'Client'} Portal</div>
      </div>
      <nav class="sidebar-nav" aria-label="Main navigation">
        <div class="sidebar-section-label">Navigation</div>
        ${items.map(item => `
          <a href="${item.href}"
             class="sidebar-item${isActive(item.href) ? ' active' : ''}"
             aria-current="${isActive(item.href) ? 'page' : 'false'}">
            ${icons[item.icon]}
            <span>${item.label}</span>
          </a>
        `).join('')}
      </nav>
      <div class="sidebar-footer">
        <div class="sidebar-user row row-md">
          <div class="avatar">${initials(user.full_name)}</div>
          <div class="flex-1 min-w-0">
            <div class="sidebar-user-name">${user.full_name}</div>
            <div class="sidebar-user-email" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${user.email}</div>
          </div>
        </div>
        <button class="btn btn-ghost btn-full mt-sm" id="logout-btn" style="justify-content:flex-start;gap:var(--sp-sm)">
          ${icons.logout} Sign out
        </button>
      </div>
    `;

    document.getElementById('logout-btn')?.addEventListener('click', logout);
  }
}
