<?php
$activePage = 'skills';
require_once __DIR__ . '/header.php';

$skillCatalog = [
  [
    'id' => 'software',
    'title' => 'Software Development',
    'intro' => 'Core languages and concepts that support the full software lifecycle.',
    'groups' => [
      [
        'icon' => 'fa-code',
        'title' => 'Programming Languages',
        'items' => ['Python', 'JavaScript', 'C', 'C++', 'PHP', 'SQL', 'HTML5', 'CSS3'],
      ],
      [
        'icon' => 'fa-brain',
        'title' => 'Programming Concepts',
        'items' => [
          'Object-Oriented Programming', 'Functional Programming Concepts', 'Procedural Programming',
          'Data Structures', 'Algorithms', 'Algorithm Design', 'Computational Problem Solving',
          'Modular Programming', 'Code Reusability', 'Exception Handling', 'File Handling',
          'Debugging', 'Software Testing', 'Code Optimization',
        ],
      ],
    ],
  ],
  [
    'id' => 'web',
    'title' => 'Web Engineering',
    'intro' => 'Full-stack architectures, API work, and client-side interface patterns.',
    'groups' => [
      [
        'icon' => 'fa-laptop-code',
        'title' => 'Front-End',
        'items' => [
          'React.js', 'Bootstrap', 'jQuery', 'HTML5', 'CSS3', 'JavaScript',
          'Responsive Web Design', 'Cross-Browser Development', 'DOM Manipulation', 'AJAX',
          'REST API Integration', 'Form Development', 'Client-Side Validation',
          'Interactive Web Interfaces', 'UI Implementation',
        ],
      ],
      [
        'icon' => 'fa-server',
        'title' => 'Backend & Ecosystems',
        'items' => [
          'PHP', 'Laravel', 'Blazor Server', 'C# / ASP.NET', 'Python', 'Django', 'Node.js',
          'RESTful API Development', 'Server-Side Programming', 'Authentication & Authorization',
          'Session Management', 'CRUD Application Development', 'Backend Architecture',
          'MVC Architecture', 'JSON', 'HTTP/HTTPS', 'Database-Driven Applications',
        ],
      ],
    ],
  ],
  [
    'id' => 'database',
    'title' => 'Databases & Storage',
    'intro' => 'Relational design, query work, and document-oriented data modeling.',
    'groups' => [
      [
        'icon' => 'fa-database',
        'title' => 'Relational Systems',
        'items' => [
          'MySQL', 'MariaDB', 'Oracle Database', 'Microsoft Access', 'SQL Queries',
          'Joins & Subqueries', 'Aggregation', 'Grouping & Views', 'Relational Database Design',
          'ER Modeling', 'Normalization', 'Primary & Foreign Keys', 'Transactions',
          'Database Integration', 'Database Administration Fundamentals',
        ],
      ],
      [
        'icon' => 'fa-network-wired',
        'title' => 'NoSQL & Science Modeling',
        'items' => [
          'MongoDB', 'Document-Oriented Database Design', 'NoSQL Data Modeling',
          'MongoDB Aggregation', 'Python-MongoDB Integration', 'CRUD Operations', 'Data Validation',
        ],
      ],
    ],
  ],
  [
    'id' => 'ai',
    'title' => 'AI & Machine Learning',
    'intro' => 'Models, training loops, and scientific computing tools used in practice.',
    'groups' => [
      [
        'icon' => 'fa-robot',
        'title' => 'AI / Machine Learning',
        'items' => [
          'Supervised Learning', 'Unsupervised Learning', 'Classification & Regression',
          'Clustering & PCA', 'Model Evaluation & Training', 'Gradient Descent',
          'Linear / Logistic Regression', 'Support Vector Machines', 'Gaussian Kernels',
          'Hyperparameter Optimization', 'Intelligent Systems', 'Robotics',
        ],
      ],
      [
        'icon' => 'fa-calculator',
        'title' => 'Data Science & Tools',
        'items' => [
          'Python Data Science', 'NumPy', 'SciPy', 'Matplotlib', 'TensorFlow', 'PyTorch',
          'Scikit-learn', 'Jupyter Notebook', 'SVD', 'Matrix Operations',
          'Mathematical Modeling', 'Numerical Optimization', 'Eigenvalues & Eigenvectors',
        ],
      ],
    ],
  ],
  [
    'id' => 'alg',
    'title' => 'Algorithms & Complexity',
    'intro' => 'Data structures, graph search, and complexity-aware problem solving.',
    'groups' => [
      [
        'icon' => 'fa-folder-tree',
        'title' => 'Data Structures',
        'items' => ['Arrays', 'Linked Lists', 'Stacks', 'Queues', 'Trees & Heaps', 'Graphs', 'Hash Tables', 'Matrices'],
      ],
      [
        'icon' => 'fa-gears',
        'title' => 'Algorithms & Theory',
        'items' => [
          'Merge Sort / Quick Sort', "Dijkstra's Algorithm", 'Floyd-Warshall Algorithm',
          'Karatsuba Multiplication', 'Bin Packing', 'Greedy Algorithms', 'Dynamic Programming',
          'Time & Space Complexity', 'Big-O Analysis', 'Computational Complexity', 'Problem Decomposition',
        ],
      ],
    ],
  ],
  [
    'id' => 'crypto',
    'title' => 'Security & Cryptography',
    'intro' => 'Encryption, hashing, and protocol ideas grounded in number theory.',
    'groups' => [
      [
        'icon' => 'fa-key',
        'title' => 'Cryptographic Logic',
        'items' => [
          'Symmetric & Asymmetric Cryptography', 'RSA Encryption', 'AES / DES', 'ElGamal Encryption',
          'SHA-256', 'Diffie-Hellman Key Exchange', 'Password Hashing & Salting',
          'Digital Signatures', 'Hash Functions', 'Zero-Knowledge Proofs',
        ],
      ],
      [
        'icon' => 'fa-shield',
        'title' => 'Security & Number Theory',
        'items' => [
          'Modular Arithmetic / Inverses', "Euler's Totient Function", "Fermat's Little Theorem",
          'Miller-Rabin Primality Testing', 'Network Credentials & Secure Sockets',
          'Kerberos Protocol', 'Feige-Fiat-Shamir', 'Wide-Mouth-Frog Protocol',
          'Secure Password Storage', 'Cryptographic Key Management',
        ],
      ],
    ],
  ],
  [
    'id' => 'cloud',
    'title' => 'Cloud & IT Infrastructure',
    'intro' => 'Deployment basics, containers, version control, and network foundations.',
    'groups' => [
      [
        'icon' => 'fa-cloud',
        'title' => 'Cloud & DevOps',
        'items' => [
          'Amazon Web Services (AWS)', 'Docker Containerization', 'Git / GitHub',
          'Version Control & Branching', 'Software Deployment Fundamentals', 'VirtualBox',
          'Virtual Machine Management', 'Cloud-Based Application Concepts',
        ],
      ],
      [
        'icon' => 'fa-network-wired',
        'title' => 'Network Architectures',
        'items' => [
          'TCP/IP Protocol Suite', 'IP Addressing & Subnetting', 'LAN / WAN Architectures',
          'Routing & Switching', 'Cisco Networking (CCNA Concept)', 'MikroTik (MTCNA Concept)',
          'Hardware & Software Troubleshooting', 'System Administration Fundamentals',
        ],
      ],
    ],
  ],
  [
    'id' => 'design',
    'title' => 'UI/UX & Digital Design',
    'intro' => 'Interface craft, layout systems, and visual tooling for product work.',
    'groups' => [
      [
        'icon' => 'fa-pen-fancy',
        'title' => 'UI/UX Design',
        'items' => [
          'User Interface Design', 'User Experience Design', 'Wireframing & Prototyping',
          'Responsive Layout Design', 'Design Systems', 'Visual Hierarchy', 'Typography',
          'Color Theory', 'Figma',
        ],
      ],
      [
        'icon' => 'fa-palette',
        'title' => 'Multimedia & Graphics',
        'items' => [
          'Adobe Photoshop', 'Adobe Illustrator', 'Adobe InDesign', 'CorelDRAW',
          'Blender 3D Modeling', 'Computer Graphics', 'Digital Image Processing',
          'Logo & Brand Identity Design',
        ],
      ],
    ],
  ],
  [
    'id' => 'academic',
    'title' => 'Academia, Research & Teaching',
    'intro' => 'Research practice, technical writing, and classroom delivery.',
    'groups' => [
      [
        'icon' => 'fa-magnifying-glass-chart',
        'title' => 'Research Specialties',
        'items' => [
          'Computer Science Research', 'Research Methodology', 'Literature Analysis & Review',
          'Technical & Academic Writing', 'Experimental / Research Design',
          'Problem Formulation', 'Research Presentation',
        ],
      ],
      [
        'icon' => 'fa-chalkboard-user',
        'title' => 'Teaching & Assessment',
        'items' => [
          'Programming Instruction', 'Web Development Instruction', 'Database Instruction',
          'Computer Graphics Instruction', 'Practical Laboratory Instruction',
          'Student Mentoring & Assessment', 'Academic Content Development',
        ],
      ],
    ],
  ],
];

