const galleryEl = document.getElementById('gallery');
const postsEl = document.getElementById('posts');
const authModal = document.getElementById('auth-modal');
const authOpen = document.getElementById('auth-open');
const authClose = document.getElementById('auth-close');
const loginForm = document.getElementById('login-form');
const registerForm = document.getElementById('register-form');
const adminLink = document.getElementById('admin-link');
const postModal = document.getElementById('post-modal');
const postModalContent = document.getElementById('post-modal-content');
const postClose = document.getElementById('post-close');

async function req(url, options = {}) {
  const r = await fetch(url, options);
  const data = await r.json();
  if (!r.ok) throw new Error(data.error || 'Request failed');
  return data;
}

async function loadSession() {
  const status = await req('api/auth.php?action=status');
  if (status.user && (status.user.role === 'admin' || status.user.role === 'subadmin')) {
    adminLink.classList.remove('hidden');
  }
}

async function loadGallery() {
  const items = await req('api/gallery.php');
  galleryEl.innerHTML = items.map((i) => `
    <article class="gallery-item"><img src="${i.image}" alt="${i.title}"/><div><p>${new Date(i.createdAt).toLocaleDateString()}</p><h3>${i.title}</h3></div></article>
  `).join('');
}

function openPost(post) {
  postModalContent.innerHTML = `<h2>${post.title}</h2><p>${post.category} · ${post.authorName}</p><img src="${post.cover}" alt="${post.title}"/><div>${post.content}</div>`;
  postModal.showModal();
}

async function loadPosts() {
  const posts = await req('api/posts.php');
  postsEl.innerHTML = posts.map((p) => `
    <article class="post-card">
      <img src="${p.cover}" alt="${p.title}"/>
      <div class="body"><small>${p.category}</small><h3>${p.title}</h3>
      <p>By ${p.authorName}</p>
      <div class="row"><button class="chip read" data-id="${p.id}">Read</button><button class="chip like" data-id="${p.id}">👍 ${p.likes||0}</button><button class="chip dislike" data-id="${p.id}">👎 ${p.dislikes||0}</button></div>
      </div>
    </article>`).join('');

  document.querySelectorAll('.read').forEach((b) => b.onclick = () => {
    const p = posts.find((x) => x.id === b.dataset.id);
    if (p) openPost(p);
  });
  document.querySelectorAll('.like').forEach((b) => b.onclick = async () => { await req('api/posts.php?action=vote', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({postId:b.dataset.id,type:'like'})}); loadPosts();});
  document.querySelectorAll('.dislike').forEach((b) => b.onclick = async () => { await req('api/posts.php?action=vote', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({postId:b.dataset.id,type:'dislike'})}); loadPosts();});
}

authOpen.onclick = () => authModal.showModal();
authClose.onclick = () => authModal.close();
postClose.onclick = () => postModal.close();

loginForm.onsubmit = async (e) => {
  e.preventDefault();
  const fd = new FormData(loginForm);
  try {
    await req('api/auth.php?action=login', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({email:fd.get('email'), password:fd.get('password')})});
    authModal.close();
    await loadSession();
    alert('Logged in successfully.');
  } catch (err) { alert(err.message); }
};

registerForm.onsubmit = async (e) => {
  e.preventDefault();
  const fd = new FormData(registerForm);
  try {
    await req('api/auth.php?action=register', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({name:fd.get('name'), email:fd.get('email'), password:fd.get('password')})});
    authModal.close();
    alert('Account created. You will be notified by email log when new blogs are published.');
  } catch (err) { alert(err.message); }
};

if (window.SplitType && window.gsap) {
  const split = new SplitType('#split-target', { types: 'words, chars' });
  gsap.from(split.chars, { y: 60, opacity: 0, stagger: 0.03, duration: 0.8, ease: 'power3.out' });
}

loadSession();
loadGallery();
loadPosts();
