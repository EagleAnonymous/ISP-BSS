# TODO — My Assigned Tickets Page

## Steps
- [x] 1. Add `staff.assignments` route in `routes/web.php`
- [x] 2. Add `assignments()` method to `Staff\TicketController`
- [x] 3. Create `resources/views/staff/assignments.blade.php` (card grid UI)
- [x] 4. Update sidebar "Assignments" link in `resources/views/layouts/staff.blade.php`
- [x] 5. Verify with `php artisan route:list`

# TODO — AI Chatbot & Ticket Visibility Fixes

## Steps
- [x] 1. Improve AI system prompt in `DashboardController::chat()` for accurate responses
- [x] 2. Fix chatbot frontend (`chatbot.blade.php`) to route all messages to the AI (remove canned interception)
- [x] 3. Preserve ticket escalation flow with category detection on explicit "ticket"/"escalate" intent
- [x] 4. Add `database` notification channel to `NewTicketNotification` so admin/staff see it in-app
- [x] 5. Create `notifications` table migration and run `php artisan migrate`
- [x] 6. Update `AppServiceProvider` to show unread notification count in staff & admin layouts
