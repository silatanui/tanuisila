import os
import glob

f1 = "C:/xampp/htdocs/tanuisila/IMG_20260625_115331.jpg"
f2 = "C:/xampp/htdocs/tanuisila/public/profile.jpg"
f3 = "C:/xampp/htdocs/tanuisila/profile.jpg"

lines = []
lines.append("Checking files:\n")
lines.append(f"f1 raw: {os.path.exists(f1)}\n")
if os.path.exists(f1):
    lines.append(f"f1 size: {os.path.getsize(f1)}\n")

lines.append(f"f2 raw: {os.path.exists(f2)}\n")
if os.path.exists(f2):
    lines.append(f"f2 size: {os.path.getsize(f2)}\n")

lines.append(f"f3 raw: {os.path.exists(f3)}\n")
if os.path.exists(f3):
    lines.append(f"f3 size: {os.path.getsize(f3)}\n")

images = glob.glob("C:/xampp/htdocs/tanuisila/*.jpg") + glob.glob("C:/xampp/htdocs/tanuisila/*.png")
lines.append("Found images in root:\n")
for img in images:
    lines.append(f"  {img} ({os.path.getsize(img)} bytes)\n")

with open("C:/xampp/htdocs/tanuisila/result.txt", "w") as f:
    f.writelines(lines)
