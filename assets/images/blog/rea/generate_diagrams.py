#!/usr/bin/env python3
"""Generate REA blog diagrams (Pillow). Run: python generate_diagrams.py"""
from __future__ import annotations

from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

OUT = Path(__file__).resolve().parent
W, H = 1600, 900
BLUE = (0, 87, 184)
BLUE_SOFT = (231, 241, 251)
INK = (17, 17, 17)
MUTED = (107, 114, 128)
LINE = (229, 231, 235)
WHITE = (255, 255, 255)
SLATE = (51, 65, 85)
ORANGE = (194, 98, 36)
GREEN = (22, 101, 52)
BG = (248, 250, 252)


def font(size: int, bold: bool = False) -> ImageFont.FreeTypeFont | ImageFont.ImageFont:
    candidates = [
        r"C:\Windows\Fonts\segoeuib.ttf" if bold else r"C:\Windows\Fonts\segoeui.ttf",
        r"C:\Windows\Fonts\arialbd.ttf" if bold else r"C:\Windows\Fonts\arial.ttf",
        r"C:\Windows\Fonts\calibrib.ttf" if bold else r"C:\Windows\Fonts\calibri.ttf",
    ]
    for path in candidates:
        try:
            return ImageFont.truetype(path, size)
        except OSError:
            continue
    return ImageFont.load_default()


def rounded(draw: ImageDraw.ImageDraw, box, fill, outline=None, width=2, radius=18):
    draw.rounded_rectangle(box, radius=radius, fill=fill, outline=outline, width=width)


def center_text(draw, xy, text, fnt, fill=INK):
    x, y = xy
    bbox = draw.textbbox((0, 0), text, font=fnt)
    tw, th = bbox[2] - bbox[0], bbox[3] - bbox[1]
    draw.text((x - tw / 2, y - th / 2), text, font=fnt, fill=fill)


def wrap_text(draw, text, fnt, max_width):
    words = text.split()
    lines, cur = [], ""
    for w in words:
        trial = f"{cur} {w}".strip()
        if draw.textbbox((0, 0), trial, font=fnt)[2] <= max_width:
            cur = trial
        else:
            if cur:
                lines.append(cur)
            cur = w
    if cur:
        lines.append(cur)
    return lines


def save(img: Image.Image, name: str):
    path = OUT / name
    img.save(path, "PNG", optimize=True)
    print("wrote", path.name)


def diagram_hero():
    img = Image.new("RGB", (W, H), WHITE)
    draw = ImageDraw.Draw(img)
    # soft gradient bands
    for i in range(H):
        t = i / H
        r = int(255 - t * 12)
        g = int(252 - t * 8)
        b = int(250 - t * 4)
        draw.line([(0, i), (W, i)], fill=(r, g, b))

    title = font(54, True)
    sub = font(26)
    small = font(20)
    center_text(draw, (W / 2, 70), "REA · Reverse Engineer Anything", title, INK)
    center_text(
        draw,
        (W / 2, 130),
        "One MCP server that gives coding agents local tools for binaries, apps, and runtime behavior",
        sub,
        MUTED,
    )

    steps = [
        ("1. Ask", "Agent + local target\nfeature question"),
        ("2. Inspect", "REA CLI / MCP\nadapters & providers"),
        ("3. Evidence", "Code · refs · unknowns\nauthenticated records"),
        ("4. Act", "Explain · rebuild\ntest against original"),
    ]
    box_w, box_h = 280, 180
    gap = 40
    total = len(steps) * box_w + (len(steps) - 1) * gap
    x0 = (W - total) / 2
    y = 280
    for i, (head, body) in enumerate(steps):
        x = x0 + i * (box_w + gap)
        rounded(draw, (x, y, x + box_w, y + box_h), BLUE_SOFT, BLUE, 3, 20)
        center_text(draw, (x + box_w / 2, y + 48), head, font(28, True), BLUE)
        for j, line in enumerate(body.split("\n")):
            center_text(draw, (x + box_w / 2, y + 100 + j * 28), line, small, SLATE)
        if i < len(steps) - 1:
            ax = x + box_w + 8
            ay = y + box_h / 2
            draw.line([(ax, ay), (ax + gap - 16, ay)], fill=BLUE, width=4)
            draw.polygon(
                [(ax + gap - 16, ay - 8), (ax + gap - 4, ay), (ax + gap - 16, ay + 8)],
                fill=BLUE,
            )

    footer = font(22)
    center_text(
        draw,
        (W / 2, H - 80),
        "Analysis stays on your machine · Findings carry evidence IDs · Agents decide what to rebuild",
        footer,
        MUTED,
    )
    save(img, "hero.png")


