"""Generate PWA icons programmatically."""
from PIL import Image, ImageDraw, ImageFont
import os

def make_icon(size, path):
    img = Image.new('RGBA', (size, size), (0, 0, 0, 0))
    d = ImageDraw.Draw(img)
    # Background circle
    d.ellipse([0, 0, size, size], fill='#0ea5e9')
    # Letter A
    margin = size // 5
    font_size = size // 2
    try:
        font = ImageFont.truetype("arial.ttf", font_size)
    except:
        font = ImageFont.load_default()
    bbox = d.textbbox((0, 0), 'A', font=font)
    w, h = bbox[2] - bbox[0], bbox[3] - bbox[1]
    d.text(((size - w) / 2, (size - h) / 2 - size // 12), 'A', fill='white', font=font)
    img.save(path, 'PNG')
    print(f"Generated {path}")

os.chdir(os.path.dirname(__file__))
make_icon(192, 'icon-192.png')
make_icon(512, 'icon-512.png')
