# DayChallenge Postman Collection

This folder contains Postman artifacts for testing all DayChallenge API phases.

## Files

| File | Description |
|------|-------------|
| `DayChallenge_API_Complete.postman_collection.json` | Merged collection — all 85+ endpoints across 13 phases |
| `DayChallenge_Local.postman_environment.json` | Merged environment with all variables |
| `*.postman_collection.json` (other) | Individual phase collections (kept for reference) |
| `*.postman_environment.json` (other) | Per-phase environment files (kept for reference) |

## Import

1. Open Postman.
2. Import `DayChallenge_API_Complete.postman_collection.json`.
3. Import `DayChallenge_Local.postman_environment.json`.
4. Select the `DayChallenge Local` environment.
5. Update `base_url` if your API host/port differs.

## Variables

| Variable | Type | Description |
|----------|------|-------------|
| `base_url` | default | API base URL (default: `http://localhost:8000`) |
| `token` | secret | Bearer token (auto-populated by Register/Login scripts) |
| `user_id` | default | Authenticated user ID |
| `user_email` | default | User email |
| `category_id` | default | Selected category UUID |
| `task_id` | default | Selected task UUID |
| `challenge_id` | default | Selected challenge UUID |
| `google_access_token` | secret | Google OAuth token |
| `password_reset_token` | secret | Token from reset email |
| `verify_url` | default | Full signed verification URL |

## Folder Structure

```
DayChallenge API - Complete
├── Phase 1 — Auth & User Management    (13 requests)
├── Phase 2 — Categories & Tasks        (10 requests)
├── Phase 3 — Challenges                (8 requests)
├── Phase 4 — Daily Progress            (4 requests)
├── Phase 5 — Streaks                   (2 requests)
├── Phase 6 — Coin System               (3 requests)
├── Phase 7 — Friends                   (7 requests)
├── Phase 9 — Promise System            (3 requests)
├── Phase 10 — Freeze System            (3 requests)
├── Phase 11 — Achievement System       (3 requests)
├── Phase 12 — Dashboard & Statistics   (3 requests)
├── Phase 13.1 — Income Management      (6 requests)
└── Phase 13.2-13.6 — FFGR Core Finance (20 requests)
```
