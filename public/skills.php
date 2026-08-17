<?php
$activePage = 'skills';
require_once __DIR__ . '/header.php';
?>

<main class="container">
  <section id="skills" class="section-row" style="border-bottom:none; min-height: 700px;">
    <div class="section-label">Skills</div>
    <div class="section-content" style="width: 100%;">
      <h2>Comprehensive Technical Toolkit</h2>
      <p style="color: var(--muted); margin-bottom: 30px; font-size: 0.98rem; max-width: 720px; line-height: 1.4;">
        Organized by domain specialties to demonstrate academic depth, engineering fundamentals, and practical software delivery. Use the search bar of the interactive matrix to investigate specific frameworks or theoretical concepts.
      </p>

      <!-- SEARCH BAR -->
      <div style="margin-bottom: 30px; position: relative;">
        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; top: 15px; color: var(--accent-muted); font-size: 1rem;"></i>
        <input type="text" id="skillsSearch" placeholder="Type here to search technical skills, libraries, or algorithms (e.g. RSA, Blazor, PyTorch, SQL)..." 
               style="width: 100%; padding: 14px 14px 14px 44px; background: var(--soft-bg); border: 1px solid var(--light-gray); color: var(--text); outline: none; font-family: inherit; font-size: 0.94rem; box-shadow: 0 4px 12px var(--shadow);">
      </div>

      <!-- INTERACTIVE SKILLS GRID MATRIX -->
      <div class="skills-layout-matrix" style="display: grid; grid-template-columns: 280px 1fr; gap: 30px;">
        
        <!-- SIDE NAVIGATION TABS -->
        <div class="skills-categories-nav" style="display: flex; flex-direction: column; gap: 6px;">
          <button class="skill-nav-btn active" onclick="switchSkillTab('tab-software')">
            <span class="num">01</span> Software Development
          </button>
          <button class="skill-nav-btn" onclick="switchSkillTab('tab-web')">
            <span class="num">02</span> Web Engineering
          </button>
          <button class="skill-nav-btn" onclick="switchSkillTab('tab-database')">
            <span class="num">03</span> Databases & Storage
          </button>
          <button class="skill-nav-btn" onclick="switchSkillTab('tab-ai')">
            <span class="num">04</span> AI & Machine Learning
          </button>
          <button class="skill-nav-btn" onclick="switchSkillTab('tab-alg')">
            <span class="num">05</span> Algorithms & Complexity
          </button>
          <button class="skill-nav-btn" onclick="switchSkillTab('tab-crypto')">
            <span class="num">06</span> Security & Cryptography
          </button>
          <button class="skill-nav-btn" onclick="switchSkillTab('tab-cloud')">
            <span class="num">07</span> Cloud & IT Infrastructure
          </button>
          <button class="skill-nav-btn" onclick="switchSkillTab('tab-design')">
            <span class="num">08</span> UI/UX & Digital Design
          </button>
          <button class="skill-nav-btn" onclick="switchSkillTab('tab-academic')">
            <span class="num">09</span> Academia, Research & Teaching
          </button>
        </div>

        <!-- CONTENT DISPLAY PANELS -->
        <div class="skills-tab-content" style="background: var(--soft-bg); border: 1px solid var(--light-gray); padding: 30px; min-height: 480px; box-shadow: 0 4px 12px var(--shadow);">
          
          <!-- TAB 1: SOFTWARE DEVELOPMENT -->
          <div id="tab-software" class="skill-panel active">
            <h3 class="panel-section-title">Software Development</h3>
            <p class="panel-section-intro">Core languages and theoretical concepts supporting software lifecycle and reuse principles.</p>
            
            <div class="panel-sub-grid">
              <div>
                <h4 class="sub-group-title"><i class="fa-solid fa-code"></i> Programming Languages</h4>
                <ul class="skills-item-tags">
                  <li class="sk-tag">Python</li>
                  <li class="sk-tag">JavaScript</li>
                  <li class="sk-tag">C</li>
                  <li class="sk-tag">C++</li>
                  <li class="sk-tag">PHP</li>
                  <li class="sk-tag">SQL</li>
                  <li class="sk-tag">HTML5</li>
                  <li class="sk-tag">CSS3</li>
                </ul>
              </div>
              <div style="margin-top: 24px;">
                <h4 class="sub-group-title"><i class="fa-solid fa-brain"></i> Programming Concepts</h4>
                <ul class="skills-item-tags spec">
                  <li class="sk-tag-flat">Object-Oriented Programming</li>
                  <li class="sk-tag-flat">Functional Programming Concepts</li>
                  <li class="sk-tag-flat">Procedural Programming</li>
                  <li class="sk-tag-flat">Data Structures</li>
                  <li class="sk-tag-flat">Algorithms</li>
                  <li class="sk-tag-flat">Algorithm Design</li>
                  <li class="sk-tag-flat">Computational Problem Solving</li>
                  <li class="sk-tag-flat">Modular Programming</li>
                  <li class="sk-tag-flat">Code Reusability</li>
                  <li class="sk-tag-flat">Exception Handling</li>
                  <li class="sk-tag-flat">File Handling</li>
                  <li class="sk-tag-flat">Debugging</li>
                  <li class="sk-tag-flat">Software Testing</li>
                  <li class="sk-tag-flat">Code Optimization</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- TAB 2: WEB ENGINEERING -->
          <div id="tab-web" class="skill-panel">
            <h3 class="panel-section-title">Web Engineering & Architectures</h3>
            <p class="panel-section-intro">End-to-end full-stack architectures, API integrations, and client-side UI optimization patterns.</p>
            
            <div class="panel-sub-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px;">
              <div>
                <h4 class="sub-group-title"><i class="fa-solid fa-laptop-code"></i> Front-End Domain</h4>
                <ul class="skills-item-tags">
                  <li class="sk-tag">React.js</li>
                  <li class="sk-tag">Bootstrap</li>
                  <li class="sk-tag">jQuery</li>
                  <li class="sk-tag">HTML5</li>
                  <li class="sk-tag">CSS3</li>
                  <li class="sk-tag">JavaScript</li>
                  <li class="sk-tag">Responsive Web Design</li>
                  <li class="sk-tag">Cross-Browser Development</li>
                  <li class="sk-tag">DOM Manipulation</li>
                  <li class="sk-tag">AJAX</li>
                  <li class="sk-tag">REST API Integration</li>
                  <li class="sk-tag">Form Development</li>
                  <li class="sk-tag">Client-Side Validation</li>
                  <li class="sk-tag">Interactive Web Interfaces</li>
                  <li class="sk-tag">UI Implementation</li>
                </ul>
              </div>
              <div>
                <h4 class="sub-group-title"><i class="fa-solid fa-server"></i> Backend & Ecosystems</h4>
                <ul class="skills-item-tags">
                  <li class="sk-tag">PHP</li>
                  <li class="sk-tag">Laravel</li>
                  <li class="sk-tag">Blazor Server</li>
                  <li class="sk-tag">C# / ASP.NET</li>
                  <li class="sk-tag">Python</li>
                  <li class="sk-tag">Django</li>
                  <li class="sk-tag">Node.js</li>
                  <li class="sk-tag">RESTful API Development</li>
                  <li class="sk-tag">Server-Side Programming</li>
                  <li class="sk-tag">Authentication & Authorization</li>
                  <li class="sk-tag">Session Management</li>
                  <li class="sk-tag">CRUD Application Development</li>
                  <li class="sk-tag">Backend Architecture</li>
                  <li class="sk-tag">MVC Architecture</li>
                  <li class="sk-tag">JSON</li>
                  <li class="sk-tag">HTTP/HTTPS</li>
                  <li class="sk-tag">Database-Driven Applications</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- TAB 3: DATABASES -->
          <div id="tab-database" class="skill-panel">
            <h3 class="panel-section-title">Databases & Data Modeling</h3>
            <p class="panel-section-intro">Relational query design, indexing models, document-oriented NoSQL databases, and administrative configurations.</p>
            
            <div class="panel-sub-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px;">
              <div>
                <h4 class="sub-group-title"><i class="fa-solid fa-database"></i> Relational Systems</h4>
                <ul class="skills-item-tags">
                  <li class="sk-tag">MySQL</li>
                  <li class="sk-tag">MariaDB</li>
                  <li class="sk-tag">Oracle Database</li>
                  <li class="sk-tag">Microsoft Access</li>
                  <li class="sk-tag">SQL Queries</li>
                  <li class="sk-tag">Joins & Subqueries</li>
                  <li class="sk-tag">Aggregation</li>
                  <li class="sk-tag">Grouping & Views</li>
                  <li class="sk-tag">Relational Database Design</li>
                  <li class="sk-tag">ER Modeling (Entity-Relationship)</li>
                  <li class="sk-tag">Normalization</li>
                  <li class="sk-tag">Primary & Foreign Keys</li>
                  <li class="sk-tag">Transactions</li>
                  <li class="sk-tag">Database Integration</li>
                  <li class="sk-tag">Database Administration Fundamentals</li>
                </ul>
              </div>
              <div>
                <h4 class="sub-group-title"><i class="fa-solid fa-network-wired"></i> NoSQL & Science Modeling</h4>
                <ul class="skills-item-tags">
                  <li class="sk-tag">MongoDB</li>
                  <li class="sk-tag">Document-Oriented Database Design</li>
                  <li class="sk-tag">NoSQL Data Modeling</li>
                  <li class="sk-tag">MongoDB Aggregation</li>
                  <li class="sk-tag">Python-MongoDB Integration</li>
                  <li class="sk-tag">CRUD Operations</li>
                  <li class="sk-tag">Data Validation</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- TAB 4: AI & ML -->
          <div id="tab-ai" class="skill-panel">
            <h3 class="panel-section-title">Machine Learning, AI & Scientific Computing</h3>
            <p class="panel-section-intro">Mathematical models, training evaluation routines, vectorized neural layers, and scientific visualization environments.</p>
            
            <div class="panel-sub-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px;">
              <div>
                <h4 class="sub-group-title"><i class="fa-solid fa-robot"></i> AI / Machine Learning</h4>
                <ul class="skills-item-tags">
                  <li class="sk-tag">Supervised Learning</li>
                  <li class="sk-tag">Unsupervised Learning</li>
                  <li class="sk-tag">Classification & Regression</li>
                  <li class="sk-tag">Clustering & PCA</li>
                  <li class="sk-tag">Model Evaluation & Training</li>
                  <li class="sk-tag">Gradient Descent</li>
                  <li class="sk-tag">Linear / Logistic Regression</li>
                  <li class="sk-tag">Support Vector Machines</li>
                  <li class="sk-tag">Gaussian Kernels</li>
                  <li class="sk-tag">Hyperparameter Optimization</li>
                  <li class="sk-tag">Intelligent Systems</li>
                  <li class="sk-tag">Robotics</li>
                </ul>
              </div>
              <div>
                <h4 class="sub-group-title"><i class="fa-solid fa-calculator"></i> Data Science & Tools</h4>
                <ul class="skills-item-tags">
                  <li class="sk-tag">Python Data Science</li>
                  <li class="sk-tag">NumPy</li>
                  <li class="sk-tag">SciPy</li>
                  <li class="sk-tag">Matplotlib</li>
                  <li class="sk-tag">TensorFlow</li>
                  <li class="sk-tag">PyTorch</li>
                  <li class="sk-tag">Scikit-learn</li>
                  <li class="sk-tag">Jupyter Notebook</li>
                  <li class="sk-tag">SVD (Singular Value Decomposition)</li>
                  <li class="sk-tag">Matrix Operations</li>
                  <li class="sk-tag">Mathematical Modeling</li>
                  <li class="sk-tag">Numerical Optimization</li>
                  <li class="sk-tag">Eigenvalues & Eigenvectors</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- TAB 5: ALGORITHMS -->
          <div id="tab-alg" class="skill-panel">
            <h3 class="panel-section-title">Algorithms & Computational Complexity</h3>
            <p class="panel-section-intro">Discrete data structures, graph search, and complexity bounds optimization modeling.</p>
            
            <div class="panel-sub-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px;">
              <div>
                <h4 class="sub-group-title"><i class="fa-solid fa-folder-tree"></i> Data Structures</h4>
                <ul class="skills-item-tags">
                  <li class="sk-tag">Arrays</li>
                  <li class="sk-tag">Linked Lists</li>
                  <li class="sk-tag">Stacks</li>
                  <li class="sk-tag">Queues</li>
                  <li class="sk-tag">Trees & Heaps</li>
                  <li class="sk-tag">Graphs</li>
                  <li class="sk-tag">Hash Tables</li>
                  <li class="sk-tag">Matrices</li>
                </ul>
              </div>
              <div>
                <h4 class="sub-group-title"><i class="fa-solid fa-gears"></i> Algorithms & Theory</h4>
                <ul class="skills-item-tags">
                  <li class="sk-tag">Merge Sort / Quick Sort</li>
                  <li class="sk-tag">Dijkstra's Algorithm</li>
                  <li class="sk-tag">Floyd-Warshall Algorithm</li>
                  <li class="sk-tag">Karatsuba Multiplication</li>
                  <li class="sk-tag">Bin Packing</li>
                  <li class="sk-tag">Greedy Algorithms</li>
                  <li class="sk-tag">Dynamic Programming</li>
                  <li class="sk-tag">Time & Space Complexity</li>
                  <li class="sk-tag">Big-O Analysis</li>
                  <li class="sk-tag">Computational Complexity</li>
                  <li class="sk-tag">Problem Decomposition</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- TAB 6: CRYPTOGRAPHY -->
          <div id="tab-crypto" class="skill-panel">
            <h3 class="panel-section-title">Cybersecurity & Cryptography</h3>
            <p class="panel-section-intro">Asymmetric encryption algorithms, number theory keys, and network security credentials validation.</p>
            
            <div class="panel-sub-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px;">
              <div>
                <h4 class="sub-group-title"><i class="fa-solid fa-key"></i> Cryptographic Logic</h4>
                <ul class="skills-item-tags">
                  <li class="sk-tag">Symmetric & Asymmetric Cryptography</li>
                  <li class="sk-tag">RSA Encryption</li>
                  <li class="sk-tag">AES / DES</li>
                  <li class="sk-tag">ElGamal Encryption</li>
                  <li class="sk-tag">SHA-256</li>
                  <li class="sk-tag">Diffie-Hellman Key Exchange</li>
                  <li class="sk-tag">Password Hashing & Salting</li>
                  <li class="sk-tag">Digital Signatures</li>
                  <li class="sk-tag">Hash Functions</li>
                  <li class="sk-tag">Zero-Knowledge Proofs</li>
                </ul>
              </div>
              <div>
                <h4 class="sub-group-title"><i class="fa-solid fa-shield"></i> Security & Number Theory</h4>
                <ul class="skills-item-tags">
                  <li class="sk-tag">Modular Arithmetic / Inverses</li>
                  <li class="sk-tag">Euler's Totient Function</li>
                  <li class="sk-tag">Fermat's Little Theorem</li>
                  <li class="sk-tag">Miller-Rabin Primality Testing</li>
                  <li class="sk-tag">Network Credentials & Secure Sockets</li>
                  <li class="sk-tag">Kerberos Protocol</li>
                  <li class="sk-tag">Feige-Fiat-Shamir</li>
                  <li class="sk-tag">Wide-Mouth-Frog Protocol</li>
                  <li class="sk-tag">Secure Password Storage</li>
                  <li class="sk-tag">Cryptographic Key Management</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- TAB 7: CLOUD & INFRASTRUCTURE -->
          <div id="tab-cloud" class="skill-panel">
            <h3 class="panel-section-title">Cloud, DevOps & IT Infrastructure</h3>
            <p class="panel-section-intro">Containerized virtualization, subnet networks design, and cloud application deployments.</p>
            
            <div class="panel-sub-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px;">
              <div>
                <h4 class="sub-group-title"><i class="fa-solid fa-cloud"></i> Cloud & DevOps</h4>
                <ul class="skills-item-tags">
                  <li class="sk-tag">Amazon Web Services (AWS)</li>
                  <li class="sk-tag">Docker Containerization</li>
                  <li class="sk-tag">Git / GitHub</li>
                  <li class="sk-tag">Version Control & Branching</li>
                  <li class="sk-tag">Software Deployment Fundamentals</li>
                  <li class="sk-tag">VirtualBox</li>
                  <li class="sk-tag">Virtual Machine Management</li>
                  <li class="sk-tag">Cloud-Based Application Concepts</li>
                </ul>
              </div>
              <div>
                <h4 class="sub-group-title"><i class="fa-solid fa-network-wired"></i> Network Architectures</h4>
                <ul class="skills-item-tags">
                  <li class="sk-tag">TCP/IP Protocol Suite</li>
                  <li class="sk-tag">IP Addressing & Subnetting</li>
                  <li class="sk-tag">LAN / WAN Architectures</li>
                  <li class="sk-tag">Routing & Switching</li>
                  <li class="sk-tag">Cisco Networking (CCNA Concept)</li>
                  <li class="sk-tag">MikroTik (MTCNA Concept)</li>
                  <li class="sk-tag">Hardware & Software Troubleshooting</li>
                  <li class="sk-tag">System Administration Fundamentals</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- TAB 8: UI/UX & MULTIMEDIA -->
          <div id="tab-design" class="skill-panel">
            <h3 class="panel-section-title">UI/UX & Web Design</h3>
            <p class="panel-section-intro">Visual hierarchy, modular design systems prototyping, wireframes, and vector modeling tools.</p>
            
            <div class="panel-sub-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px;">
              <div>
                <h4 class="sub-group-title"><i class="fa-solid fa-pen-fancy"></i> UI/UX Design</h4>
                <ul class="skills-item-tags">
                  <li class="sk-tag">User Interface Design</li>
                  <li class="sk-tag">User Experience Design</li>
                  <li class="sk-tag">Wireframing & Prototyping</li>
                  <li class="sk-tag">Responsive Layout Design</li>
                  <li class="sk-tag">Design Systems</li>
                  <li class="sk-tag">Visual Hierarchy</li>
                  <li class="sk-tag">Typography</li>
                  <li class="sk-tag">Color Theory</li>
                  <li class="sk-tag">Figma</li>
                </ul>
              </div>
              <div>
                <h4 class="sub-group-title"><i class="fa-solid fa-palette"></i> Multimedia & Graphics</h4>
                <ul class="skills-item-tags">
                  <li class="sk-tag">Adobe Photoshop</li>
                  <li class="sk-tag">Adobe Illustrator</li>
                  <li class="sk-tag">Adobe InDesign</li>
                  <li class="sk-tag">CorelDRAW</li>
                  <li class="sk-tag">Blender 3D Modeling</li>
                  <li class="sk-tag">Computer Graphics</li>
                  <li class="sk-tag">Digital Image Processing</li>
                  <li class="sk-tag">Logo & Brand Identity Design</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- TAB 9: ACADEMIC & RESEARCH -->
          <div id="tab-academic" class="skill-panel">
            <h3 class="panel-section-title">Academic Instruction & Technical Research</h3>
            <p class="panel-section-intro">Theoretical research methodology, advanced scientific writing, syllabus curriculum delivery, and students mentorship.</p>
            
            <div class="panel-sub-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px;">
              <div>
                <h4 class="sub-group-title"><i class="fa-solid fa-magnifying-glass-chart"></i> Research Specialties</h4>
                <ul class="skills-item-tags">
                  <li class="sk-tag">Computer Science Research</li>
                  <li class="sk-tag">Research Methodology</li>
                  <li class="sk-tag">Literature Analysis & Review</li>
                  <li class="sk-tag">Technical & Academic Writing</li>
                  <li class="sk-tag">Experimental / Research Design</li>
                  <li class="sk-tag">Problem Formulation</li>
                  <li class="sk-tag">Research Presentation</li>
                </ul>
              </div>
              <div>
                <h4 class="sub-group-title"><i class="fa-solid fa-chalkboard-user"></i> Teaching & Assessment</h4>
                <ul class="skills-item-tags">
                  <li class="sk-tag">Programming Instruction</li>
                  <li class="sk-tag">Web Development Instruction</li>
                  <li class="sk-tag">Database Instruction</li>
                  <li class="sk-tag">Computer Graphics Instruction</li>
                  <li class="sk-tag">Practical Laboratory Instruction</li>
                  <li class="sk-tag">Student Mentoring & Assessment</li>
                  <li class="sk-tag">Academic Content Development</li>
                </ul>
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>
  </section>
