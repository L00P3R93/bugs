# Bug Bounty Platform - Complete Project Description

## Project Overview

A comprehensive **Bug Bounty & Testing Management Platform** built on Laravel 12, designed to connect software testers with organizations seeking quality assurance. The platform facilitates bug reporting, validation, scoring, and automated payouts through integrated wallet systems.

---

## Technology Stack

| Component | Package | Version | Purpose |
|-----------|---------|---------|---------|
| **PHP** | php | 8.4.18 | Core language runtime |
| **Framework** | laravel/framework | 12 | Application foundation |
| **Admin Panel** | filament/filament | 5 | Backend management interface |
| **Authentication** | laravel/fortify | 1 | Authentication scaffolding |
| **UI Components** | livewire/flux | 2 | Free UI component library |
| **Frontend** | livewire/livewire | 4 | Reactive frontend framework |
| **CLI Prompts** | laravel/prompts | 0 | Interactive command-line interfaces |
| **MCP Protocol** | laravel/mcp | 0 | Model Context Protocol integration |
| **Code Style** | laravel/pint | 1 | Automatic code formatting |
| **Development** | laravel/sail | 1 | Docker development environment |
| **Testing** | pestphp/pest | 4 | Modern PHP testing framework |
| **Unit Testing** | phpunit/phpunit | 12 | Standard PHP testing |
| **Styling** | tailwindcss | 4 | Utility-first CSS framework |

---

## Core Features

### 1. Multi-Role User System

| Role | Description | Capabilities |
|------|-------------|------------|
| **Super Admin** | Platform owner | Full system access, configuration, user management |
| **Admin** | Organization managers | Program management, bug validation, financial oversight |
| **Tester** | Bug hunters | Submit bugs, track earnings, manage wallet |

### 2. Bug Reporting & Management

**Submission Flow:**
- Testers create detailed bug reports with rich media attachments (images/videos)
- Automated categorization and labeling system
- Environment capture (OS, browser, device specifications)
- Reproduction steps with validation requirements

**Bug Status Workflow:**

## Bug Status Workflow

| From Status | To Status | Actor | Conditions | Database Updates | Description |
|-------------|-----------|-------|------------|------------------|-------------|
| **SUBMITTED** | TRIAGED | Moderator | Initial review complete | `triaged_by`, `triaged_at`, `category_id`, `severity` | New bug report enters the system. Moderator performs initial intake, assigns appropriate category (Security, UI/UX, etc.) and preliminary severity level (P0-P3). Auto-labels applied based on keywords. |
| SUBMITTED | REJECTED | Moderator | Spam, out of scope, invalid | `rejected_by`, `rejected_at`, `rejection_reason` | Immediate rejection for reports that are spam, completely out of program scope, or lack minimum required information. Reporter receives rejection notification with reason. |
| **TRIAGED** | UNDER_REVIEW | System/Mod | Auto-promote or manual | `reviewed_by`, `reviewed_at` | Qualified bug moves to technical review queue. Technical team assigned based on category expertise. Bug becomes locked for editing by reporter. |
| TRIAGED | DUPLICATE | Moderator | Matches existing bug | `duplicate_of_id`, `status` | Bug matches existing report (fingerprint match or manual identification). Linked to original report. Reporter redirected to original. No payout for duplicates. |
| **UNDER_REVIEW** | VALIDATED | Technical Reviewer | Bug confirmed, reproducible | `validated_by`, `validated_at`, `final_amount`, `is_paid=false` | Technical team successfully reproduces bug, confirms it's unintended behavior. Quality criteria assessed, novelty factors applied, final payout calculated. Funds deposited to pending_balance (7-day hold). |
| UNDER_REVIEW | REJECTED | Technical Reviewer | Not a bug, intended behavior | `rejected_by`, `rejected_at`, `rejection_reason` | Bug is confirmed as intended functionality, or cannot be reproduced. Detailed feedback provided to reporter. 14-day appeal window opens. |
| UNDER_REVIEW | WONT_FIX | Admin | Out of program scope | `status` only | Bug is valid but falls outside program scope (e.g., third-party issues, deprecated features, accepted risks). No payout issued. Reporter notified with explanation. |
| **VALIDATED** | PAID | System (cron) | 7-day hold expired | `is_paid=true`, `paid_at`, balance updated | Automated daily process releases held funds after 7-day dispute window. Funds moved from pending_balance to available_balance. Reporter can now withdraw. |
| VALIDATED | → appeal → | Moderator | Appeal approved | Return to UNDER_REVIEW | Reporter disputes validation outcome (e.g., severity undervalued). Moderator reviews appeal, returns bug to technical review for reassessment if justified. |
| **PAID** | FIXED | Developer | Bug resolved in code | `status` only | Development team deploys fix to production. Bug marked as resolved. Reporter notified of fix. Counts toward reporter's reputation score. |
| **FIXED** | CLOSED | System | 30 days post-fix | Archived | 30-day stabilization period complete. Bug archived for analytics. All associated data retained but read-only. Reporter can no longer comment. |
| REJECTED | → appeal → | Moderator | Appeal approved | Return to UNDER_REVIEW | Reporter disputes rejection (e.g., new evidence provided). Moderator reviews new information. If valid, bug re-enters technical review queue. |

