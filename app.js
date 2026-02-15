const galleryEl = document.getElementById('gallery');
const postsEl = document.getElementById('posts');
const authModal = document.getElementById('auth-modal');
const authOpen = document.getElementById('auth-open');
const authClose = document.getElementById('auth-close');
const loginForm = document.getElementById('login-form');
const registerForm = document.getElementById('register-form');
const postModal = document.getElementById('post-modal');
const postModalContent = document.getElementById('post-modal-content');
const postClose = document.getElementById('post-close');
const profileMenu = document.getElementById('profile-menu');
const profileToggle = document.getElementById('profile-toggle');
const profileDropdown = document.getElementById('profile-dropdown');
const adminLink = document.getElementById('admin-link');
const logoutBtn = document.getElementById('logout-btn');
const changeNameBtn = document.getElementById('change-name');

let sessionUser = null;

async function req(url, options = {}) {
  const r = await fetch(url, options);
  const data = await r.json();
  if (!r.ok) throw new Error(data.error || 'Request failed');
  return data;
}

async function loadSession() {
  const status = await req('api/auth.php?action=status');
  sessionUser = status.user;

  if (sessionUser) {
    authOpen.classList.add('hidden');
    profileMenu.classList.remove('hidden');
    profileToggle.textContent = sessionUser.name;
  } else {
    authOpen.classList.remove('hidden');
    profileMenu.classList.add('hidden');
    profileDropdown.classList.add('hidden');
  }

  if (sessionUser && (sessionUser.role === 'admin' || sessionUser.role === 'subadmin')) {
    adminLink.classList.remove('hidden');
  } else {
    adminLink.classList.add('hidden');
  }
}

async function loadGallery() {
  const items = await req('api/gallery.php');
  galleryEl.innerHTML = items.map((i) => `
    <article class="gallery-item reveal-item"><img src="${i.image}" alt="${i.title}"/><div><p>${new Date(i.createdAt).toLocaleDateString()}</p><h3>${i.title}</h3></div></article>
  `).join('');
}

function openPost(post) {
  postModalContent.innerHTML = `<h2>${post.title}</h2><p>${post.category} · ${post.authorName}</p><img src="${post.cover}" alt="${post.title}"/><div>${post.content}</div>`;
  postModal.showModal();
}

async function vote(postId, type) {
  if (!sessionUser) {
    authModal.showModal();
    alert('Please log in to vote.');
    return;
  }

  try {
    await req('api/posts.php?action=vote', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ postId, type }),
    });
    await loadPosts();
  } catch (error) {
    alert(error.message);
  }
}

async function loadPosts() {
  const posts = await req('api/posts.php');
  postsEl.innerHTML = posts.map((p) => `
    <article class="post-card reveal-item">
      <img src="${p.cover}" alt="${p.title}"/>
      <div class="body"><small>${p.category}</small><h3>${p.title}</h3>
      <p>By ${p.authorName}</p>
      <div class="row"><button class="chip read" data-id="${p.id}">Read</button><button class="chip like" data-id="${p.id}">👍 ${p.likes || 0}</button><button class="chip dislike" data-id="${p.id}">👎 ${p.dislikes || 0}</button></div>
      </div>
    </article>`).join('');

  document.querySelectorAll('.read').forEach((b) => {
    b.onclick = () => {
      const p = posts.find((x) => x.id === b.dataset.id);
      if (p) openPost(p);
    };
  });
  document.querySelectorAll('.like').forEach((b) => { b.onclick = () => vote(b.dataset.id, 'like'); });
  document.querySelectorAll('.dislike').forEach((b) => { b.onclick = () => vote(b.dataset.id, 'dislike'); });

  wireRevealItems();
}

function wireRevealItems() {
  if (!window.gsap || !window.ScrollTrigger) return;
  gsap.utils.toArray('.reveal-item').forEach((el) => {
    gsap.fromTo(el, { y: 40, opacity: 0 }, {
      y: 0,
      opacity: 1,
      duration: 0.7,
      ease: 'power2.out',
      scrollTrigger: {
        trigger: el,
        start: 'top 85%',
      },
    });
  });
}

authOpen.onclick = () => authModal.showModal();
authClose.onclick = () => authModal.close();
postClose.onclick = () => postModal.close();
profileToggle.onclick = () => profileDropdown.classList.toggle('hidden');

logoutBtn.onclick = async () => {
  await req('api/auth.php?action=logout', { method: 'POST' });
  await loadSession();
};

changeNameBtn.onclick = async () => {
  const name = prompt('New display name', sessionUser?.name || '');
  if (!name) return;
  try {
    await req('api/auth.php?action=update-name', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name }),
    });
    await loadSession();
  } catch (error) {
    alert(error.message);
  }
};

loginForm.onsubmit = async (e) => {
  e.preventDefault();
  const fd = new FormData(loginForm);
  try {
    await req('api/auth.php?action=login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: fd.get('email'), password: fd.get('password') }),
    });
    authModal.close();
    await loadSession();
  } catch (err) {
    alert(err.message);
  }
};

registerForm.onsubmit = async (e) => {
  e.preventDefault();
  const fd = new FormData(registerForm);
  try {
    await req('api/auth.php?action=register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name: fd.get('name'), email: fd.get('email'), password: fd.get('password') }),
    });
    authModal.close();
    await loadSession();
    alert('Account created. You will be notified when new blogs are published.');
  } catch (err) {
    alert(err.message);
  }
};

if (window.SplitType && window.gsap) {
  const split = new SplitType('#split-target', { types: 'words, chars' });
  gsap.from(split.chars, { y: 70, opacity: 0, rotateX: -40, stagger: 0.02, duration: 0.8, ease: 'power3.out' });
  gsap.from('.hero-copy', { y: 20, opacity: 0, delay: 0.3, duration: 0.8 });
  gsap.to('.ambient', { backgroundPosition: '30% 20%, 75% 10%', duration: 12, repeat: -1, yoyo: true, ease: 'sine.inOut' });
}


if (window.gsap && window.ScrollTrigger) {
  gsap.utils.toArray('.reveal').forEach((section) => {
    gsap.to(section, {
      opacity: 1,
      y: 0,
      duration: 0.9,
      ease: 'power2.out',
      scrollTrigger: {
        trigger: section,
        start: 'top 88%',
      },
    });
  });
}

loadSession();
loadGallery();
loadPosts();
