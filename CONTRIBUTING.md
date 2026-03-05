# Contributing

## Commit Messages

- First line: concise summary in **imperative mood**, max 72 characters.
- Blank line, then body (if needed): explain the "why", not the "what". Wrap at 72 characters.
- No attribution to individual tools or services.

## Pull Requests

Every PR must include three sections:

### Summary

1-3 bullet points explaining what the PR does and why.

### Changes

List specific files or areas changed, grouped logically.

### Test Plan

Checklist of steps to verify the changes work correctly.

**Guidelines:**

- Keep it concise — reviewers should understand the PR in under 30 seconds.
- Reference related issue numbers when available.

## Versioning

Before tagging a release, update the `Version` header in `style.css` to match the release tag (without the `v` prefix):

```
Version: 1.2.0
```

The tag name (`v1.2.0`) and the header value (`1.2.0`) must match — the Plugin Update Checker uses this header to detect available updates.

## Release Notes

When tagging a release (`vX.X.X`), group changes under:

- **Features**
- **Bug Fixes**
- **Performance**
- **Breaking Changes** (if any)
- **Full Changelog**

Base the notes on commit messages since the last tag. Keep entries concise and professional. No attribution to individual tools or services - notes represent the team.
