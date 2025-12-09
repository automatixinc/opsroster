# Multi-tenant SaaS boilerplate v2

Detailed docs coming soon!

## Installation

- `cp .env.example .env`
- `composer install`
- `npm install`
- `npm run build`
- `php artisan key:generate`
- `touch database/database.sqlite`
- `php artisan migrate`
- If you're using a RLS-based setup, `php artisan tenants:rls`

Confirm everything works by running `php artisan test`.

To serve the app, just run `php artisan serve`. Make sure to visit on `localhost:8000` — not 127.0.0.1.

> Helpful note: `localhost` supports subdomains which works great for local development with `artisan serve`, however some browsers (looking at you Safari) don't seem to like localhost subdomains, so if you run into issues it's recommended to use Chrome or a proper setup like Laravel Herd.

You can create a central admin user using `php artisan app:create-admin`.

## Enabling additional Jetstream features

You can enable additional Jetstream features in the `config/jetstream.php` file.

## Postgres RLS

In setups using RLS, don't forget to run `php artisan tenants:rls` after adding or updating migrations to generate the RLS policies for the new or updated tables.

### Jetstream migrations

Some Jetstream tables don't have real foreign key constraints (`user_id` column on `teams` table, `current_team_id` column on `users` table). The RLS policies don't get properly created for all tenant tables.

Because of that, we give these columns comment constraints (e.g. `$table->foreignId('current_team_id')->nullable()->comment('rls teams.id')`). The comment constraints allow the RLS policies to get generated correctly while preserving the default behavior.

## Upgrading

Unlike version 1, this boilerplate is not a single GitHub repository.

Instead, it's a generated codebase. Future versions may have _different changes in the same commits_ as past versions.

This is because there are simply too many possible setups for this boilerplate to be a single repository. Instead,
we maintain a project that generates these boilerplates.

For this reason, downloads are available as .zip files. This makes updating a little more complicated, though still
manageable.

For comparison: In version 1, you'd typically keep a git remote for the boilerplate repo in addition to your own project. That way
you could merge in changes from the boilerplate repo, or rebase on them. Conflicts were still possible, but you could
directly pull in changes from the boilerplate remote.

In version 2, there is no git remote you can use, however the process is ultimately not too different.

Here are some ways you can handle updates, based your on git workflow preferences:

> Please note these are *suggestions* rather than copypasteable instructions.
> Always handle updates with care.

### Applying a diff

(In the following examples I use simple diffs, but you can use patches if you prefer.)

Assuming the following directory structure:
```
- parent/
    - your_current_project/
    - new_version/
```

(`new_version` being an unzipped, newer version of the same boilerplate setup.)

```bash
# Check out the boilerplate tag in your project (this is just the last commit *before* your first commit)
(cd your_current_project && git checkout boilerplate)

# Remove the git history from the new version
rm -rf new_version/.git

# Copy the git history from your project into the new version
cp -R your_current_project/.git new_version/.git

# Now we're comparing the latest points in both versions of the boilerplate
# by using the new_version *code* but the your_current_project *history*

# Create a diff of the changes
(cd new_version && git diff) > changes.diff

cd your_current_project

# Checkout the development branch of your project
git checkout master

# Apply the changes
git apply changes.diff
```

It's recommended to preview and potentially clean up the diff before applying it.
You may encounter conflicts if the boilerplate code has been significantly changed
in your project.

### Rebasing

This approach is similar to the previous one, but instead of applying a diff *as a
new commit*, we rebase your changes on top of the new boilerplate code.

```bash
# Check out the boilerplate tag in your project
(cd your_current_project && git checkout boilerplate)

# Remove the git history from the new version
rm -rf new_version/.git

# Copy the git history from your project into the new version
cp -R your_current_project/.git new_version/.git

# Now we're comparing the latest points in both versions of the boilerplate
# by using the new_version *code* but the your_current_project *history*

# Create a diff of the changes
(cd new_version && git diff) > changes.diff

cd your_current_project

# Difference from the previous approach: we remain on the boilerplate tag
# Instead, we create a new branch based on that tag
git checkout -b boilerplate-update

# Apply the changes
git apply changes.diff

# Rebase master on the boilerplate-update branch
git checkout master
git rebase boilerplate-update
```

One more note: You don't need to use the `rm -rf .git` approach, there are other
ways to do these diffs but this is the simplest one, even if a bit hacky.

## Ziggy's route() helper with Tenancy

When using Vue with the Ziggy route helper, you may want the helper to automatically prefix the passed route names with 'tenant.' when in tenant context to simplify package integrations.

Let's say you want to have all Fortify routes to be tenant app-specific, so you give the Fortify routes a 'tenant.' name prefix. This will break all Jetstream Vue components with calls like `route('register')` or `route('login')`. You can either manually prefix these calls in each view (which could be a pain), or you can override the route() helper so that route('register') calls will behave as if the call was route('tenant.register').

To achieve that, you'll need to:
- override window.route in your app's layout file -- this override affects route() calls in the <script> tags of your components
- use a custom ZiggyVue instance in your app.js where you override the route helper -- this override afects route() calls in the <template> tags of your components

So essentially, you'll be overriding two distinct route() helpers.

Your layout file (e.g. resources/views/app.blade.php):
```diff
<!-- Scripts -->
@routes
+ <script>
+ window.tenancyInitialized = {{ tenant() ? 1 : 0 }}
+
+ if (window.tenancyInitialized) {
+    const oldRoute = window.route
+    window.route = function (name, params, absolute, config) {
+        if (name && ! name.startsWith('tenant.')) {
+             name = 'tenant.' + name
+        }
+
+        return oldRoute(name, params, absolute, config)
+    }
+ }
+ </script>
@vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
```

