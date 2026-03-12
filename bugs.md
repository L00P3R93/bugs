# Bug Bounty Platform - Complete Database Schema

## Overview
This schema supports a comprehensive bug bounty platform where testers submit bugs, moderators validate them, and payments are processed through user wallets with sophisticated scoring algorithms.

---

## Core Entities

### 1. Users
| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `email` | VARCHAR | Unique, verified |
| `username` | VARCHAR | Unique display name |
| `password_hash` | VARCHAR | Encrypted password |
| `full_name` | VARCHAR | Real name (optional) |
| `avatar_url` | VARCHAR | Profile image |
| `bio` | TEXT | User description |
| `location` | VARCHAR | Geographic location |
| `timezone` | VARCHAR | Default: UTC |
| `reputation_score` | INTEGER | Community standing |
| `trust_level` | INTEGER | 1-5, affects limits |
| `total_earned` | DECIMAL | Lifetime earnings |
| `total_withdrawn` | DECIMAL | Total payouts |
| `status` | ENUM | active, suspended, banned, pending_verification |
| `email_verified_at` | TIMESTAMP | Verification date |
| `identity_verified` | BOOLEAN | KYC status |
| `two_factor_enabled` | BOOLEAN | 2FA status |
| `created_at` | TIMESTAMP | Account creation |

---

### 2. Categories (Bug Classification)
| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `slug` | VARCHAR | URL-friendly name |
| `name` | VARCHAR | Display name |
| `description` | TEXT | Full description |
| `base_min_amount` | DECIMAL | Minimum payout |
| `base_max_amount` | DECIMAL | Maximum payout |
| `weight_multiplier` | DECIMAL | Category importance |
| `color` | VARCHAR | UI color code |
| `icon` | VARCHAR | UI icon |
| `is_active` | BOOLEAN | Available for use |
| `sort_order` | INTEGER | Display priority |

**Example Categories:**

| Category | Base Range | Weight |
|----------|------------|--------|
| Critical Security | $500-$5,000 | 5.0x |
| Security | $100-$1,000 | 3.0x |
| Crash/Data Loss | $75-$500 | 2.5x |
| Performance | $50-$300 | 2.0x |
| Functional | $25-$200 | 1.5x |
| UI/UX | $10-$75 | 1.0x |
| Compatibility | $15-$100 | 1.2x |
| Accessibility | $20-$150 | 1.3x |

---

### 3. Severity Multipliers
| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `level` | ENUM | p0_critical, p1_high, p2_medium, p3_low |
| `name` | VARCHAR | Display name |
| `multiplier` | DECIMAL | Payout multiplier |
| `description` | TEXT | Impact definition |

**Severity Levels:**

| Level | Multiplier | Definition | Example |
|-------|------------|------------|---------|
| P0 - Critical | 3.0x | System down, security breach | Database exposed |
| P1 - High | 2.0x | Major feature broken | Payment fails |
| P2 - Medium | 1.0x | Partially broken, workaround exists | Button broken on mobile |
| P3 - Low | 0.5x | Minor inconvenience | Misaligned icon |

---

### 4. Labels (Organization Tags)
| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `name` | VARCHAR | Display name |
| `slug` | VARCHAR | URL-friendly |
| `type` | VARCHAR | technical, platform, process, special |
| `color` | VARCHAR | UI color |
| `description` | TEXT | Purpose |
| `usage_count` | INTEGER | Popularity metric |
| `is_active` | BOOLEAN | Available for use |

**Label Types:**

| Type | Examples |
|------|----------|
| Technical | frontend, backend, api, database, mobile |
| Platform | ios, android, chrome, firefox |
| Process | needs-info, under-review, validated, duplicate |
| Special | good-first-bug, bounty-eligible, urgent |

---

