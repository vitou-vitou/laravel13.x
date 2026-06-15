# Impeccable craft outputs

Standalone UI prototypes. Not wired into Laravel routes unless you choose to integrate them.

## business-category

Inspired by [business-cambodia.com/categories/business](https://business-cambodia.com/categories/business).

| File | Purpose |
|------|---------|
| `business-category/index.html` | Category listing page |
| `business-category/styles.css` | OKLCH tokens + layout |
| `business-category/main.js` | Mobile nav + load-more feedback |

### Preview locally

```bash
# From repo root (Python)
python -m http.server 8765 --directory .impeccable/craft/business-category

# Then open http://localhost:8765/
```

Or open `index.html` directly in a browser (fonts and images need network).

### Design notes

- **Register:** brand / editorial news
- **Theme:** light, morning commute reading
- **Fonts:** Noto Sans Khmer + Barlow (Bunny Fonts)
- **Accent:** committed red OKLCH (~BC energy), distinct from monorepo welcome tokens
