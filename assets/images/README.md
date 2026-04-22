# Theme Assets - Images

## 📁 Purpose

Store **structural images** that are part of the theme/site design and should ship via Git to all environments.

---

## ✅ Use This Folder For:

- Hero section background images
- About page team photos
- Service/feature icons
- Logo and branding assets
- Default placeholder images
- Any image that's part of site structure (not user content)

---

## ❌ Don't Use This Folder For:

- Blog post images (use Media Library)
- User-uploaded content
- Gallery photos that change frequently
- Client-specific images that differ per environment

---

## 📂 Recommended Structure

```
assets/images/
├── hero/
│   ├── home-hero.jpg
│   ├── about-hero.jpg
│   └── services-hero.jpg
├── team/
│   ├── john-doe.jpg
│   └── jane-smith.jpg
├── icons/
│   ├── security.svg
│   ├── training.svg
│   └── support.svg
├── placeholders/
│   ├── featured-image.jpg
│   └── avatar.jpg
└── branding/
    ├── logo.png
    └── logo-white.png
```

---

## 🔄 Workflow

### **Adding Images:**

1. Place image in appropriate subfolder:
   ```bash
   assets/images/hero/about-hero.jpg
   ```

2. In WordPress page editor:
   - Click "Set featured image"
   - Upload/select your image
   - WordPress will detect it's from theme assets

3. Export the page:
   - **Tools → Page Content Sync**
   - Select page → Export

4. Commit to Git:
   ```bash
   git add assets/images/hero/about-hero.jpg
   git add template-parts/page-patterns/about.php
   git commit -m "Add about page with hero image"
   git push
   ```

5. On staging/production:
   ```bash
   git pull
   ```
   - Import pages (Tools → Page Content Sync → Import)
   - Image ships with theme ✅

---

## 🎯 How It Works

### **Export:**
```php
// Detects image is in theme assets
'featured_image_path' => 'assets/images/hero/about-hero.jpg'  // ← Relative path
```

### **Import:**
```php
// Finds image in theme folder (shipped via Git)
$file_path = get_theme_file_path( 'assets/images/hero/about-hero.jpg' );

// Creates media library attachment pointing to it
$attachment_id = custom_theme_get_or_create_theme_image_attachment( $file_path );

// Sets as featured image
set_post_thumbnail( $page_id, $attachment_id );
```

---

## 📏 Image Guidelines

### **Recommended Sizes:**

| Use Case | Recommended Size | Format |
|----------|------------------|--------|
| **Hero backgrounds** | 1920x1080px | JPG (optimized) |
| **Featured images** | 1200x630px | JPG |
| **Team photos** | 400x400px | JPG |
| **Icons** | 64x64px or vector | SVG or PNG |
| **Logo** | Vector or 2x size | PNG or SVG |

### **Optimization:**

- Compress JPG images (70-85% quality)
- Use WebP format when possible
- Use SVG for logos/icons
- Keep file sizes under 200KB

---

## ⚠️ Important Notes

1. **Once committed to Git, images are permanent**
   - Don't commit huge files
   - Optimize before adding

2. **Images ship to all environments**
   - Same image on local, staging, production
   - Can't have different images per environment

3. **Media Library still exists**
   - Clients can upload content images separately
   - Those won't sync via Git (stored in wp-content/uploads)

---

## 🔍 Troubleshooting

### **Image not showing after import:**

1. Check file exists:
   ```bash
   ls assets/images/hero/about-hero.jpg
   ```

2. Check file permissions:
   ```bash
   chmod 644 assets/images/hero/about-hero.jpg
   ```

3. Check exported path in pattern file:
   ```php
   // template-parts/page-patterns/about.php
   'featured_image_path' => 'assets/images/hero/about-hero.jpg'
   ```

### **Image creates duplicate attachment:**

The system checks for existing attachments by file path. If you see duplicates:

1. Delete extra attachments in Media Library
2. Re-run import

---

## 📚 Reference

- See [DEVELOPMENT-STRATEGY.md](../../DEVELOPMENT-STRATEGY.md) for full workflow
- See [DEPLOYMENT-WORKFLOW.md](../../DEPLOYMENT-WORKFLOW.md) for deployment process
