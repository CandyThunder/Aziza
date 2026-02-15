<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Alina Rho — Neo Atelier</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=Manrope:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <div class="mesh-bg" aria-hidden="true"></div>
  <header class="topbar">
    <a href="#home" class="logo">ALINA RHO / ART INDEX</a>
    <nav>
      <a href="#works">Works</a>
      <a href="#journal">Journal</a>
      <a href="#studio">Studio</a>
      <button id="open-cms" class="chip">CMS</button>
    </nav>
  </header>

  <main id="home">
    <section class="hero">
      <p class="kicker">Contemporary Artist • Signal, Texture, Ritual</p>
      <h1>Digital Brutalism meets tactile poetry.</h1>
      <p class="lead">Designed with inspiration from high-scoring Awwwards experiences: strong typographic hierarchy, immersive contrast, editorial layout shifts, and cinematic spacing instead of template-like blocks.</p>
      <div class="ticker">
        <span>AWWWARDS-INSPIRED STORY FLOW</span>
        <span>FWA-LIKE EXPERIMENTAL COMPOSITION</span>
        <span>SITEINSPIRE-LEVEL TYPE CONTROL</span>
      </div>
    </section>

    <section id="works" class="works">
      <article class="work-card tall">
        <img src="https://images.unsplash.com/photo-1547891654-e66ed7ebb968?auto=format&fit=crop&w=1200&q=80" alt="layered abstract canvas" />
        <div><p>01</p><h3>Signal Bloom</h3></div>
      </article>
      <article class="work-card wide">
        <img src="https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=1600&q=80" alt="painted wall textures" />
        <div><p>02</p><h3>Ritual Fragments</h3></div>
      </article>
      <article class="work-card">
        <img src="https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=900&q=80" alt="expressionist portrait details" />
        <div><p>03</p><h3>Echo Bodies</h3></div>
      </article>
      <article class="work-card">
        <img src="https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?auto=format&fit=crop&w=900&q=80" alt="artist mixing paint" />
        <div><p>04</p><h3>Noise Archive</h3></div>
      </article>
    </section>

    <section id="journal" class="journal">
      <div class="section-head">
        <h2>Journal / CMS-powered</h2>
        <p>Backed by PHP + JSON so it works under XAMPP without extra dependencies.</p>
      </div>
      <div id="post-grid" class="post-grid"></div>
    </section>

    <section id="studio" class="studio">
      <h2>Studio Notes</h2>
      <p>Compared to many award-winning art portfolios, this build focuses on a stronger identity: kinetic marquee strip, dramatic clipping panels, and modular cards with cinematic cropping while preserving fast-load fundamentals.</p>
    </section>
  </main>

  <aside id="cms" class="cms" aria-hidden="true">
    <div class="cms-head">
      <h3>Post Manager</h3>
      <button id="close-cms" class="chip">Close</button>
    </div>
    <form id="post-form">
      <input name="title" placeholder="Post title" required />
      <input name="category" placeholder="Category" required />
      <input name="image" type="url" placeholder="Image URL" required />
      <textarea name="content" rows="5" placeholder="Write your post" required></textarea>
      <button class="btn" type="submit">Publish</button>
    </form>
    <ul id="post-admin-list"></ul>
  </aside>

  <dialog id="post-modal">
    <article id="post-modal-content"></article>
    <button id="close-modal" class="chip">Close</button>
  </dialog>

  <script src="app.js"></script>
</body>
</html>
