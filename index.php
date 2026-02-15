<?php require_once __DIR__ . '/api/lib.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Alina Rho — Motion Atelier</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <div class="ambient" aria-hidden="true"></div>
  <header class="topbar">
    <a href="#" class="logo">ALINA RHO / STUDIO</a>
    <nav>
      <a href="#gallery">Gallery</a>
      <a href="#blog">Blog</a>
      <button id="auth-open" class="chip">Login / Create account</button>
      <div id="profile-menu" class="profile hidden">
        <button id="profile-toggle" class="chip"></button>
        <div id="profile-dropdown" class="dropdown hidden">
          <a id="admin-link" href="admin.php" class="chip hidden">Open desk</a>
          <button id="change-name" class="chip">Change display name</button>
          <button id="logout-btn" class="chip">Sign out</button>
        </div>
      </div>
    </nav>
  </header>

  <main>
    <section class="hero reveal">
      <p class="tag">Awwwards-inspired kinetic portfolio</p>
      <h1 id="split-target">Sonic textures. Moving type. Living stories.</h1>
      <p class="hero-copy">Built with motion-led transitions, split-text hero animation, and editorial pacing inspired by award-winning art portfolios.</p>
      <div class="marquee"><span>EDITORIAL MOTION • PARALLAX DEPTH • RESPONSIVE STORYFLOW • RICH CONTENT CMS • </span></div>
    </section>

    <section id="gallery" class="gallery-grid reveal"></section>

    <section id="blog" class="blog-wrap reveal">
      <div class="heading"><h2>Studio Blog</h2><p>Authenticated users can vote once per post. You can switch your vote later.</p></div>
      <div id="posts" class="posts"></div>
    </section>
  </main>

  <dialog id="auth-modal">
    <div class="auth-panels">
      <form id="login-form" class="panel">
        <h3>Login</h3>
        <input name="email" type="email" placeholder="Email" required />
        <input name="password" type="password" placeholder="Password" required />
        <button class="btn">Login</button>
        <small>Seed admin: admin@neoatelier.local / Admin@123</small>
      </form>
      <form id="register-form" class="panel">
        <h3>Create account</h3>
        <input name="name" placeholder="Name" required />
        <input name="email" type="email" placeholder="Email" required />
        <input name="password" type="password" placeholder="Password" required />
        <button class="btn">Create</button>
      </form>
    </div>
    <button id="auth-close" class="chip">Close</button>
  </dialog>

  <dialog id="post-modal"><article id="post-modal-content"></article><button id="post-close" class="chip">Close</button></dialog>

  <script src="https://unpkg.com/gsap@3/dist/gsap.min.js"></script>
  <script src="https://unpkg.com/gsap@3/dist/ScrollTrigger.min.js"></script>
  <script src="https://unpkg.com/split-type"></script>
  <script src="app.js"></script>
</body>
</html>