def diagram_investigation_flow():
    img = Image.new("RGB", (W, 1000), WHITE)
    draw = ImageDraw.Draw(img)
    draw.rectangle((0, 0, W, 1000), fill=BG)
    center_text(draw, (W / 2, 50), "Investigation loop", font(42, True), INK)
    center_text(
        draw,
        (W / 2, 100),
        "How an agent and REA trade questions for authenticated Evidence",
        font(22),
        MUTED,
    )

    nodes = [
        (200, 280, "You / Agent", "Prompt about one\nfeature or behavior", BLUE_SOFT, BLUE),
        (580, 280, "REA MCP / CLI", "Select adapter\nrun local analysis", WHITE, BLUE),
        (960, 280, "Provider layer", "Hopper · Ghidra · IDA\nJS · JADX · browser…", WHITE, SLATE),
        (1340, 280, "Evidence ledger", "normalized_result\nlimitations · coverage", BLUE_SOFT, GREEN),
        (580, 620, "Paged views", "inspect_analysis_view\nsummaries & facets", WHITE, ORANGE),
        (960, 620, "Follow-ups", "compare · match\ntrace · rebuild", WHITE, BLUE),
        (1340, 620, "Your project", "Explain or implement\ntest against original", BLUE_SOFT, GREEN),
    ]
    for x, y, title, body, fill, outline in nodes:
        rounded(draw, (x - 150, y - 90, x + 150, y + 90), fill, outline, 3, 18)
        center_text(draw, (x, y - 40), title, font(24, True), outline if outline != SLATE else INK)
        for j, line in enumerate(body.split("\n")):
            center_text(draw, (x, y + 10 + j * 26), line, font(18), SLATE)

    arrows = [
        ((350, 280), (430, 280)),
        ((730, 280), (810, 280)),
        ((1110, 280), (1190, 280)),
        ((1340, 370), (1340, 530)),
        ((1190, 620), (1110, 620)),
        ((810, 620), (730, 620)),
        ((580, 530), (580, 370)),
        ((730, 620), (1190, 370)),
    ]
    for (x1, y1), (x2, y2) in arrows:
        draw.line([(x1, y1), (x2, y2)], fill=BLUE, width=3)

    note = font(18)
    center_text(
        draw,
        (W / 2, 920),
        "Evidence is retained in-session. Large dossiers stay paged. Unknowns are first-class, not hidden.",
        note,
        MUTED,
    )
    save(img, "investigation-loop.png")


def diagram_architecture():
    img = Image.new("RGB", (W, 1100), WHITE)
    draw = ImageDraw.Draw(img)
    draw.rectangle((0, 0, W, 1100), fill=WHITE)
    center_text(draw, (W / 2, 48), "Selected architecture layers", font(40, True), INK)
    center_text(
        draw,
        (W / 2, 95),
        "Entry points compose providers; domain stays pure; Evidence contracts stay caller-visible",
        font(20),
        MUTED,
    )

    layers = [
        ("Entry", "CLI (src/cli) · MCP server (src/main) · scripts/rea.mjs dispatcher", BLUE),
        ("Composition", "Typed provider factories · session binding · optional WASM/EVM descriptors", BLUE),
        ("Application", "Shared workflows · BinarySession · InvestigationRecords · JS reconstruction", SLATE),
        ("Adapters / Providers", "Hopper · Ghidra · IDA · Kuna · JADX · browser CDP · Frida · format readers", ORANGE),
        ("Process foundation", "Owned process groups · deadlines · private roots · Windows job objects", MUTED),
        ("Contracts + Domain", "Zod schemas · Evidence · graphs · comparisons (side-effect free domain)", GREEN),
    ]
    y = 160
    for title, body, accent in layers:
        rounded(draw, (80, y, W - 80, y + 110), BLUE_SOFT if accent == BLUE else BG, accent, 3, 16)
        draw.rectangle((80, y, 96, y + 110), fill=accent)
        draw.text((120, y + 22), title, font=font(26, True), fill=INK)
        lines = wrap_text(draw, body, font(20), W - 240)
        for i, line in enumerate(lines):
            draw.text((120, y + 58 + i * 26), line, font=font(20), fill=SLATE)
        y += 130
    save(img, "architecture-layers.png")


