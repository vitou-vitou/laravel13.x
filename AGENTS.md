# Workspace chat workflow

For any non-trivial code change in this workspace (feature, bugfix, refactor, or configuration change), use the Cursor manual at `.cursor/skills/spec-kit-openspec-superpowers/SKILL.md` before taking implementation action.

- Load its session combo and `laravel13-x-policy.md` first.
- Use Spec-Kit + Superpowers for greenfield MVP work; use OpenSpec + Superpowers for post-MVP changes. Never combine Spec-Kit and OpenSpec for one feature.
- Follow the manual's G1-G4 gates, including explicit user approval of a spec before implementation.
- Do not apply this workflow to ordinary conversation, read-only questions, or requests that do not change code.

The Cursor manual remains the source of truth; this file only makes its workflow discoverable to chat agents working from the repository root.