**Bug Lifecycle States:**

SUBMITTED → TRIAGED → UNDER_REVIEW → VALIDATED → PAID → FIXED → CLOSED
↓           ↓            ↓            ↓
REJECTED   DUPLICATE    WONT_FIX    (appeal process)

### 3. Advanced Scoring Engine

**Formula:**
$$
PAYOUT = BASE × SEVERITY × QUALITY × NOVELTY
$$

| Component | Source | Range |
|-----------|--------|-------|
| **Base** | Category-defined | $10 - $5,000 |
| **Severity** | P0-P3 multiplier | 0.5x - 3.0x |
| **Quality** | Submission criteria | 1.0x - 2.0x |
| **Novelty** | Uniqueness factors | 1.0x - 1.5x |

**Categories:**
- Critical Security (Auth bypass, RCE, data leaks)
- Security (XSS, CSRF, IDOR)
- Crash/Data Loss
- Performance
- Functional
- UI/UX
- Compatibility
- Accessibility

### 4. Wallet & Financial System

| Feature | Implementation |
|---------|---------------|
| **Balances** | Available vs. Pending (7-day hold) |
| **Deposits** | Automatic on bug validation |
| **Withdrawals** | Multiple methods (PayPal, Bank, Crypto, Gift Cards) |
| **Transaction Log** | Complete audit trail with bug linkage |
| **Fraud Protection** | Rate limiting, duplicate detection, reputation gating |

### 5. Validation Workflow

**Multi-Stage Review Process:**
1. **Triage** - Moderator categorization and severity estimation
2. **Technical Review** - Reproduction and quality assessment
3. **Consensus** - Multi-reviewer voting for high-value bugs
4. **Final Scoring** - Automated calculation with quality/novelty bonuses
5. **Payment Hold** - 7-day dispute window before funds release

### 6. Anti-Gaming Protections

| Risk | Mitigation |
|------|------------|
| Duplicate submissions | Content fingerprinting (hash of steps + screenshots) |
| Severity inflation | Multi-reviewer consensus required for P0/P1 |
| Fake reports | Video evidence required for payouts >$100 |
| Collusion | Random reviewer assignment with blind scoring |
| Automated spam | CAPTCHA, rate limiting, trust level gates |

---

## Database Architecture

### Core Entities

| Table | Purpose | Key Relationships |
|-------|---------|-------------------|
| `users` | Platform accounts | 1:1 wallet, 1:M bugs submitted |
| `categories` | Bug classification | Defines base payout ranges |
| `severity_multipliers` | Impact levels | P0-P3 multipliers |
| `labels` | Organization tags | M:M with bugs |
| `bugs` | Core reports | Belongs to user, category; has many attachments, reviews |
| `bug_attachments` | Media evidence | Images, videos, documents |
| `bug_quality_scores` | Scoring criteria | Tracks which quality bonuses apply |
| `novelty_factors` | Uniqueness bonuses | First report, edge case detection |
| `wallets` | Financial accounts | 1:1 with users, tracks balances |
| `transactions` | Payment records | Links to bugs for bounty traceability |
| `reviews` | Moderation decisions | Multi-reviewer assessment records |
| `review_votes` | Consensus system | Agree/disagree/abstain voting |
| `comments` | Collaboration | Threaded discussion on bugs |
| `notifications` | User alerts | Email/push delivery tracking |
| `audit_logs` | Compliance | Immutable change history |
| `fraud_flags` | Security | Automated suspicion detection |

---

## Admin Interface (Filament v5)

### Management Panels

| Resource | Functionality |
|----------|---------------|
| **User Management** | CRUD operations, role assignment, reputation adjustment |
| **Bug Moderation** | Triage queue, validation workflow, dispute resolution |
| **Category Configuration** | Base amounts, weights, active/inactive toggles |
| **Financial Oversight** | Transaction monitoring, withdrawal approval, fraud review |
| **Reporting & Analytics** | Tester leaderboards, program metrics, payout statistics |
| **System Configuration** | Quality criteria, novelty factors, hold periods |

---

## Frontend Architecture (Livewire v4 + Flux UI v2)

### Public Pages

| Route | Purpose | Components |
|-------|---------|------------|
| `/` | Landing/Marketing | Hero, feature highlights, program listings |
| `/bugs` | Public bug feed (anonymized) | Filterable list, statistics |
| `/leaderboard` | Top testers | Rankings, earnings display |

### Authenticated Tester Dashboard

| Section | Features |
|---------|----------|
| **Submit Bug** | Multi-step form with drag-drop attachments |
| **My Bugs** | Submission history, status tracking, earnings preview |
| **Wallet** | Balance overview, transaction history, withdrawal requests |
| **Profile** | Reputation score, statistics, API keys (future) |

### Real-Time Features (Livewire)

- Notification bell with unread counts
- Bug status updates without page refresh
- Wallet balance updates post-validation
- Comment threads with instant submission

---