</main>

<style>
  /* INTERACTIVE COMPACT MATRIX STYLES */
  .skill-nav-btn {
    border: 1px solid var(--light-gray);
    background: var(--soft-bg);
    color: var(--muted);
    padding: 13px 18px;
    text-align: left;
    font-family: inherit;
    font-size: 0.88rem;
    font-weight: 700;
    cursor: pointer;
    line-height: 1.2;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 12px;
  }
  
  .skill-nav-btn .num {
    font-size: 0.72rem;
    font-weight: 800;
    color: var(--accent-muted);
    letter-spacing: 0.05em;
  }
  
  .skill-nav-btn:hover {
    background: rgba(15, 23, 42, 0.02);
    color: var(--text);
    border-color: var(--text);
  }
  
  .skill-nav-btn.active {
    background: var(--text);
    color: #fff;
    border-color: var(--text);
  }
  
  .skill-nav-btn.active .num {
    color: var(--accent);
  }

  .skill-panel {
    display: none;
  }
  
  .skill-panel.active {
    display: block;
    animation: fadeInTab 0.3s ease forwards;
  }

  @keyframes fadeInTab {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .panel-section-title {
    margin: 0 0 6px;
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.04em;
    color: var(--text);
  }

  .panel-section-intro {
    margin: 0 0 24px;
    font-size: 0.94rem;
    color: var(--muted);
  }

  .sub-group-title {
    margin: 0 0 12px;
    font-size: 0.94rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 8px;
  }

  /* HIGH COMPACT TEXTURE TAGS */
  .skills-item-tags {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .skills-item-tags .sk-tag {
    background: var(--bg);
    border: 1px solid var(--light-gray);
    padding: 6px 10px;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text);
  }

  .skills-item-tags.spec .sk-tag-flat {
    background: var(--bg);
    border: 1px solid var(--light-gray);
    padding: 6px 10px;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text);
    display: inline-block;
    margin: 0 6px 6px 0;
  }

  /* RESPONSIVE MATRIX OVERLAYS */
  @media (max-width: 900px) {
    .skills-layout-matrix {
      grid-template-columns: 1fr !important;
    }
    
    .skills-categories-nav {
      flex-direction: row !important;
      flex-wrap: wrap;
    }
    
    .skill-nav-btn {
      flex: 1;
      min-width: 140px;
      padding: 10px 14px;
      font-size: 0.8rem;
    }
  }