def diagram_evidence():
    img = Image.new("RGB", (W, H), WHITE)
    draw = ImageDraw.Draw(img)
    center_text(draw, (W / 2, 50), "Evidence as the unit of truth", font(40, True), INK)
    center_text(
        draw,
        (W / 2, 100),
        "Every useful REA result is an authenticated record, not a disposable chat blob",
        font(22),
        MUTED,
    )

    fields = [
        ("evidence_id", "Stable identity for the observation"),
        ("normalized_result", "Caller-facing facts after adapter normalization"),
        ("raw_result", "Upstream report when distinct, else null"),
        ("analysis_profile", "Concrete profile or null; always present"),
        ("coverage / facets", "What was actually observed vs unavailable"),
        ("limitations", "Honest bounds, unknowns, omitted paths"),
        ("locations / links", "Where in the binary, module, or page"),
        ("authority / confidence", "How strongly the producer stands behind it"),
    ]
    cols = 2
    box_w, box_h = 680, 100
    x0, y0 = 100, 170
    for i, (k, v) in enumerate(fields):
        col, row = i % cols, i // cols
        x = x0 + col * (box_w + 40)
        y = y0 + row * (box_h + 24)
        rounded(draw, (x, y, x + box_w, y + box_h), WHITE, LINE, 2, 14)
        draw.rectangle((x, y, x + 12, y + box_h), fill=BLUE)
        draw.text((x + 36, y + 22), k, font=font(24, True), fill=BLUE)
        draw.text((x + 36, y + 56), v, font=font(20), fill=SLATE)

    center_text(
        draw,
        (W / 2, H - 60),
        "Pass Evidence into compare/match tools · page large dossiers · export bundles for later sessions",
        font(20),
        MUTED,
    )
    save(img, "evidence-model.png")


def diagram_targets():
    img = Image.new("RGB", (W, 1050), WHITE)
    draw = ImageDraw.Draw(img)
    center_text(draw, (W / 2, 48), "What you can analyze", font(40, True), INK)
    center_text(
        draw,
        (W / 2, 95),
        "One agent interface · many local adapters · requirements vary by target",
        font(22),
        MUTED,
    )

    groups = [
        ("Native & metadata", ["Hopper / Ghidra / IDA / Kuna", "PE · ELF · Mach-O", "Go · Rust · GPU · IL2CPP", "objdump · Rizin · GDB"]),
        ("Apps & packages", ["JavaScript / Electron", ".NET assemblies", "PyInstaller / .pyc", "Apple bundles · ASAR"]),
        ("Mobile & firmware", ["Android APK + JADX", "ADB device sessions", "Flutter snapshots", "Binwalk / Unblob"]),
        ("Runtime & network", ["Browser CDP capture", "HAR / SAZ / mitm", "pcap / tshark", "Process PTY capture"]),
        ("Specialized", ["EVM bytecode", "WASM + WABT", "SQLite snapshots", "sigrok logic signals"]),
        ("Game / engine", ["Godot PCK", "Unity metadata", "Electron fuses", "Frida / Cutter"]),
    ]
    box_w, box_h = 460, 260
    gap_x, gap_y = 40, 36
    x0 = (W - (3 * box_w + 2 * gap_x)) / 2
    y0 = 150
    accents = [BLUE, ORANGE, GREEN, SLATE, BLUE, ORANGE]
    for i, ((title, items), accent) in enumerate(zip(groups, accents)):
        col, row = i % 3, i // 3
        x = x0 + col * (box_w + gap_x)
        y = y0 + row * (box_h + gap_y)
        rounded(draw, (x, y, x + box_w, y + box_h), BG, accent, 3, 18)
        draw.text((x + 28, y + 24), title, font=font(26, True), fill=accent)
        for j, item in enumerate(items):
            draw.ellipse((x + 32, y + 78 + j * 42, x + 44, y + 90 + j * 42), fill=accent)
            draw.text((x + 58, y + 70 + j * 42), item, font=font(20), fill=INK)
    save(img, "target-matrix.png")