Now, you can use window.tenancyInitialized to check the current context in JS. If the context is tenant (window.tenancyInitialized is true), override window.route to automatically prefix names passed to route() calls in the <script> tags of your Vue components with 'tenant.' (unless already prefixed).

You'll want to do the same for the route() calls in the <template> tags of your Vue components. For that, you'll need to use a custom ZiggyVue instance in resources/js/app.js instead of the imported one, and if window.tenancyInitialized is true, override the route() helper provided by the ZiggyVue plugin:
```diff
import { createApp, h } from 'vue';
- import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
...

+ const ZiggyVue = {
+    install(app, options) {
+        const r = (name, params, absolute, config = options) => {
+            if (window.tenancyInitialized && (name && ! name.startsWith('tenant.'))) {
+                name = 'tenant.' + name
+            }
+
+            return route(name, params, absolute, config);
+        };
+
+        if (parseInt(app.version) > 2) {
+            app.config.globalProperties.route = r;
+            app.provide('route', r);
+        } else {
+            app.mixin({
+                methods: {
+                    route: r,
+                },
+            });
+        }
+    }
+ };
+
createInertiaApp({
```

## Domain management

Domain management allows you to
- create new domains
- delete existing domains
- request or revoke Ploi certificates for the domains
- make domains primary

The validation and additional logic (like redirects) is handled in the domain controllers (Admin\DomainController and Tenant\DomainController) in Vue setups, and in Livewire setups, it's handled in the Livewire components.

Domain management logic is reusable. It's extracted to DomainManager + the domain controllers/components. The domain management works the same in the tenant app, and the admin panel (in Jetstream, tenant edit page, in starter kit setups, the "Domains" subpage on the tenant edit page).

To customize domain management in Livewire setups, you can just update the tenant domain management components, since the admin panel components extend them. Feel free to customize the compomnents as you like.

## Billing management

Billing management **in the tenant app** allows the tenant to
- see its subscription status (the subscription banner)
- see its invoices (upcoming/paid, paid invoices can be downloaded as PDF files)
- update its billing address
- update its payment method
- manage its subscription plan (create, update, cancel, resume)

**In the admin panel**, admins can
- see the tenant's subscription status
- update the tenant's billing address
- adjust the tenant's credit balance

The subscription status and billing address parts are the same in both contexts. The components and logic are reused, following the same patterns as described in the domain management section.

## Filament admin panel

The Filament admin panel is not only available in the central app, but in the tenant apps too -- as long as you're logged in as the tenant's owner user (= the admin).

> Note: Even in Filament setups, our custom auth page is used. You can still use Filament's auth page (see the "Using Filament's login view" section)

To access the Filament admin panel, navigate to the /admin/login page by clicking the "Admin login" button in the central landing page's navigation. There, you'll need to enter your admin user's credentials. You can either use our custom `app:create-admin` command (preferred), or Filament's `filament:make-user` (not preferred because of inaccurate terminal output on success, but effectively, they're the same, more on that below).

The admin panel's /admin/redirect/tenants route can be a bit confusing because of the redirect. It ultimately serves for `route('admin.tenants.index')` call reusability across the setups (as always, feel free to customize that). Filament also has its own /admin/logout route, so for that one to get registered and to work in the admin panel, we have a custom /logout route (named admin.logout) that handles logging out. It redirects to the 'home' route after logout instead of showing the Filament's logout response.

### Filament's make-user command

As mentioned above, by default, the `filament:make-user` command won't work completely, and instead, you should use the `app:create-admin` one. But if you prefer the Filament's command you can:

Comment out or delete the `/admin/login` route that's currently in routes/web.php. Filament already tries to register the `/admin/login` route -- the one in routes/web.php conflicts with it, so unless you delete the route, Filament won't properly register its own `/admin/login` route (named 'filament.admin.auth.login' by default), and because of that, the `filament:make-user` command won't work right.

Instead of that route, add
- `Route::redirect('/admin/filament/login', '/admin/login')->name('login');` if you're using Livewire
- or `Route::get('/admin/filament/login', fn () => Inertia::location(route('filament.admin.auth.login')))->name('login');` if you're using Inertia.

In AdminPanelProvider, import `App\Http\Controllers\Admin\AuthController`. Then, add `->login([AuthController::class, 'show'])` to the panel's configuration (in the panel() method).

This way, our custom login page will still be used instead of the Filament's one, and the `filament:make-user` command will now work as expected.

### Using Filament's login view

If you prefer Filament's auth page, follow these steps:

Comment out or delete the `/admin/login` route. Filament already tries to register the `/admin/login` route -- the one in routes/web.php conflicts with it, so unless you delete the route, Filament won't properly register its `/admin/login` route.

Instead of that route, add
- `Route::redirect('/admin/filament/login', '/admin/login')->name('login');` if you're using Livewire
- or `Route::get('/admin/filament/login', fn () => Inertia::location(route('filament.admin.auth.login')))->name('login');` if you're using Inertia.

With this redirect route, there'll be no route conflicts, Filament will register its login route (named 'filament.admin.auth.login' by default, but it's path is `/admin/login` -- that's actually causing the conflict that prevents the Filament login route from getting registered), and each `route('admin.login')` call will return the link to that route. This might sound extra, but the application has quite a few of these route calls, so for your app to not blow up with errors, you need a route named 'admin.login', and ideally, it should point to the correct path (`/admin/login`).

Then, in AdminPanelProvider, add `->login()` to the panel's configuration (in the panel() method).

This'll make your app use Filament's login view (+ make the Filament's make-user command work correctly).
