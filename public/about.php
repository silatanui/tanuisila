<?php
$activePage = 'about';
require_once __DIR__ . '/header.php';
?>

<main class="container">
  <section id="about" class="section-row" style="border-bottom:none;">
    <div class="section-label">About</div>
    <div class="section-content" style="width: 100%;">
      
      <h2>I am a Computer Scientist who enjoys turning ideas into working technology.</h2>
      
      <div style="margin-bottom: 24px; border: 1px solid var(--light-gray); padding: 12px; background: var(--soft-bg);">
        <img src="https://edu.unideb.hu/elemek/discover1.jpg" alt="University of Debrecen" style="width: 100%; height: auto; object-fit: cover; border: none;">
      </div>

      <!-- MAIN EDITORIAL BIOGRAPHY BODY (Clean High density layouts) -->
      <div class="editorial-bio-content" style="font-size: 0.94rem; line-height: 1.45; color: var(--muted); display: flex; flex-direction: column; gap: 16px; max-width: 900px; text-align: justify;">
        
        <p style="font-size: 0.94rem; line-height: 1.45; color: var(--muted); margin: 0 0 4px;">
          I’m <strong>Tanui Kipng'etich Sila</strong>, a Computer Scientist, Software Developer, Researcher, and Technology Innovator with a background spanning software development, web engineering, databases, artificial intelligence, machine learning, networking, and security research.
        </p>

        <p style="margin: 0 0 4px;">
          My interest in technology has always been strongly connected to <strong>building things</strong>. I enjoy taking a complex computational problem, breaking it down into understandable units, and turning ideas into functioning interactive systems. For me, programming is not simply writing code; it is a rigorous method for creating practical, maintainable solutions.
        </p>

        <p style="margin: 0 0 4px;">
          My academic journey began with a <strong>BSc in Computer Science at Kirinyaga University</strong>, graduating with <strong>First Class Honours</strong>. This gave me a solid grounding in core computing foundations, databases, routing switching systems, and mathematical logic. I later advanced my specialization with an <strong>MSc in Computer Science at the University of Debrecen, Hungary</strong>, as a <strong>Stipendium Hungaricum scholar</strong>, focusing on data science, AI workflow automation, geometric modeling, and research methodologies.
        </p>

        <div style="border-left: 3px solid var(--accent); padding-left: 16px; margin: 12px 0; background: var(--soft-bg); padding: 14px 18px;">
          <h4 style="margin: 0 0 6px; font-size: 1.05rem; font-weight: 800; color: var(--text); letter-spacing:-0.02em;">Applied Engineering & Systems</h4>
          <p style="margin: 0; line-height: 1.4;">
            I have spent considerable time developing robust full-stack web applications and database architectures using <strong>Python, Go, PHP, JavaScript, C#, Blazor, React, Laravel, and SQL/NoSQL (MySQL, MongoDB)</strong>. Working on e-commerce, LMS platforms, and custom business integrations has helped me develop a deep appreciation for software as an integrated, scalable ecosystem.
          </p>
        </div>

        <div style="border-left: 3px solid var(--accent); padding-left: 16px; margin: 12px 0; background: var(--soft-bg); padding: 14px 18px;">
          <h4 style="margin: 0 0 6px; font-size: 1.05rem; font-weight: 800; color: var(--text); letter-spacing:-0.02em;">Research & Algorithmic Problem Solving</h4>
          <p style="margin: 0; line-height: 1.4;">
            Research defines how I approach software design. I have implemented complex math models covering <strong>regression analysis, neural network vectorized computation (backpropagation), clustering, Principal Component Analysis, numerical curves design (Bézier, Hermite), and modern asymmetric cryptography (RSA, ElGamal)</strong>. I enjoy turning abstract equations into interactive, testable, and highly optimized programs.
          </p>
        </div>

        <div style="border-left: 3px solid var(--accent); padding-left: 16px; margin: 12px 0; background: var(--soft-bg); padding: 14px 18px;">
          <h4 style="margin: 0 0 6px; font-size: 1.05rem; font-weight: 800; color: var(--text); letter-spacing:-0.02em;">Teaching, Sharing & Sterrali Innovation</h4>
          <p style="margin: 0; line-height: 1.4;">
            Serving as an <strong>ICT and Programming Lecturer</strong> taught me to synthesize and explain complex systems with absolute clarity. Technical knowledge is of peak value when it can be clearly communicated and applied. Today, I continue to channel this engineering mindset toward digital products development, systems automation, and technology entrepreneurship through <strong>Sterrali Technologies</strong>.
          </p>
        </div>

        <blockquote style="margin: 12px 0; padding: 16px 20px; background: var(--soft-bg); border-left: 4px solid var(--accent); font-size: 0.98rem; font-weight: 700; color: var(--text); line-height: 1.4; text-align: center; border-radius: 0;">
          "Understand the problem. Explore the possibilities. Build the solution. Test it. Improve it."
        </blockquote>

        <p style="margin: 0 0 12px;">
          My development philosophy is focused on building software that is strictly <strong>useful, understandable, reliable, maintainable, accessible, and purpose-driven</strong>. Whether designing a neural model, a database query pipeline, or a full-stack platform, I want the work to ultimately answer one question: <em>What problem does this solve, and how can it be solved better?</em>
        </p>

        <!-- COMPACT IDENTITY GRID MATRIX -->
        <div class="compact-identity-matrix" style="margin-top: 30px; border-top: 1px solid var(--light-gray); padding-top: 30px;">
          <h4 style="margin: 0 0 20px; font-size: 0.8rem; font-weight: 800; color: var(--accent-muted); letter-spacing: 0.14em; text-transform: uppercase;">Core Specialties</h4>
          
          <div class="identity-cards-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            
            <div style="background: var(--soft-bg); border: 1px solid var(--light-gray); padding: 18px; display: flex; gap: 14px; align-items: flex-start;">
              <small style="font-weight: 800; font-size: 0.85rem; color: var(--accent); font-family: monospace;">01</small>
              <div style="line-height:1.35;">
                <h5 style="margin: 0 0 4px; font-size: 0.98rem; font-weight: 800; color: var(--text);">Software Development</h5>
                <p style="margin: 0; font-size: 0.85rem; color: var(--muted);">Designing and building web applications, software systems, APIs, and database-driven solutions.</p>
              </div>
            </div>

            <div style="background: var(--soft-bg); border: 1px solid var(--light-gray); padding: 18px; display: flex; gap: 14px; align-items: flex-start;">
              <small style="font-weight: 800; font-size: 0.85rem; color: var(--accent); font-family: monospace;">02</small>
              <div style="line-height:1.35;">
                <h5 style="margin: 0 0 4px; font-size: 0.98rem; font-weight: 800; color: var(--text);">Research & Computing</h5>
                <p style="margin: 0; font-size: 0.85rem; color: var(--muted);">Exploring algorithms, artificial intelligence, machine learning, computational methods, and computer science problems.</p>
              </div>
            </div>

            <div style="background: var(--soft-bg); border: 1px solid var(--light-gray); padding: 18px; display: flex; gap: 14px; align-items: flex-start;">
              <small style="font-weight: 800; font-size: 0.85rem; color: var(--accent); font-family: monospace;">03</small>
              <div style="line-height:1.35;">
                <h5 style="margin: 0 0 4px; font-size: 0.98rem; font-weight: 800; color: var(--text);">Technology Innovation</h5>
                <p style="margin: 0; font-size: 0.85rem; color: var(--muted);">Turning ideas and real-world problems into practical digital solutions and technology projects.</p>
              </div>
            </div>

            <div style="background: var(--soft-bg); border: 1px solid var(--light-gray); padding: 18px; display: flex; gap: 14px; align-items: flex-start;">
              <small style="font-weight: 800; font-size: 0.85rem; color: var(--accent); font-family: monospace;">04</small>
              <div style="line-height:1.35;">
                <h5 style="margin: 0 0 4px; font-size: 0.98rem; font-weight: 800; color: var(--text);">Teaching & Knowledge</h5>
                <p style="margin: 0; font-size: 0.85rem; color: var(--muted);">Making programming, computing, and technical concepts easier to understand through practical learning.</p>
              </div>
            </div>

          </div>
        </div>

        <!-- OUTRO HERO CARD -->
        <div class="outro-stamp-box" style="margin-top: 30px; border-top: 1px solid var(--light-gray); padding-top: 24px; text-align: center;">
          <blockquote style="margin: 0 0 12px; font-size: 1.05rem; font-weight: 700; color: var(--text); border: none; padding: 0; background: transparent;">
            "Computer Science is not just what I studied. It is how I approach problems."
          </blockquote>
          <strong style="font-size: 1.05rem; color: var(--text); display: block; margin-bottom: 2px;">Tanui Kipng'etich Sila</strong>
          <small style="font-size: 0.78rem; font-weight: 700; color: var(--accent-muted); letter-spacing: 0.08em; text-transform: uppercase;">Computer Scientist · Software Developer · Researcher · Technology Innovator</small>
        </div>

      </div>

    </div>
  </section>
</main>

<style>
  /* Dynamic mobile stacks for cards */
  @media (max-width: 760px) {
    .identity-cards-grid {
      grid-template-columns: 1fr !important;
    }
  }
</style>

<?php
require_once __DIR__ . '/footer.php';
?>