### 5. Bugs (Core Entity)
| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `bug_number` | VARCHAR | Human-readable ID (BUG-2026-0001) |
| `reporter_id` | UUID | FK → users |
| `category_id` | UUID | FK → categories |
| `severity` | ENUM | p0_critical, p1_high, p2_medium, p3_low |
| `title` | VARCHAR | Brief summary |
| `description` | TEXT | Full description |
| `environment` | TEXT | OS, browser, device |
| `reproduction_steps` | TEXT | How to reproduce |
| `expected_result` | TEXT | What should happen |
| `actual_result` | TEXT | What actually happens |
| `status` | ENUM | submitted, triaged, under_review, validated, rejected, duplicate, wont_fix, fixed, closed |
| `triaged_by` | UUID | FK → users (moderator) |
| `triaged_at` | TIMESTAMP | Triage date |
| `reviewed_by` | UUID | FK → users (technical reviewer) |
| `reviewed_at` | TIMESTAMP | Review date |
| `validated_by` | UUID | FK → users (validator) |
| `validated_at` | TIMESTAMP | Validation date |
| `rejected_by` | UUID | FK → users (rejector) |
| `rejected_at` | TIMESTAMP | Rejection date |
| `rejection_reason` | TEXT | Why rejected |
| `duplicate_of_id` | UUID | FK → bugs (original report) |
| `base_amount` | DECIMAL | Calculated base |
| `severity_multiplier` | DECIMAL | Applied severity |
| `quality_bonus_multiplier` | DECIMAL | Quality bonus |
| `novelty_bonus_multiplier` | DECIMAL | Novelty bonus |
| `final_amount` | DECIMAL | Total payout |
| `is_paid` | BOOLEAN | Payment status |
| `paid_at` | TIMESTAMP | Payment date |
| `view_count` | INTEGER | Popularity metric |
| `comment_count` | INTEGER | Engagement metric |
| `created_at` | TIMESTAMP | Submission date |
| `updated_at` | TIMESTAMP | Last modification |

---

### 6. Bug Labels (Many-to-Many)
| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `bug_id` | UUID | FK → bugs |
| `label_id` | UUID | FK → labels |
| `added_by` | UUID | FK → users |
| `added_at` | TIMESTAMP | When added |

---

### 7. Bug Attachments
| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `bug_id` | UUID | FK → bugs |
| `file_name` | VARCHAR | Original filename |
| `file_type` | VARCHAR | image, video, document |
| `mime_type` | VARCHAR | Technical MIME type |
| `file_size_bytes` | BIGINT | File size |
| `storage_path` | VARCHAR | Server location |
| `public_url` | VARCHAR | CDN/access URL |
| `width` | INTEGER | For images/videos |
| `height` | INTEGER | For images/videos |
| `duration_seconds` | INTEGER | For videos |
| `uploaded_by` | UUID | FK → users |
| `uploaded_at` | TIMESTAMP | Upload date |

---

### 8. Quality Criteria (Scoring System)
| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `name` | VARCHAR | Criterion name |
| `slug` | VARCHAR | URL-friendly |
| `description` | TEXT | What qualifies |
| `bonus_multiplier` | DECIMAL | % bonus (e.g., 0.20 = 20%) |
| `is_active` | BOOLEAN | Available for use |

**Quality Criteria:**

| Criteria | Bonus | Description |
|----------|-------|-------------|
| Clear reproduction steps | +20% | Step-by-step instructions |
| Video evidence attached | +15% | Screen recording included |
| Root cause analysis | +25% | Technical diagnosis provided |
| Suggested fix included | +15% | Proposed solution |
| Affects multiple environments | +10% | Cross-platform impact |

**Max Quality Bonus: 2.0x (if all criteria met)**

---

### 9. Bug Quality Scores
| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `bug_id` | UUID | FK → bugs |
| `criteria_id` | UUID | FK → quality_criteria |
| `is_met` | BOOLEAN | Criterion satisfied |
| `assessed_by` | UUID | FK → users (reviewer) |
| `assessed_at` | TIMESTAMP | Assessment date |
| `notes` | TEXT | Reviewer comments |

---

### 10. Novelty Factors
| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `bug_id` | UUID | FK → bugs |
| `factor_type` | VARCHAR | first_report, new_feature, edge_case |
| `description` | TEXT | Explanation |
| `bonus_multiplier` | DECIMAL | Applied bonus |
| `assessed_by` | UUID | FK → users |
| `assessed_at` | TIMESTAMP | Assessment date |

**Novelty Bonuses:**

| Factor | Bonus | Description |
|--------|-------|-------------|
| First report of this bug type | 1.5x | Never seen before |
| Bug in new/untested feature | 1.3x | Fresh functionality |
| Edge case never seen before | 1.2x | Rare scenario |

