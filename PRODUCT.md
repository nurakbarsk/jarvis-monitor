# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack

Node.js, Express, SQLite3, Telegraf. Frontend: static HTML/CSS/JS served from `public/`. Specified by the project brief (not delegated).

## Users

- Foundation / campus staff who report bugs on two campus-foundation projects.
- Internal operators who watch GitHub push activity and Telegram alerts (Jarvis).

## Product Purpose

Jarvis Monitor is a local project-monitoring and ticketing console. It records GitHub push changelogs, accepts campus bug tickets, and sends Telegram alerts so operators see commits and incidents without opening GitHub.

Success: a staff member can switch between Proyek Yayasan A and Proyek Yayasan B, read recent commits, file a ticket, and see open tickets; each push and new ticket is stored and (when configured) notified on Telegram.

## Positioning

GitHub webhook + campus ticketing + instant Telegram in one SQLite-backed console — not a general LMS, not a full GitHub clone.

## Operating Context

Local development. Config via `.env` (`PORT`, `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`). Database file `./monitor.db` in development, `/data/monitor.db` when `NODE_ENV=production` (override with `DB_PATH`). GitHub delivers JSON or form-encoded payloads to `POST /api/webhook/github`.

## Capabilities and Constraints

- Projects: Proyek Yayasan A, Proyek Yayasan B.
- Commit types: `feat:` → fitur, `fix:` → bugfix, else update.
- Tickets default to status Open.
- Data API returns 20 latest changelogs, open tickets, and 7-day commit activity for the selected project.
- Telegram is optional: missing token/chat id must not break HTTP APIs.

## Brand Commitments

Visual system is the Eduplex dashboard in `design.md` (dark sidebar, lime accent, pastel category tints, large radii, flat cards). Product name: Jarvis. Voice: concise, operational, Indonesian UI copy for campus staff.

## Evidence on Hand

No live GitHub or Telegram credentials in repo. Seeded demo changelogs/tickets are illustrative until a real webhook or form submit arrives.

## Product Principles

- One project in view at a time; switching is the primary context change.
- Alerts are short and structured (repo/type/message, or ticket id/project/reporter/description).
- Status is always labeled in text, never color-only.
- Missing integrations fail soft; persistence still succeeds.

## Accessibility & Inclusion

WCAG AA contrast on lime and pastel surfaces; icon-only controls have accessible names; keyboard focus visible.
