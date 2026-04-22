# Block Development Structure

## Directory Layout

```
blocks/                          # Source files (you edit these)
  └── header-navigation/
      ├── block.json              # Block metadata
      ├── index.js                # Block registration
      ├── edit.js                 # Editor component (React)
      ├── save.js                 # Frontend component (React)
      └── style.css               # Styles

build/blocks/                    # Compiled files (auto-generated)
  └── header-navigation/
      └── index.js                # Compiled JavaScript
```

## How It Works

1. **You create** blocks in `blocks/` folder
2. **Webpack compiles** them from JSX to JavaScript  
3. **Compiled files** go to `build/blocks/`
4. **WordPress loads** the compiled files from `build/`

## Commands

```bash
# Development with hot reload
npm run watch

# Production build
npm run build

# Watch only blocks (faster)
npm run watch:blocks
```

## Creating a New Block

1. Create directory: `blocks/my-block/`
2. Add files: `block.json`, `index.js`, `edit.js`, `save.js`,style.css`
3. Run `npm run watch`
4. Block automatically appears in WordPress!

**Note**: The `block.json` file points to `../../build/blocks/my-block/index.js` for the compiled JavaScript.
# Native Gutenberg Blocks

This directory contains native WordPress Gutenberg blocks built with block.json and React.

## Block Structure

Each block should be in its own subdirectory with the following structure:

```
blocks/
  └── block-name/
      ├── block.json          # Block metadata and attributes
      ├── index.js            # Block registration
      ├── edit.js             # Editor component (React)
      ├── save.js             # Frontend output (or null for dynamic)
      ├── render.php          # (Optional) Server-side rendering for dynamic blocks
      ├── style.css           # Frontend styles
      └── editor.css          # (Optional) Editor-only styles
```

## Creating a New Block

1. Create a new directory: `blocks/your-block-name/`
2. Create `block.json` with metadata and attributes
3. Create React components (edit.js, save.js)
4. Register in `index.js`
5. Add styles with Tailwind CSS in `style.css`

Blocks are automatically registered by `block-registry.php` when they contain a `block.json` file.

## Build Process

Run the following commands to work with blocks:

```bash
# Start development server with hot reload
npm run start

# Build for production
npm run build
```

## Resources

- [WordPress Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [block.json Reference](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/)
- [@wordpress/create-block](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-create-block/)
