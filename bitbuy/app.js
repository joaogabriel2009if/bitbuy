/* ============================================================
   BitBuy — app.js
   Lógica completa: auth, anúncios, tema, toast, modais, roteamento
   ============================================================ */

const API_URL = '../api';

/* ============================================================
   ESTADO GLOBAL
   ============================================================ */


const State = {
  user: null,
  ads: [],
  theme: localStorage.getItem('bitbuy-theme') || 'light',

  getUser()  { return this.user; },
  setUser(u) {
    this.user = u;
    if (u) localStorage.setItem('bitbuy_user', JSON.stringify(u));
    else    localStorage.removeItem('bitbuy_user');
  },
  loadUser() {
    try {
      const s = localStorage.getItem('bitbuy_user');
      this.user = s ? JSON.parse(s) : null;
    } catch { this.user = null; }
    return this.user;
  },
  logout() { this.setUser(null); },
};

/* ============================================================
   TEMA
   ============================================================ */
function applyTheme(theme) {
  State.theme = theme;
  localStorage.setItem('bitbuy-theme', theme);
  document.documentElement.classList.toggle('dark', theme === 'dark');
  // Atualizar ícone do botão de tema em todos os lugares
  document.querySelectorAll('.theme-icon-moon, .theme-icon-sun').forEach(el => {
    el.style.display = 'none';
  });
  document.querySelectorAll(theme === 'dark' ? '.theme-icon-sun' : '.theme-icon-moon').forEach(el => {
    el.style.display = '';
  });
  // Atualizar switch de configurações se existir
  const sw = document.getElementById('dark-switch');
  if (sw) sw.classList.toggle('on', theme === 'dark');
}

function toggleTheme() {
  applyTheme(State.theme === 'dark' ? 'light' : 'dark');
}

/* ============================================================
   TOAST
   ============================================================ */
function toast(message, type = 'success') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  t.innerHTML = `<span>${message}</span>`;
  container.appendChild(t);
  setTimeout(() => {
    t.style.animation = 'slideOut 0.3s ease forwards';
    setTimeout(() => t.remove(), 300);
  }, 3000);
}

/* ============================================================
   MODAL / DIALOG
   ============================================================ */
function openModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('open');
}
function closeModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('open');
}
// Fechar ao clicar fora do modal
document.addEventListener('click', e => {
  if (e.target.classList.contains('overlay')) {
    e.target.classList.remove('open');
  }
});

/* ============================================================
   TABS
   ============================================================ */
function initTabs(containerId) {
  const container = document.getElementById(containerId);
  if (!container) return;
  const triggers = container.querySelectorAll('.tab-trigger');
  const contents = container.querySelectorAll('.tab-content');
  triggers.forEach(trigger => {
    trigger.addEventListener('click', () => {
      const target = trigger.dataset.tab;
      triggers.forEach(t => t.classList.remove('active'));
      contents.forEach(c => c.classList.remove('active'));
      trigger.classList.add('active');
      const content = container.querySelector(`.tab-content[data-tab="${target}"]`);
      if (content) content.classList.add('active');
    });
  });
}

/* ============================================================
   SWITCH
   ============================================================ */
function initSwitch(el, onChange) {
  el.addEventListener('click', () => {
    el.classList.toggle('on');
    onChange(el.classList.contains('on'));
  });
}

/* ============================================================
   ESTRELAS (RATING)
   ============================================================ */
function renderStars(container, value, interactive = false, onChange = null) {
  container.innerHTML = '';
  for (let i = 1; i <= 5; i++) {
    const star = document.createElement('button');
    star.type = 'button';
    star.className = 'star' + (i <= value ? ' filled' : '');
    star.innerHTML = `<svg viewBox="0 0 24 24" fill="${i <= value ? 'currentColor' : 'none'}" stroke="currentColor" stroke-width="2">
      <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
    </svg>`;
    if (interactive) {
      star.addEventListener('click', () => {
        if (onChange) onChange(i);
        renderStars(container, i, true, onChange);
      });
      star.style.cursor = 'pointer';
    } else {
      star.style.pointerEvents = 'none';
    }
    container.appendChild(star);
  }
}

/* ============================================================
   API
   ============================================================ */
