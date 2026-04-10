![Eagle](https://raw.githubusercontent.com/liamseys/eagle/main/.github/banner.jpg)

# Eagle 🦅

A self-hosted support ticket management system built with Laravel.

## Features

- **Clients**: Manage client profiles and view their ticket history in one place.
- **Tickets**: Create, track, and manage support tickets efficiently.
  - **Email to Ticket**: Automatically convert incoming emails into support tickets.
  - **Escalations**: Require approval before processing certain tickets.
- **Help Center**: Write articles, organize them into categories, and allow users to submit forms which create tickets.
- **Workflows**: Define and manage Service Level Agreement (SLA) policies for each ticket priority.
- **Groups**: Create groups for various teams and departments to streamline ticket assignment and forwarding.
- **Users**: Manage permissions for secure access control.

## Installation

1. Clone the repository:

```bash
git clone https://github.com/liamseys/eagle.git
cd eagle
```

2. Install dependencies:

```bash
composer install
```

3. Set up your environment:

```bash
cp .env.example .env
php artisan key:generate
```

4. Configure your database in `.env` and run migrations:

```bash
php artisan migrate
```

5. Build the assets:

```bash
npm install
npm run build
```

6. Set up a cron job to run the scheduler every minute:

```bash
php artisan schedule:run
```

7. Start the queue worker:

```bash
php artisan queue:work
```

## Laravel Reverb

Eagle uses [Laravel Reverb](https://reverb.laravel.com) for real-time chatbot message streaming. Start the Reverb server alongside your application:

```bash
php artisan reverb:start
```

Make sure to configure your Reverb credentials in `.env`:

```env
REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

## AI Chatbot Widget

Eagle includes an embeddable AI-powered chatbot widget that can be added to any website. It uses your Help Center articles to answer questions and can create support tickets when it can't resolve an issue. Add the following snippet before the closing `</body>` tag:

```html
<script src="https://your-eagle-domain.com/chatbot/widget.js"></script>
```

To pre-fill the user's name and email (e.g. for authenticated users), define `eagleSettings` before the script tag:

```html
<script>
    window.eagleSettings = {
        name: 'John Doe',
        email: 'john@example.com',
    };
</script>
<script src="https://your-eagle-domain.com/chatbot/widget.js"></script>
```

## License

Eagle is released under the Creative Commons Attribution-NonCommercial 4.0 International license. See the [LICENSE](LICENSE) file for more details. A human-friendly summary is available at [creativecommons.org](https://creativecommons.org/licenses/by-nc/4.0/).

Dependencies may be subject to their own licenses.

## Security

If you discover any security-related issues, please email [liam.seys@gmail.com](mailto:liam.seys@gmail.com) instead of using the issue tracker. All security vulnerabilities will be promptly addressed.