def diagram_providers():
    img = Image.new("RGB", (W, H), WHITE)
    draw = ImageDraw.Draw(img)
    center_text(draw, (W / 2, 50), "Deep native providers", font(40, True), INK)
    center_text(
        draw,
        (W / 2, 100),
        "Deterministic selection from configured candidates · no silent fallback",
        font(22),
        MUTED,
    )

    providers = [
        ("Hopper", "macOS-friendly\nUnix-socket bridge\noptional setup install", BLUE),
        ("Ghidra", "Headless Java bridge\nCFG · decompiler\nmulti-arch / DOS", SLATE),
        ("IDA", "GUI MCP or headless DB\nupstream ida-pro-mcp\nHex-Rays when licensed", ORANGE),
        ("Kuna", "BYO decompiler\nmetadata discovery\nversion probe on open", GREEN),
    ]
    box_w, box_h = 320, 280
    gap = 40
    total = 4 * box_w + 3 * gap
    x0 = (W - total) / 2
    y = 220
    for i, (name, body, accent) in enumerate(providers):
        x = x0 + i * (box_w + gap)
        rounded(draw, (x, y, x + box_w, y + box_h), WHITE, accent, 4, 20)
        rounded(draw, (x + 24, y + 24, x + box_w - 24, y + 90), accent, accent, 0, 14)
        center_text(draw, (x + box_w / 2, y + 57), name, font(28, True), WHITE)
        for j, line in enumerate(body.split("\n")):
            center_text(draw, (x + box_w / 2, y + 140 + j * 32), line, font(20), SLATE)

    center_text(
        draw,
        (W / 2, H - 90),
        "Static JS and .NET need no native engine · raise REA_*_STARTUP_TIMEOUT_MS for large binaries",
        font(20),
        MUTED,
    )
    save(img, "providers.png")


def diagram_percent_case():
    """Worked Calculator % reconstruction sketch."""
    img = Image.new("RGB", (W, 980), WHITE)
    draw = ImageDraw.Draw(img)
    center_text(draw, (W / 2, 48), "Worked example · Calculator percent", font(38, True), INK)
    center_text(
        draw,
        (W / 2, 95),
        "From installed instructions to two recoverable rules (illustrative of the REA.tools showcase)",
        font(20),
        MUTED,
    )

    # left: assembly clues
    rounded(draw, (80, 160, 760, 520), BG, LINE, 2, 16)
    draw.text((110, 185), "Selected x64 clues", font=font(24, True), fill=BLUE)
    asm = [
        "CMP EAX, 0x5c   ; multiply?",
        "JZ  …            ; same target",
        "CMP EAX, 0x5b   ; divide?",
        "MOV EDX, 0x64   ; literal 100",
        "CALL helpers…   ; copy / compute",
    ]
    for i, line in enumerate(asm):
        draw.text((120, 240 + i * 42), line, font=font(22), fill=INK)

    # right: recovered rules
    rounded(draw, (840, 160, 1520, 520), BLUE_SOFT, BLUE, 3, 16)
    draw.text((870, 185), "Recovered rules", font=font(24, True), fill=BLUE)
    draw.text((870, 250), "After + or −", font=font(22, True), fill=INK)
    draw.text((870, 290), "percent = current × previous / 100", font=font(22), fill=SLATE)
    draw.text((870, 340), "200 + 10%  →  200 + 20  = 220", font=font(22), fill=GREEN)
    draw.text((870, 410), "After × or ÷", font=font(22, True), fill=INK)
    draw.text((870, 450), "percent = current / 100", font=font(22), fill=SLATE)
    draw.text((870, 490), "200 × 10%  →  200 × 0.1  = 20", font=font(22), fill=GREEN)

    # bottom pipeline
    stages = [
        "Open calc.exe\n/ library",
        "Locate %\nhandler",
        "Decode\nbranches",
        "Name helpers\n& constants",
        "Rebuild &\ntest cases",
    ]
    bw = 260
    gap = 28
    total = 5 * bw + 4 * gap
    x0 = (W - total) / 2
    y = 600
    for i, label in enumerate(stages):
        x = x0 + i * (bw + gap)
        rounded(draw, (x, y, x + bw, y + 160), WHITE, BLUE if i < 4 else GREEN, 3, 16)
        center_text(draw, (x + bw / 2, y + 40), f"0{i+1}", font(22, True), BLUE if i < 4 else GREEN)
        for j, line in enumerate(label.split("\n")):
            center_text(draw, (x + bw / 2, y + 90 + j * 28), line, font(20), SLATE)
        if i < 4:
            ax = x + bw + 4
            ay = y + 80
            draw.line([(ax, ay), (ax + gap - 12, ay)], fill=BLUE, width=3)
    save(img, "calculator-percent.png")