---

### 11. Wallets
| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `user_id` | UUID | FK → users (unique) |
| `available_balance` | DECIMAL | Ready to withdraw |
| `pending_balance` | DECIMAL | 7-day hold period |
| `total_earned` | DECIMAL | Lifetime earnings |
| `daily_withdrawal_limit` | DECIMAL | Max per day |
| `monthly_withdrawal_limit` | DECIMAL | Max per month |
| `is_locked` | BOOLEAN | Security freeze |
| `locked_reason` | TEXT | Why frozen |
| `locked_at` | TIMESTAMP | Freeze date |
| `created_at` | TIMESTAMP | Wallet creation |
| `updated_at` | TIMESTAMP | Last update |

---

### 12. Transactions
| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `transaction_number` | VARCHAR | Human-readable ID (TXN-2026-000001) |
| `wallet_id` | UUID | FK → wallets |
| `user_id` | UUID | FK → users |
| `type` | ENUM | bounty_earned, bonus, withdrawal, refund, adjustment, referral |
| `status` | ENUM | pending, completed, failed, cancelled |
| `amount` | DECIMAL | Gross amount |
| `fee_amount` | DECIMAL | Platform/processing fees |
| `net_amount` | DECIMAL | Final amount |
| `currency` | VARCHAR | USD, EUR, etc. |
| `exchange_rate` | DECIMAL | If multi-currency |
| `bug_id` | UUID | FK → bugs (if bounty) |
| `reference_id` | VARCHAR | External payment ID |
| `payout_method` | ENUM | paypal, bank_transfer, crypto_btc, crypto_eth, gift_card |
| `payout_details` | JSON | Encrypted account info |
| `processed_at` | TIMESTAMP | Processing start |
| `completed_at` | TIMESTAMP | Finalized date |
| `cancelled_at` | TIMESTAMP | Cancellation date |
| `description` | TEXT | Human-readable description |
| `ip_address` | VARCHAR | Request origin |
| `user_agent` | TEXT | Device info |
| `created_at` | TIMESTAMP | Transaction creation |

---

### 13. Transaction Logs (Audit Trail)
| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `transaction_id` | UUID | FK → transactions |
| `action` | VARCHAR | created, processing, completed, failed |
| `previous_status` | ENUM | Old status |
| `new_status` | ENUM | New status |
| `details` | JSON | Additional data |
| `performed_by` | UUID | FK → users (null = system) |
| `created_at` | TIMESTAMP | Log entry date |

---

### 14. Reviews (Moderation)
| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `bug_id` | UUID | FK → bugs |
| `reviewer_id` | UUID | FK → users |
| `verdict` | VARCHAR | valid, invalid, duplicate, needs_info |
| `severity_assessment` | ENUM | Reviewer's severity rating |
| `category_assessment` | UUID | FK → categories |
| `feedback` | TEXT | Public feedback |
| `internal_notes` | TEXT | Private notes |
| `base_amount_suggested` | DECIMAL | Proposed base |
| `quality_multiplier_suggested` | DECIMAL | Proposed quality bonus |
| `final_amount_suggested` | DECIMAL | Proposed total |
| `created_at` | TIMESTAMP | Review date |

---

### 15. Review Votes (Consensus)
| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `bug_id` | UUID | FK → bugs |
| `review_id` | UUID | FK → reviews |
| `voter_id` | UUID | FK → users |
| `vote` | VARCHAR | agree, disagree, abstain |
| `created_at` | TIMESTAMP | Vote date |

---

### 16. Comments (Collaboration)
| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `bug_id` | UUID | FK → bugs |
| `author_id` | UUID | FK → users |
| `content` | TEXT | Comment body |
| `is_internal` | BOOLEAN | Staff-only visibility |
| `parent_id` | UUID | FK → comments (threading) |
| `depth` | INTEGER | Nesting level |
| `is_edited` | BOOLEAN | Modified flag |
| `edited_at` | TIMESTAMP | Edit date |
| `created_at` | TIMESTAMP | Post date |

---

