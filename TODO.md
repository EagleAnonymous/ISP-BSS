# TODO: Connect Chatbot to Groq AI

## Steps
- [ ] 1. Add `groq` config block to `config/services.php` (reads `GROQ_API_KEY`, `GROQ_MODEL`)
- [ ] 2. Create `app/Services/GroqService.php` to call Groq's `/chat/completions` API
- [ ] 3. Add `chat()` method to `app/Http/Controllers/Subscriber/DashboardController.php`
- [ ] 4. Add `POST /subscriber/chatbot/chat` route to `routes/web.php`
- [ ] 5. Update `resources/views/subscriber/chatbot.blade.php` to send messages to Groq AI
- [ ] 6. Append `GROQ_API_KEY` and `GROQ_MODEL` to `.env` and `.env.example`
- [ ] 7. Run `php artisan config:clear`
- [ ] 8. Test the integration

