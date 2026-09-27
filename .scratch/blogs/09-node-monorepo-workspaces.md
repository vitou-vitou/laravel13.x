# Node Monorepo Workspaces: Keep Package Boundaries Clean, Stop Hoisting the Universe

Your team decides to move three frontend apps and a shared UI library into a single monorepo. Within three months, developers are installing random packages at the repository root "so everyone has access," apps import deep internal files like `../../packages/ui/src/components/Button.tsx`, and CI builds fail randomly because one package silently depends on a library installed by a neighbor.

Workspaces in modern package managers like pnpm, npm, and yarn solve code sharing across related projects. But a monorepo is a collection of distinct packages, not one giant unstructured folder. If you maintain strict package boundaries, define explicit package exports, and stop hoisting application dependencies to the root, your monorepo will remain fast, modular, and reliable.

## Define Clear Boundaries with Workspace Protocols

Whether you use pnpm workspaces, Turborepo, or Nx, the foundation is a clean workspace configuration that explicitly defines which directories contain standalone packages.

Here is a standard layout for a pnpm monorepo:

pnpm-workspace.yaml:
```yaml
packages:
  - 'apps/*'
  - 'packages/*'
```

In your shared UI package, define a clean name and declare its public contract:

packages/ui/package.json:
```json
{
  "name": "@acme/ui",
  "version": "0.1.0",
  "private": true,
  "type": "module",
  "exports": {
    ".": "./src/index.ts",
    "./styles.css": "./src/styles.css"
  },
  "dependencies": {
    "clsx": "^2.1.0",
    "tailwind-merge": "^2.2.0"
  },
  "devDependencies": {
    "typescript": "^5.4.0"
  }
}
```

Notice the `exports` field. It defines the only public entry points that external applications are allowed to import. If an engineer tries to reach into an internal helper under `src/utils/private-helpers.ts`, Node and modern bundlers will immediately throw an error.

## Import Packages, Not Relative File Paths

In an undisciplined monorepo, developers write deep relative import paths to borrow code:

```ts
// Anti-pattern: do not use relative paths across package boundaries
import { Button } from '../../../packages/ui/src/components/Button';
```

This bypasses the package's build process, couples your app to the file structure of a sibling project, and breaks if you ever publish the shared package to a registry or move it into a Docker image.

Declare the internal dependency inside the consuming application's `package.json` using the workspace protocol:

apps/web/package.json:
```json
{
  "name": "@acme/web",
  "version": "1.0.0",
  "private": true,
  "dependencies": {
    "@acme/ui": "workspace:*",
    "react": "^18.3.0",
    "react-dom": "^18.3.0"
  }
}
```

Now, import cleanly using the package name:

apps/web/src/App.tsx:
```tsx
import { Button } from '@acme/ui';
import '@acme/ui/styles.css';

export default function App() {
    return (
        <main className="p-8">
            <h1 className="text-xl font-bold mb-4">Customer Portal</h1>
            <Button variant="primary" onClick={() => alert('Clicked')}>
                Continue
            </Button>
        </main>
    );
}
```

The bundler resolves `@acme/ui` as a standard package, respecting its exports map and declared dependencies.

## What Belongs at the Root package.json?

The root `package.json` of a monorepo should hold almost nothing. It exists to configure workspace-wide tooling, not to host dependencies for your apps:

- **Belongs at root:** The package manager version (`packageManager`), root task orchestrators (Turborepo, Nx), formatting tools (Prettier), and repo-wide linting configs.
- **Does NOT belong at root:** React, Vue, Lodash, Tailwind, Axios, or database drivers.

When you install runtime dependencies at the root, you create "ghost dependencies." An app might import `date-fns` without declaring it in its own `package.json`. It runs fine on your local machine because the package is in the root `node_modules`, but when Docker builds that app in isolation for production, the build crashes with a missing module error.

Always install dependencies into the specific package that uses them:

```bash
# Correct: install directly into the target package
pnpm --filter @acme/web add date-fns
```

## Run Commands Targeted by Workspace Filter

Avoid giant root scripts that try to run every lint and test sequentially in a single process. Use workspace filtering to run tasks where the code lives.

```bash
# Run tests only for the UI package
pnpm --filter @acme/ui test

# Run build for the web app and all its dependencies
pnpm --filter @acme/web... build
```

The trailing `...` in pnpm tells the package manager to build all internal dependencies (`@acme/ui`) before building the target app (`@acme/web`).

## What Can Go Wrong

The most painful issue in TypeScript monorepos is divergent compiler configurations causing type mismatch errors across packages.

To prevent drift, create a shared base configuration in `packages/tsconfig`:

packages/tsconfig/base.json:
```json
{
  "$schema": "https://json.schemastore.org/tsconfig",
  "compilerOptions": {
    "strict": true,
    "esModuleInterop": true,
    "skipLibCheck": true,
    "moduleResolution": "bundler"
  }
}
```

Then extend it inside each package:

apps/web/tsconfig.json:
```json
{
  "extends": "@acme/tsconfig/base.json",
  "compilerOptions": {
    "jsx": "react-jsx"
  },
  "include": ["src"]
}
```

Every project in your workspace inherits identical strictness and module resolution rules, preventing compiler surprises on CI.

## Summary

A monorepo should accelerate development, not create an untangled ball of shared code. Isolate your packages with explicit directory roots. Declare all dependencies directly in each package's `package.json`, export only what is public via the `exports` field, and keep the root configuration clean.

When package boundaries are respected, adding new applications, refactoring shared libraries, and debugging build issues becomes straightforward and predictable.

## Further Reading

- [pnpm Workspaces Documentation](https://pnpm.io/workspaces)
- [Node.js Package Exports Documentation](https://nodejs.org/api/packages.html#exports)
- [Turborepo Monorepo Handbook](https://turbo.build/repo/docs/handbook)
- [TypeScript Project References](https://www.typescriptlang.org/docs/handbook/project-references.html)

What tooling do you use to manage monorepo boundaries in your team? Let us know in the comments below.