$skillTotal = 0;
foreach ($skillCatalog as $cat) {
  foreach ($cat['groups'] as $group) {
    $skillTotal += count($group['items']);
  }
}

?>

<main id="main-content" class="sk-shell">
  <div class="sk-shell__bg" aria-hidden="true"></div>

  <section class="container sk-page">
    <header class="sk-hero reveal-on-enter">
      <div class="sk-hero__copy">
        <p class="sk-hero__eyebrow">Toolkit</p>
        <h1 class="sk-hero__title">Skills</h1>
        <p class="sk-hero__lede">
          Languages, systems, research methods, and teaching practice organized by domain.
          Search the matrix or browse a category to find a specific tool or concept.
        </p>
        <div class="sk-hero__meta">
          <span class="sk-hero__count"><?php echo (int) $skillTotal; ?> skills mapped</span>
          <span class="sk-hero__count sk-hero__count--muted"><?php echo count($skillCatalog); ?> domains</span>
          <a class="sk-hero__jump" href="<?php echo appUrl('public/projects.php'); ?>">See projects</a>
          <a class="sk-hero__jump sk-hero__jump--muted" href="<?php echo appUrl('public/experience.php'); ?>">View experience</a>
        </div>
      </div>
    </header>

    <div class="sk-search reveal-on-enter">
      <label class="sk-search__label" for="skillsSearch">Search skills</label>
      <div class="sk-search__field">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <input
          type="search"
          id="skillsSearch"
          placeholder="Search skills, libraries, or algorithms (e.g. RSA, Blazor, PyTorch, SQL)"
          autocomplete="off"
        >
      </div>
      <p class="sk-search__status" id="skillsSearchStatus" hidden></p>
    </div>

    <div class="sk-layout">
      <aside class="sk-nav" aria-label="Skill domains">
        <?php foreach ($skillCatalog as $index => $cat): ?>
          <button
            type="button"
            class="sk-nav__btn<?php echo $index === 0 ? ' is-active' : ''; ?>"
            data-tab="tab-<?php echo htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8'); ?>"
          >
            <span class="sk-nav__num"><?php echo str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT); ?></span>
            <span class="sk-nav__label"><?php echo htmlspecialchars($cat['title'], ENT_QUOTES, 'UTF-8'); ?></span>
          </button>
        <?php endforeach; ?>
      </aside>

      <div class="sk-panels" id="skillsPanels">
        <?php foreach ($skillCatalog as $index => $cat):
          $panelId = 'tab-' . $cat['id'];
          $itemCount = 0;
          foreach ($cat['groups'] as $group) {
            $itemCount += count($group['items']);
          }
        ?>
          <article
            id="<?php echo htmlspecialchars($panelId, ENT_QUOTES, 'UTF-8'); ?>"
            class="sk-panel<?php echo $index === 0 ? ' is-active' : ''; ?>"
            data-category="<?php echo htmlspecialchars($cat['title'], ENT_QUOTES, 'UTF-8'); ?>"
          >
            <header class="sk-panel__head">
              <h2 class="sk-panel__title"><?php echo htmlspecialchars($cat['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
              <p class="sk-panel__intro"><?php echo htmlspecialchars($cat['intro'], ENT_QUOTES, 'UTF-8'); ?></p>
              <p class="sk-panel__count"><?php echo (int) $itemCount; ?> items in this domain</p>
            </header>

            <div class="sk-panel__groups">
              <?php foreach ($cat['groups'] as $group): ?>
                <section class="sk-group">
                  <h3 class="sk-group__title">
                    <i class="fa-solid <?php echo htmlspecialchars($group['icon'], ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
                    <?php echo htmlspecialchars($group['title'], ENT_QUOTES, 'UTF-8'); ?>
                  </h3>
                  <ul class="sk-tags">
                    <?php foreach ($group['items'] as $item): ?>
                      <li class="sk-tag" data-skill="<?php echo htmlspecialchars(strtolower($item), ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($item, ENT_QUOTES, 'UTF-8'); ?>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </section>
              <?php endforeach; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>

    <aside class="sk-cta reveal-on-enter">
      <div>
        <p class="sk-cta__eyebrow">Next</p>
        <h2 class="sk-cta__title">See these skills show up in shipped work.</h2>
      </div>
      <div class="sk-cta__actions">
        <a class="sk-cta__btn sk-cta__btn--primary" href="<?php echo appUrl('public/projects.php'); ?>">Projects</a>
        <a class="sk-cta__btn" href="<?php echo appUrl('public/contact.php'); ?>">Get in touch</a>
      </div>
    </aside>
  </section>
</main>

<script>
(() => {
  const nav = document.querySelector('.sk-nav');
  const buttons = [...document.querySelectorAll('.sk-nav__btn')];
  const panels = [...document.querySelectorAll('.sk-panel')];
  const tags = [...document.querySelectorAll('.sk-tag')];
  const search = document.getElementById('skillsSearch');
  const status = document.getElementById('skillsSearchStatus');
  const panelsWrap = document.getElementById('skillsPanels');

  function activateTab(tabId) {
    buttons.forEach((btn) => {
      btn.classList.toggle('is-active', btn.dataset.tab === tabId);
    });
    panels.forEach((panel) => {
      const on = panel.id === tabId;
      panel.classList.toggle('is-active', on);
      panel.hidden = !on;
    });
  }

  buttons.forEach((btn) => {
    btn.addEventListener('click', () => {
      if (search.value.trim() !== '') {
        search.value = '';
        clearSearch();
      }
      activateTab(btn.dataset.tab);
    });
  });

  function clearSearch() {
    nav.hidden = false;
    panelsWrap.classList.remove('is-searching');
    status.hidden = true;
    status.textContent = '';
    tags.forEach((tag) => {
      tag.hidden = false;
      tag.classList.remove('is-match');
    });
    document.querySelectorAll('.sk-group').forEach((group) => {
      group.hidden = false;
    });
    const activeBtn = document.querySelector('.sk-nav__btn.is-active');
    activateTab(activeBtn ? activeBtn.dataset.tab : buttons[0].dataset.tab);
  }

  search.addEventListener('input', () => {
    const query = search.value.toLowerCase().trim();
    if (query === '') {
      clearSearch();
      return;
    }

    nav.hidden = true;
    panelsWrap.classList.add('is-searching');
    let matches = 0;

    panels.forEach((panel) => {
      let panelHasMatch = false;
      panel.querySelectorAll('.sk-group').forEach((group) => {
        let groupHasMatch = false;
        group.querySelectorAll('.sk-tag').forEach((tag) => {
          const hit = (tag.dataset.skill || tag.textContent.toLowerCase()).includes(query);
          tag.hidden = !hit;
          tag.classList.toggle('is-match', hit);
          if (hit) {
            groupHasMatch = true;
            panelHasMatch = true;
            matches += 1;
          }
        });
        group.hidden = !groupHasMatch;
      });
      panel.hidden = !panelHasMatch;
      panel.classList.toggle('is-active', panelHasMatch);
    });

    status.hidden = false;
    status.textContent = matches === 0
      ? 'No skills matched that search.'
      : matches + ' match' + (matches === 1 ? '' : 'es') + ' across the toolkit.';
  });

  activateTab(buttons[0].dataset.tab);
})();
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
