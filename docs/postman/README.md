# DayChallenge Phase 1 Postman Kit

This folder contains Postman artifacts for testing and documenting the Phase 1 authentication/user-management APIs.

## Files

- `DayChallenge-Phase1-Auth.postman_collection.json`
- `DayChallenge-Phase1-Auth.postman_environment.json`

## Import Steps

1. Open Postman.
2. Import both JSON files from this folder.
3. Select the `DayChallenge Local` environment.
4. Update `base_url` if your API host/port differs.

## Suggested Test Flow

1. Register
2. Verify Email (paste signed URL into `verify_url`)
3. Login
4. Get Profile
5. Update Profile
6. Change Password
7. Forgot Password
8. Reset Password
9. Logout
10. Delete Account

## Variable Notes

- `auth_token` is auto-populated by Register, Login, and Google Login test scripts.
- `verify_url` must be the full signed verification URL from email.
- `password_reset_token` must come from the reset-password email.
