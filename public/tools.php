<?php
$activePage = 'tools';
require_once __DIR__ . '/header.php';
?>
<main id="main-content" class="container">
  <section class="section-row" style="border-bottom:none;">
    <div class="section-label">Tools</div>
    <div class="section-content">
      <div class="page-hero-mini">
        <h2>Byte Lab</h2>
        <p>Quick encoding helpers for reverse-engineering triage and day-to-day debugging — runs entirely in your browser.</p>
      </div>
      <div class="tool-panel reveal-on-enter">
        <div>
          <label for="byte-input">Input</label>
          <textarea id="byte-input" placeholder="Paste text or hex…"></textarea>
        </div>
        <div class="tool-actions">
          <button type="button" class="btn primary" id="to-b64">To Base64</button>
          <button type="button" class="btn" id="from-b64">From Base64</button>
          <button type="button" class="btn" id="to-hex">To Hex</button>
          <button type="button" class="btn" id="from-hex">From Hex</button>
          <button type="button" class="btn" id="to-upper">UPPER</button>
          <button type="button" class="btn" id="byte-clear">Clear</button>
        </div>
        <div>
          <label for="byte-output">Output</label>
          <textarea id="byte-output" readonly placeholder="Result appears here"></textarea>
        </div>
      </div>
    </div>
  </section>
</main>
<script>
(function () {
  const input = document.getElementById('byte-input');
  const output = document.getElementById('byte-output');
  const enc = new TextEncoder();
  const dec = new TextDecoder();

  function toB64(str) {
    const bytes = enc.encode(str);
    let bin = '';
    bytes.forEach(b => { bin += String.fromCharCode(b); });
    return btoa(bin);
  }
  function fromB64(str) {
    const bin = atob(str.trim());
    const bytes = Uint8Array.from(bin, c => c.charCodeAt(0));
    return dec.decode(bytes);
  }
  function toHex(str) {
    return Array.from(enc.encode(str)).map(b => b.toString(16).padStart(2, '0')).join(' ');
  }
  function fromHex(str) {
    const clean = str.replace(/[^0-9a-fA-F]/g, '');
    if (clean.length % 2) throw new Error('Odd hex length');
    const bytes = new Uint8Array(clean.length / 2);
    for (let i = 0; i < clean.length; i += 2) bytes[i / 2] = parseInt(clean.slice(i, i + 2), 16);
    return dec.decode(bytes);
  }
  function run(fn) {
    try { output.value = fn(input.value); }
    catch (e) { output.value = 'Error: ' + e.message; }
  }
  document.getElementById('to-b64').onclick = () => run(toB64);
  document.getElementById('from-b64').onclick = () => run(fromB64);
  document.getElementById('to-hex').onclick = () => run(toHex);
  document.getElementById('from-hex').onclick = () => run(fromHex);
  document.getElementById('to-upper').onclick = () => { output.value = input.value.toUpperCase(); };
  document.getElementById('byte-clear').onclick = () => { input.value = ''; output.value = ''; };
})();
</script>
<?php require_once __DIR__ . '/footer.php'; ?>
