#!/usr/bin/env python3
"""
Compress uploaded images automatically.
Usage: python compress_image.py <image_path> [max_width] [max_height] [quality]
Called by Laravel after each image upload.
"""

import sys
import os
from PIL import Image

MAX_WIDTH = 1280
MAX_HEIGHT = 1280
QUALITY = 82


def compress(path, max_w=MAX_WIDTH, max_h=MAX_HEIGHT, quality=QUALITY):
    if not os.path.exists(path):
        print(f"ERROR: file not found: {path}", file=sys.stderr)
        sys.exit(1)

    img = Image.open(path)

    # Convert RGBA/P to RGB for JPEG compatibility
    if img.mode in ("RGBA", "P"):
        img = img.convert("RGB")

    original_size = os.path.getsize(path)

    # Resize if too large
    img.thumbnail((max_w, max_h), Image.LANCZOS)

    # Save with compression
    ext = os.path.splitext(path)[1].lower()
    if ext in (".jpg", ".jpeg"):
        img.save(path, "JPEG", quality=quality, optimize=True)
    elif ext == ".png":
        img.save(path, "PNG", optimize=True)
    elif ext == ".gif":
        img.save(path, "GIF")
    elif ext == ".webp":
        img.save(path, "WEBP", quality=quality, optimize=True)
    else:
        img.save(path, quality=quality, optimize=True)

    new_size = os.path.getsize(path)
    reduction = round((1 - new_size / original_size) * 100, 1) if original_size > 0 else 0
    print(f"OK: {os.path.basename(path)} | {original_size//1024}KB → {new_size//1024}KB (-{reduction}%) | {img.size[0]}x{img.size[1]}px")


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python compress_image.py <path> [max_w] [max_h] [quality]")
        sys.exit(1)

    path    = sys.argv[1]
    max_w   = int(sys.argv[2]) if len(sys.argv) > 2 else MAX_WIDTH
    max_h   = int(sys.argv[3]) if len(sys.argv) > 3 else MAX_HEIGHT
    quality = int(sys.argv[4]) if len(sys.argv) > 4 else QUALITY

    compress(path, max_w, max_h, quality)
