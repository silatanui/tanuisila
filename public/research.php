<?php
$activePage = 'research';
require_once __DIR__ . '/header.php';
?>

<main class="container">
  <section id="research" class="section-row" style="border-bottom:none;">
    <div class="section-label">Research</div>
    <div class="section-content">
      <h2>Academic & Computational Research</h2>
      <div style="margin-bottom: 24px; border: 1px solid var(--light-gray); padding: 12px; background: var(--soft-bg);">
        <img src="https://images.pexels.com/photos/5185146/pexels-photo-5185146.jpeg" alt="Academic & Computational Research" style="width: 100%; height: auto; object-fit: cover; border: none;">
      </div>
      <div class="timeline-rows">
        <div class="timeline-row">
          <div class="time-meta">Master's Thesis</div>
          <div class="timeline-desc">
            <h4>AI-Driven E-Commerce Automation</h4>
            <div class="timeline-institution">University of Debrecen</div>
            <div class="timeline-body" style="margin-bottom: 12px;">Applied artificial intelligence to e-commerce workflow automation, combining intelligent recommendation systems, inventory optimisation and customer behaviour analysis inside a production-oriented Blazor Server project.</div>
            <a href="thesis.pdf" target="_blank" style="display: inline-flex; font-size: 0.76rem; font-weight: 700; text-transform: uppercase; border-bottom: 2px solid var(--text); padding-bottom: 2px; color: var(--text);">View Full Thesis (PDF) →</a>
          </div>
        </div>
        <div class="timeline-row">
          <div class="time-meta">Theoretical Study</div>
          <div class="timeline-desc">
            <h4>Models of Computation</h4>
            <div class="timeline-institution">Computability & Formal Systems</div>
            <div class="timeline-body">Investigating undecidability, formal languages and computability. Designed interactive solving visualizations for the Post Correspondence Problem, Hilbert's 10th Problem, and the Entscheidungsproblem.</div>
          </div>
        </div>
        <div class="timeline-row">
          <div class="time-meta">Geometric Modelling</div>
          <div class="timeline-desc">
            <h4>CAGD & Numerical Methods</h4>
            <div class="timeline-institution">Computer Aided Geometric Design</div>
            <div class="timeline-body">Parametric curves design algorithms (Bézier, Hermite, B-Spline), real-time differential geometry analysis and least-squares approximation on massive datasets.</div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php
require_once __DIR__ . '/footer.php';
?>