</style>

<script>
  // INSTANT TAB SWITCHING INTERACTIVE ENGINE
  function switchSkillTab(tabId) {
    // Nav Button Highlight States
    const buttons = document.querySelectorAll('.skill-nav-btn');
    buttons.forEach(btn => {
      btn.classList.remove('active');
    });

    // Content Display Switching
    const panels = document.querySelectorAll('.skill-panel');
    panels.forEach(p => {
      p.classList.remove('active');
    });

    // Toggle selected button active state
    event.currentTarget.classList.add('active');
    document.getElementById(tabId).classList.add('active');
  }

  // INSTANT CONTEXT FILTER MATRIX SEARCH ENGINE
  document.getElementById('skillsSearch').addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase().trim();
    const tags = document.querySelectorAll('.skills-item-tags li, .skills-item-tags.spec .sk-tag-flat');
    const sections = document.querySelectorAll('.skill-panel');
    const sideNav = document.querySelector('.skills-categories-nav');
    const tabsContent = document.querySelector('.skills-tab-content');

    if (query === '') {
      // Restore default tab views
      sideNav.style.display = 'flex';
      tabsContent.style.border = '1px solid var(--light-gray)';
      tabsContent.style.padding = '30px';
      
      sections.forEach(p => {
        p.classList.remove('active');
        p.style.display = 'none';
        p.style.marginBottom = '0';
      });

      // Show active tab
      const currentActiveBtn = document.querySelector('.skill-nav-btn.active');
      let selectId = 'tab-software';
      if (currentActiveBtn) {
        // Extract tab target from current button action block
        const clickAttr = currentActiveBtn.getAttribute('onclick');
        const match = clickAttr.match(/\'(.*?)\'/);
        if (match) selectId = match[1];
      }
      const activePanel = document.getElementById(selectId);
      activePanel.classList.add('active');
      activePanel.style.display = 'block';

      // Restore baseline tags states
      tags.forEach(tag => {
        tag.style.display = 'inline-block';
        tag.style.opacity = '1';
        tag.style.background = 'var(--bg)';
        tag.style.color = 'var(--text)';
        tag.style.borderColor = 'var(--light-gray)';
      });
      return;
    }

    // Hide sidebar tabs when typing search queries to show universal filter match layout
    sideNav.style.display = 'none';
    tabsContent.style.padding = '20px';

    let matchCount = 0;

    // Filter tags
    tags.forEach(tag => {
      const txt = tag.textContent.toLowerCase();
      if (txt.includes(query)) {
        tag.style.display = 'inline-block';
        tag.style.opacity = '1';
        tag.style.background = 'var(--accent)';
        tag.style.color = '#fff';
        tag.style.borderColor = 'var(--accent)';
        matchCount++;
      } else {
        tag.style.display = 'none';
        tag.style.opacity = '0.15';
      }
    });

    // Make all matching panels containing tags visible
    sections.forEach(p => {
      p.style.display = 'block';
      p.classList.add('active');
      p.style.marginBottom = '40px';
      
      // Check if this panel has any visible tags
      const panelTags = p.querySelectorAll('.skills-item-tags li, .skills-item-tags.spec .sk-tag-flat');
      let hasVisible = false;
      panelTags.forEach(t => {
        if (t.style.display !== 'none') hasVisible = true;
      });

      if (hasVisible) {
        p.style.display = 'block';
      } else {
        p.style.display = 'none';
      }
    });
  });
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
