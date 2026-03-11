# Docs Index

This folder is the onboarding and operations hub for the USNsoft platform. Start here if you are new to the repository, setting up a machine, debugging local problems, or trying to understand how the public website and internal/admin UI fit together in one Laravel codebase.

## Recommended Reading Order

1. [setup/local-development.md](setup/local-development.md)  
   Start here to get the stack running with Docker.
2. [setup/first-run-checklist.md](setup/first-run-checklist.md)  
   Use this as a quick machine bootstrap checklist after the full setup guide.
3. [access/sample-logins.md](access/sample-logins.md)  
   Use the seeded local accounts to verify the app and role boundaries.
4. [architecture/project-structure-overview.md](architecture/project-structure-overview.md)  
   Learn where the important code lives.
5. [ui/design-system-overview.md](ui/design-system-overview.md)  
   Understand the shared theme and component rules before editing UI.
6. [runbooks/debugging-guide.md](runbooks/debugging-guide.md)  
   Use this when something fails locally.

## Setup

- [setup/local-development.md](setup/local-development.md)  
  Full Docker-first local setup guide for developers.
- [setup/docker-commands.md](setup/docker-commands.md)  
  Quick command reference for daily Docker workflows.
- [setup/environment-configuration.md](setup/environment-configuration.md)  
  `.env` rules, required variables, and secrets discipline.
- [setup/first-run-checklist.md](setup/first-run-checklist.md)  
  Ordered setup checklist for a fresh machine.

## Access

- [access/sample-logins.md](access/sample-logins.md)  
  Development-only seeded accounts, passwords, and reseed steps.
- [access/roles-and-access-overview.md](access/roles-and-access-overview.md)  
  High-level role intent and privileged boundaries.

## Runbooks

- [runbooks/emergency-commands.md](runbooks/emergency-commands.md)  
  Safe versus destructive local recovery commands.
- [runbooks/debugging-guide.md](runbooks/debugging-guide.md)  
  Common local failures, likely causes, and recovery steps.
- [runbooks/developer-workflow-and-maintenance-notes.md](runbooks/developer-workflow-and-maintenance-notes.md)  
  Day-to-day engineering workflow and maintenance expectations.
- [runbooks/queue-and-scheduler.md](runbooks/queue-and-scheduler.md)  
  Queue and scheduler behavior for notifications and scheduled CMS publishing.
- [runbooks/database-reset-and-seeding.md](runbooks/database-reset-and-seeding.md)  
  Migration/reset strategy and seed recovery.

## UI

- [ui/ui-audit-and-theme-summary.md](ui/ui-audit-and-theme-summary.md)  
  Concise audit of what was inconsistent and what was standardized.
- [ui/design-system-overview.md](ui/design-system-overview.md)  
  Shared UI tokens, components, accessibility, and responsive rules.

## Architecture

- [architecture/project-structure-overview.md](architecture/project-structure-overview.md)  
  Guide to the important folders and modules.
- [architecture/public-vs-admin-ui-notes.md](architecture/public-vs-admin-ui-notes.md)  
  How the public and admin/custom internal UI stay consistent without becoming identical.

## Legacy Reference Docs

These older stage-oriented documents still exist and may help with deeper background:

- [architecture.md](architecture.md)
- [auth-authorization-foundation.md](auth-authorization-foundation.md)
- [cms-admin-ux.md](cms-admin-ux.md)
- [cms-block-schemas.md](cms-block-schemas.md)
- [cms-core.md](cms-core.md)
- [cms-workflow.md](cms-workflow.md)
- [deployment-overview.md](deployment-overview.md)
- [domain-model.md](domain-model.md)
- [modules.md](modules.md)
- [run-local.md](run-local.md)
- [security-baseline.md](security-baseline.md)
- [workflow.md](workflow.md)