### 17. Notifications
| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `user_id` | UUID | FK → users |
| `type` | VARCHAR | bug_validated, payment_received, etc. |
| `title` | VARCHAR | Notification headline |
| `message` | TEXT | Full message |
| `action_url` | VARCHAR | Deep link |
| `bug_id` | UUID | FK → bugs (if related) |
| `transaction_id` | UUID | FK → transactions (if related) |
| `is_read` | BOOLEAN | Read status |
| `read_at` | TIMESTAMP | Read date |
| `email_sent` | BOOLEAN | Email delivered |
| `push_sent` | BOOLEAN | Push notification sent |
| `created_at` | TIMESTAMP | Notification date |

---

### 18. Audit Logs (Compliance)
| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `user_id` | UUID | FK → users (actor) |
| `user_email` | VARCHAR | Actor email (snapshot) |
| `ip_address` | VARCHAR | Request origin |
| `action` | VARCHAR | bug_created, payment_processed, etc. |
| `entity_type` | VARCHAR | bug, transaction, user |
| `entity_id` | UUID | Affected record |
| `previous_values` | JSON | Before state |
| `new_values` | JSON | After state |
| `metadata` | JSON | Additional context |
| `created_at` | TIMESTAMP | Event date |

---

### 19. Fraud Flags (Security)
| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `user_id` | UUID | FK → users (suspected) |
| `bug_id` | UUID | FK → bugs (if related) |
| `flag_type` | VARCHAR | duplicate_pattern, suspicious_ip, rate_limit |
| `confidence_score` | DECIMAL | 0.0 - 1.0 likelihood |
| `detected_by` | VARCHAR | system, manual, ml_model |
| `status` | VARCHAR | open, investigating, cleared, confirmed |
| `resolved_by` | UUID | FK → users (investigator) |
| `resolution_notes` | TEXT | Investigation outcome |
| `created_at` | TIMESTAMP | Flag date |
| `resolved_at` | TIMESTAMP | Resolution date |

---

### 20. Config Settings (Platform Configuration)
| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `key` | VARCHAR | Setting name (unique) |
| `value` | TEXT | Setting value |
| `data_type` | VARCHAR | string, number, boolean, json |
| `description` | TEXT | Purpose |
| `is_editable` | BOOLEAN | Can modify |
| `updated_at` | TIMESTAMP | Last change |
| `updated_by` | UUID | FK → users |

---

## Relationships
**Relationship Legend:**
- `users 1:M bugs` — One user submits many bugs
- `bugs M:1 categories` — Many bugs belong to one category
- `bugs 1:M bug_labels` — One bug has many labels (junction table)
- `bugs 1:M attachments` — One bug has many files
- `bugs 1:M reviews` — One bug has many reviewer assessments
- `users 1:1 wallets` — One user has one wallet
- `wallets 1:M transactions` — One wallet has many transactions
- `transactions 1:M logs` — One transaction has many status logs
- `bugs 1:M comments` — One bug has many comments (threaded)
- `bugs 1:M quality_scores` — One bug scored against many criteria
- `bugs 1:M novelty_factors` — One bug may have multiple novelty bonuses

## Scoring Formula Implementation

### Base Formula
$$
FINAL PAYOUT = BASE × SEVERITY × QUALITY × NOVELTY
$$



### Component Breakdown

| Component | Source Table | Calculation Method |
|-----------|--------------|-------------------|
| **BASE** | `categories` | Random or fixed value between `base_min_amount` and `base_max_amount` |
| **SEVERITY** | `severity_multipliers` | Lookup by `severity` field on bug |
| **QUALITY** | `bug_quality_scores` + `quality_criteria` | `1.0 + SUM(met_criteria.bonus_multiplier)` |
| **NOVELTY** | `novelty_factors` | `PRODUCT(applicable_factors.bonus_multiplier)` |

### Detailed Calculation Steps
Step 1: Determine BASE

```sql
    SELECT base_min_amount, base_max_amount
    FROM categories WHERE id = [bug.category_id];
    BASE = RANDOM(base_min_amount, base_max_amount)
    OR BASE = (base_min_amount + base_max_amount) / 2  // fixed option
```
---

Step 2: Apply SEVERITY Multiplier

```sql
SELECT multiplier FROM severity_multipliers
WHERE level = [bug.severity];
SEVERITY = multiplier  // 3.0, 2.0, 1.0, or 0.5

```
---