def diagram_js_electron():
    img = Image.new("RGB", (W, H), WHITE)
    draw = ImageDraw.Draw(img)
    center_text(draw, (W / 2, 50), "JavaScript / Electron investigation", font(38, True), INK)
    center_text(
        draw,
        (W / 2, 100),
        "Static graph first · then optional runtime observation · always with coverage",
        font(22),
        MUTED,
    )

    boxes = [
        (280, 320, "Artifact", "Directory or .asar\nintegrity + inventory"),
        (640, 320, "Module graph", "Imports · routes\nIPC · native addons"),
        (1000, 320, "Source maps", "Heap-limited codec\npoint evidence"),
        (1360, 320, "Runtime", "CDP / Inspector\nHAR · process PTY"),
        (820, 620, "Agent outcome", "Explain feature\nrebuild + regression checks"),
    ]
    for x, y, title, body in boxes:
        rounded(draw, (x - 160, y - 100, x + 160, y + 100), BLUE_SOFT, BLUE, 3, 18)
        center_text(draw, (x, y - 40), title, font(24, True), BLUE)
        for j, line in enumerate(body.split("\n")):
            center_text(draw, (x, y + 15 + j * 28), line, font(18), SLATE)

    for a, b in [((440, 320), (480, 320)), ((800, 320), (840, 320)), ((1160, 320), (1200, 320))]:
        draw.line([a, b], fill=BLUE, width=3)
    draw.line([(820, 420), (820, 520)], fill=BLUE, width=3)
    draw.line([(280, 420), (280, 520), (660, 520)], fill=BLUE, width=3)
    draw.line([(1360, 420), (1360, 520), (980, 520)], fill=BLUE, width=3)

    center_text(
        draw,
        (W / 2, H - 70),
        "npx -y rea-agents@latest analyze-javascript-application /abs/path --json",
        font(20),
        MUTED,
    )
    save(img, "javascript-electron.png")


def diagram_setup():
    img = Image.new("RGB", (W, 720), WHITE)
    draw = ImageDraw.Draw(img)
    center_text(draw, (W / 2, 48), "Setup in three moves", font(40, True), INK)
    steps = [
        ("Install Node", "Node 22.x / 24.x / 26+\nand npm on PATH"),
        ("Run setup", "npx rea-agents setup\napprove agent changes"),
        ("Restart agent", "MCP + skill loaded\nask about one feature"),
    ]
    box_w, box_h = 380, 240
    gap = 50
    total = 3 * box_w + 2 * gap
    x0 = (W - total) / 2
    y = 160
    for i, (title, body) in enumerate(steps):
        x = x0 + i * (box_w + gap)
        rounded(draw, (x, y, x + box_w, y + box_h), WHITE, BLUE, 4, 20)
        center_text(draw, (x + box_w / 2, y + 50), f"{i+1}", font(36, True), BLUE)
        center_text(draw, (x + box_w / 2, y + 110), title, font(26, True), INK)
        for j, line in enumerate(body.split("\n")):
            center_text(draw, (x + box_w / 2, y + 160 + j * 28), line, font(20), SLATE)
        if i < 2:
            ax = x + box_w + 10
            ay = y + box_h / 2
            draw.line([(ax, ay), (ax + gap - 20, ay)], fill=BLUE, width=4)
            draw.polygon(
                [(ax + gap - 20, ay - 9), (ax + gap - 8, ay), (ax + gap - 20, ay + 9)],
                fill=BLUE,
            )
    center_text(
        draw,
        (W / 2, 560),
        "Supported agents include Claude Code, Codex, Cursor, Gemini CLI, Grok Build, and more",
        font(20),
        MUTED,
    )
    center_text(
        draw,
        (W / 2, 610),
        "Official sources: github.com/morluto/rea · rea.tools · npm package rea-agents",
        font(20),
        MUTED,
    )
    save(img, "setup-flow.png")


def main():
    diagram_hero()
    diagram_investigation_flow()
    diagram_architecture()
    diagram_evidence()
    diagram_targets()
    diagram_providers()
    diagram_percent_case()
    diagram_js_electron()
    diagram_setup()
    print("done")


if __name__ == "__main__":
    main()