async function apiPost(endpoint, data) {
  const r = await fetch(`${API_URL}/${endpoint}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data),
  });
  if (!r.ok) throw new Error(`HTTP ${r.status}`);
  return r.json();
}

async function fetchAds() {
  const r = await fetch(`${API_URL}/get_ads.php`);
  if (!r.ok) throw new Error(`HTTP ${r.status}`);
  return r.json();
}

/* ============================================================
   NAVEGAÇÃO (SPA simples via hash ou reload)
   ============================================================ */
function navigate(page) {
  window.location.href = page;
}

/* ============================================================
   FOOTER + NAV COMPARTILHADOS
   ============================================================ */
function renderFooter() {
  const footer = document.getElementById('app-footer');
  if (!footer) return;
  footer.innerHTML = `
    <div class="footer-inner">
      <div class="flex gap-2 items-center">
        <button class="footer-btn" onclick="openModal('settings-modal')" title="Configurações">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
          </svg>
        </button>
        <button class="footer-btn" onclick="openModal('about-modal')" title="Sobre">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
          </svg>
        </button>
      </div>
      <span class="footer-copy">© 2026 BitBuy - Marketplace Escolar</span>
      <button class="theme-btn" onclick="toggleTheme()" title="Alternar tema">
        <svg class="theme-icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:${State.theme==='dark'?'none':''}">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
        </svg>
        <svg class="theme-icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:${State.theme==='light'?'none':''}">
          <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
          <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
          <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
          <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
        </svg>
      </button>
    </div>`;

  // Modais do footer
  if (!document.getElementById('settings-modal')) {
    document.body.insertAdjacentHTML('beforeend', `
      <div class="overlay" id="settings-modal">
        <div class="modal">
          <button class="modal-close" onclick="closeModal('settings-modal')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
          <div class="modal-header">
            <div class="modal-title">Configurações</div>
            <div class="modal-description">Personalize sua experiência no BitBuy</div>
          </div>
          <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 0;border-top:1px solid var(--border)">
            <div>
              <div style="font-weight:500;font-size:.9rem">Tema Escuro</div>
              <div style="font-size:.8rem;color:var(--muted-foreground)">Ative para uma experiência mais confortável</div>
            </div>
            <div class="switch ${State.theme==='dark'?'on':''}" id="dark-switch">
              <div class="switch-thumb"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="overlay" id="about-modal">
        <div class="modal">
          <button class="modal-close" onclick="closeModal('about-modal')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
          <div class="modal-header">
            <div class="modal-title">Sobre o BitBuy</div>
            <div class="modal-description">Seu marketplace escolar de confiança</div>
          </div>
          <div style="display:flex;flex-direction:column;gap:1rem;padding-top:1rem;border-top:1px solid var(--border)">
            <div style="display:flex;gap:.75rem;align-items:flex-start">
              <svg style="width:1.25rem;height:1.25rem;color:var(--accent);flex-shrink:0;margin-top:2px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              <div><div style="font-weight:500;margin-bottom:.25rem">Seguro e Confiável</div><div style="font-size:.85rem;color:var(--muted-foreground)">Todas as transações são verificadas e seguras para a comunidade escolar.</div></div>
            </div>
            <div style="display:flex;gap:.75rem;align-items:flex-start">
              <svg style="width:1.25rem;height:1.25rem;color:var(--accent);flex-shrink:0;margin-top:2px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              <div><div style="font-weight:500;margin-bottom:.25rem">Contato</div><div style="font-size:.85rem;color:var(--muted-foreground)">Dúvidas? Entre em contato: suporte@bitbuy.com</div></div>
            </div>
            <div style="border-top:1px solid var(--border);padding-top:1rem;text-align:center;font-size:.75rem;color:var(--muted-foreground)">
              Versão 1.0.0 - Desenvolvido com HTML, CSS e JavaScript
            </div>
          </div>
        </div>
      </div>`);

    const sw = document.getElementById('dark-switch');
    if (sw) {
      sw.addEventListener('click', () => {
        const isDark = !sw.classList.contains('on');
        sw.classList.toggle('on');
        applyTheme(isDark ? 'dark' : 'light');
      });
    }
  }
}

function renderBottomNav(activePage) {
  const nav = document.getElementById('bottom-nav');
  if (!nav) return;
  const user = State.getUser();
  nav.innerHTML = `
    <div class="bottom-nav-inner">
      <button class="nav-btn ${activePage==='home'?'active':''}" onclick="navigate('home.html')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        <span>Início</span>
      </button>
      ${user?.accountType === 'vendedor' ? `
      <button class="nav-btn ${activePage==='create-ad'?'active':''}" onclick="navigate('create-ad.html')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        <span>Criar Anúncio</span>
      </button>` : ''}
      <button class="nav-btn ${activePage==='profile'?'active':''}" onclick="navigate('profile.html')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span>Perfil</span>
      </button>
    </div>`;
}

/* ============================================================
   AD CARD (HTML string)
   ============================================================ */
function adCardHTML(ad) {
  const avg = ad.reviews.length
    ? (ad.reviews.reduce((s,r) => s+r.rating, 0) / ad.reviews.length).toFixed(1)
    : null;

  const imgHTML = ad.image
    ? `<div class="ad-card-img"><img src="${ad.image}" alt="${escHtml(ad.title)}" onerror="this.parentElement.innerHTML='<svg viewBox=\\'0 0 24 24\\' fill=\\'none\\' stroke=\\'currentColor\\' stroke-width=\\'1.5\\'><rect x=\\'3\\' y=\\'3\\' width=\\'18\\' height=\\'18\\' rx=\\'2\\'/><circle cx=\\'8.5\\' cy=\\'8.5\\' r=\\'1.5\\'/><polyline points=\\'21 15 16 10 5 21\\'/></svg>'"></div>`
    : '';

  return `
    <div class="ad-card" onclick="openAdModal(${JSON.stringify(ad).replace(/"/g,'&quot;')})">
      ${imgHTML}
      <div class="ad-card-body">
        <div class="ad-card-top">
          <div class="ad-card-title">${escHtml(ad.title)}</div>
          <span class="badge badge-secondary">${escHtml(ad.type)}</span>
        </div>
        <div class="ad-price">R$ ${Number(ad.price).toFixed(2)}</div>
        ${ad.stock !== undefined && ad.stock !== null && ad.stock !== 0 ? `
          <div class="ad-meta">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            Estoque: ${ad.stock}
          </div>` : ''}
        ${avg ? `
          <div class="ad-meta" style="margin-top:.35rem">
            <svg viewBox="0 0 24 24" fill="#eab308" stroke="#eab308" stroke-width="2" style="width:.9rem;height:.9rem"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            ${avg} (${ad.reviews.length})
          </div>` : ''}
      </div>
    </div>`;
}

function escHtml(str) {
  return String(str ?? '')
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

/* ============================================================
   MODAL DE DETALHE DO ANÚNCIO
   ============================================================ */
let _currentAd = null;
let _currentRating = 5;

function openAdModal(ad) {
  _currentAd = ad;
  _currentRating = 5;
  const user = State.getUser();

  const avg = ad.reviews.length
    ? (ad.reviews.reduce((s,r) => s+r.rating, 0) / ad.reviews.length).toFixed(1)
    : null;

  const starsHtml = (n) => Array.from({length:5}, (_,i) =>
    `<svg viewBox="0 0 24 24" fill="${i<n?'#eab308':'none'}" stroke="${i<n?'#eab308':'#d1d5db'}" stroke-width="2" style="width:1rem;height:1rem;display:inline-block">
      <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
    </svg>`).join('');

  const reviewsHtml = ad.reviews.length === 0
    ? `<p style="color:var(--muted-foreground);font-size:.875rem">Nenhuma avaliação ainda</p>`
    : ad.reviews.map(r => `
        <div style="border:1px solid var(--border);border-radius:.5rem;padding:.75rem;margin-bottom:.5rem">
          <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.35rem">
            <span style="font-weight:500;font-size:.875rem">@${escHtml(r.username)}</span>
            <div>${starsHtml(r.rating)}</div>
          </div>
          <p style="font-size:.85rem;color:var(--muted-foreground)">${escHtml(r.comment)}</p>
        </div>`).join('');

  const canReview = user && user.id !== ad.sellerId;
  const reviewFormHtml = canReview ? `
    <div style="border:1px solid var(--border);border-radius:.75rem;padding:1rem;margin-top:1rem">
      <div style="font-weight:500;margin-bottom:.75rem">Deixe sua avaliação</div>
      <div style="margin-bottom:.75rem">
        <div class="label">Nota</div>
        <div id="modal-stars" class="stars"></div>
      </div>
      <div class="form-group">
        <label class="label">Comentário</label>
        <textarea class="textarea" id="modal-review-comment" rows="3" placeholder="Escreva sua avaliação..."></textarea>
      </div>
      <button class="btn btn-primary btn-full" onclick="submitReview()">Enviar Avaliação</button>
    </div>` : '';

  let modal = document.getElementById('ad-detail-modal');
  if (!modal) {
    document.body.insertAdjacentHTML('beforeend', `
      <div class="overlay" id="ad-detail-modal">
        <div class="modal modal-lg" id="ad-detail-modal-inner"></div>
      </div>`);
    modal = document.getElementById('ad-detail-modal');
  }

  document.getElementById('ad-detail-modal-inner').innerHTML = `
    <button class="modal-close" onclick="closeModal('ad-detail-modal')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="modal-header">
      <div class="modal-title">${escHtml(ad.title)}</div>
      <div class="modal-description">Detalhes do anúncio, avaliações e informações do vendedor.</div>
    </div>
    ${ad.image ? `<div style="width:100%;aspect-ratio:16/9;overflow:hidden;border-radius:.5rem;background:var(--muted);margin-bottom:1rem">
      <img src="${ad.image}" alt="${escHtml(ad.title)}" style="width:100%;height:100%;object-fit:cover" onerror="this.style.display='none'">
    </div>` : ''}
    <div style="margin-bottom:1rem">
      <div style="font-size:1.75rem;font-weight:700;color:var(--accent);margin-bottom:.5rem">R$ ${Number(ad.price).toFixed(2)}</div>
      <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem">
        <span class="badge badge-default">${escHtml(ad.type)}</span>
        <span class="badge badge-outline">${escHtml(ad.category)}</span>
        ${ad.stock !== undefined && ad.stock !== null && ad.stock !== 0 ? `<span class="badge badge-secondary">Estoque: ${ad.stock}</span>` : ''}
      </div>
    </div>
    <div style="margin-bottom:1rem">
      <div style="font-weight:600;margin-bottom:.35rem">Descrição</div>
      <p style="color:var(--muted-foreground);font-size:.9rem">${escHtml(ad.description)}</p>
    </div>
    <div style="margin-bottom:1rem">
      <div style="font-weight:600;margin-bottom:.35rem">Vendedor</div>
      <p style="color:var(--muted-foreground);font-size:.9rem">@${escHtml(ad.sellerUsername)}</p>
    </div>
    <div>
      <div style="font-weight:600;margin-bottom:.75rem">Avaliações (${ad.reviews.length})</div>
      ${reviewsHtml}
      ${reviewFormHtml}
    </div>`;

  openModal('ad-detail-modal');

  if (canReview) {
    const starsEl = document.getElementById('modal-stars');
    if (starsEl) renderStars(starsEl, _currentRating, true, v => { _currentRating = v; });
  }
}

async function submitReview() {
  const user = State.getUser();
  if (!user || !_currentAd) return;
  const comment = document.getElementById('modal-review-comment')?.value.trim() || '';
  try {
    const data = await apiPost('add_review.php', {
      adId: _currentAd.id,
      userId: user.id,
      username: user.username,
      rating: _currentRating,
      comment,
    });
    if (data.success) {
      toast('Avaliação enviada com sucesso!');
      // Atualiza anúncio local
      const adIdx = State.ads.findIndex(a => a.id === _currentAd.id);
      if (adIdx !== -1) {
        State.ads[adIdx].reviews.push(data.newReview);
        _currentAd = State.ads[adIdx];
      }
      closeModal('ad-detail-modal');
      if (typeof renderAds === 'function') renderAds();
    } else {
      toast('Erro ao enviar avaliação.', 'error');
    }
  } catch {
    toast('Erro de conexão.', 'error');
  }
}

/* ============================================================
   INICIALIZAÇÃO GLOBAL
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
  State.loadUser();
  applyTheme(State.theme);

  // Redirecionar se não autenticado (em páginas protegidas)
  const page = document.body.dataset.page;
  if (page && page !== 'auth' && !State.getUser()) {
    navigate('index.html');
    return;
  }
  if (page === 'auth' && State.getUser()) {
    navigate('home.html');
    return;
  }

  renderFooter();
  renderBottomNav(page);

  // Inicializar página específica
  if (typeof initPage === 'function') initPage();
});