Step 3: Calculate QUALITY Bonus
```sql
SELECT qc.bonus_multiplier
FROM bug_quality_scores bqs
JOIN quality_criteria qc ON qc.id = bqs.criteria_id
WHERE bqs.bug_id = [bug.id] AND bqs.is_met = true;
QUALITY = 1.0 + SUM(bonus_multiplier)  // Max 2.0x (all criteria met)

```
---

Step 4: Calculate NOVELTY Bonus

```sql
SELECT bonus_multiplier
FROM novelty_factors
WHERE bug_id = [bug.id];
NOVELTY = PRODUCT(bonus_multiplier)  // Compound multipliers
```
---

Step 5: Compute FINAL

$$ FINAL = BASE × SEVERITY × QUALITY × NOVELTY $$

Store in: bugs.final_amount

---

### Real-World Example

**Bug Report:** SQL Injection in Search (Security Category, P0 Critical)

| Step | Component | Value | Running Total |
|------|-----------|-------|---------------|
| 1 | Base (Security: $100-$1,000) | $500 | $500 |
| 2 | Severity (P0 Critical × 3.0) | × 3.0 | $1,500 |
| 3 | Quality (3 criteria met: 20% + 15% + 25% = 60%) | × 1.6 | $2,400 |
| 4 | Novelty (First report × 1.5) | × 1.5 | **$3,600** |
| | | | |
| **FINAL** | | | **$3,600** |

---

## Anti-Gaming Protections

| Risk | Mitigation Strategy | Database Implementation |
|------|---------------------|------------------------|
| Duplicate farming | Fingerprinting (hash of steps + screenshots) | `bug_attachments` file hashing |
| Severity inflation | Multi-reviewer consensus | `review_votes` table |
| Fake bugs | Require video for >$100 payouts | `bug_attachments` validation |
| Collusion | Random reviewer assignment | `reviews.reviewer_id` randomization |
| Automated spam | Rate limiting, CAPTCHA, reputation gates | `users.trust_level` gating |

---

## Indexing Strategy

| Table | Indexed Fields | Purpose |
|-------|---------------|---------|
| users | email, username, status | Fast lookup, login |
| bugs | bug_number, reporter_id, category_id, status, severity, created_at, is_paid | Search, filtering, reporting |
| transactions | transaction_number, wallet_id, user_id, bug_id, type, status, created_at | Financial queries, audit |
| comments | bug_id, author_id, parent_id | Thread loading |
| notifications | user_id, is_read, created_at | Inbox queries |

---

## Data Integrity Rules

| Constraint | Implementation |
|------------|---------------|
| No orphan bugs | `reporter_id` → users (restrict delete) |
| No orphan attachments | `bug_id` → bugs (cascade delete) |
| No negative balances | CHECK constraint on wallet amounts |
| Unique bug numbers | Unique index on `bug_number` |
| Unique usernames/emails | Unique constraints on users |
| Valid status transitions | Application-level state machine |

---

## Security Considerations

1. **Encrypted Fields**: `payout_details` (JSON) encrypted at rest
2. **Audit Trail**: All changes logged to `audit_logs`
3. **Soft Deletes**: Not implemented; use status fields
4. **Rate Limiting**: Tracked via `fraud_flags`
5. **Identity Verification**: `users.identity_verified` boolean
6. **Two-Factor**: `users.two_factor_enabled` flag

---

## Scaling Considerations

| Strategy | Implementation |
|----------|---------------|
| Partitioning | `transactions` table by date if high volume |
| Archiving | Move old `audit_logs` to cold storage |
| CDN | `bug_attachments.public_url` points to CDN |
| Caching | Denormalized `comment_count`, `view_count` on bugs |
| Read Replicas | For `audit_logs` and `notifications` queries |

---

## Sample Queries

### Calculate User's Total Earnings
```sql
SELECT 
    u.username,
    w.total_earned,
    w.available_balance,
    w.pending_balance,
    COUNT(b.id) as bugs_submitted,
    SUM(CASE WHEN b.status = 'validated' THEN 1 ELSE 0 END) as bugs_validated
FROM users u
JOIN wallets w ON w.user_id = u.id
LEFT JOIN bugs b ON b.reporter_id = u.id
WHERE u.id = ?
GROUP BY u.id, w.id;
