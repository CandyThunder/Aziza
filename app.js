const postGrid = document.getElementById('post-grid');
const adminList = document.getElementById('post-admin-list');
const postForm = document.getElementById('post-form');
const cms = document.getElementById('cms');
const openCms = document.getElementById('open-cms');
const closeCms = document.getElementById('close-cms');
const modal = document.getElementById('post-modal');
const modalContent = document.getElementById('post-modal-content');
const closeModal = document.getElementById('close-modal');

async function fetchPosts() {
  const response = await fetch('api/posts.php');
  if (!response.ok) throw new Error('Could not load posts');
  return response.json();
}

function openPost(post) {
  modalContent.innerHTML = `
    <h2>${post.title}</h2>
    <p>${post.category} / ${new Date(post.createdAt).toLocaleDateString()}</p>
    <img src="${post.image}" alt="${post.title}" />
    <p>${post.content}</p>
  `;
  modal.showModal();
}

function render(posts) {
  postGrid.innerHTML = posts
    .map(
      (post) => `
      <article class="post">
        <img src="${post.image}" alt="${post.title}" loading="lazy" />
        <div class="meta">
          <small>${post.category}</small>
          <h3>${post.title}</h3>
          <p>${post.content.slice(0, 92)}...</p>
          <button class="chip read" data-id="${post.id}">Read</button>
        </div>
      </article>
      `,
    )
    .join('');

  adminList.innerHTML = posts
    .map(
      (post) => `
      <li>
        <span>${post.title}</span>
        <button class="chip del" data-id="${post.id}">Delete</button>
      </li>
      `,
    )
    .join('');

  document.querySelectorAll('.read').forEach((button) => {
    button.addEventListener('click', () => {
      const selected = posts.find((post) => post.id === button.dataset.id);
      if (selected) openPost(selected);
    });
  });

  document.querySelectorAll('.del').forEach((button) => {
    button.addEventListener('click', async () => {
      await fetch(`api/posts.php?id=${encodeURIComponent(button.dataset.id)}`, { method: 'DELETE' });
      await boot();
    });
  });
}

async function boot() {
  try {
    const posts = await fetchPosts();
    render(posts);
  } catch (error) {
    postGrid.innerHTML = `<p>${error.message}</p>`;
  }
}

postForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  const formData = new FormData(postForm);

  const payload = {
    title: formData.get('title').toString().trim(),
    category: formData.get('category').toString().trim(),
    image: formData.get('image').toString().trim(),
    content: formData.get('content').toString().trim(),
  };

  await fetch('api/posts.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });

  postForm.reset();
  await boot();
});

openCms.addEventListener('click', () => {
  cms.classList.add('open');
  cms.setAttribute('aria-hidden', 'false');
});

closeCms.addEventListener('click', () => {
  cms.classList.remove('open');
  cms.setAttribute('aria-hidden', 'true');
});

closeModal.addEventListener('click', () => modal.close());

boot();
