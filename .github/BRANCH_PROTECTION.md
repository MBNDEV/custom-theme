# Branch protection: block merges when lint fails

The Lint workflow runs on **every push and every pull request** (all branches). To prevent merging into any branch when `composer run lint:run` fails:

1. In GitHub: **Settings** → **Branches** → **Add branch protection rule** (or edit an existing rule).
2. Set **Branch name pattern** to the branch(es) to protect:
   - `master` – protect only `master`
   - `*` or `**` – protect all branches (pattern matching)
   - `main` – if you use `main` instead of `master`
3. Enable **Require status checks to pass before merging**.
4. In the search box, select the **Lint** status check (the workflow that runs `composer run lint:run`).
5. Save.

Then:
- **Pull requests** targeting that branch cannot be merged until the Lint workflow passes.
- **Direct pushes** still complete (GitHub runs checks after the push). To avoid bad code on protected branches, restrict push access and use pull requests for changes.

Result: merges are only allowed when lint (and security scan) pass.
