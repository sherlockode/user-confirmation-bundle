SherlockodeUserConfirmationBundle
=================================

The SherlockodeUserConfirmationBundle provides a way to create a user account that will stay disabled until the
user visits a confirmation link sent by email and sets a password.

## Prerequisites

This version of the bundle requires Symfony 3.* or 4.* and FOSUserBundle

## Installation

### Step 1: Install SherlockodeUserConfirmationBundle

Install with [Composer](https://getcomposer.org/):

```bash
$ composer require sherlockode/user-confirmation-bundle
```

Enable the bundle in the Symfony kernel:

```php
<?php
// config/bundles.php
return [
    // ...
    Sherlockode\UserConfirmationBundle\SherlockodeUserConfirmationBundle::class => ['all' => true],
];
```

### Step 2: Configure the bundle

Import the routing in `config/routes.yaml`

```yaml
sherlockode_user_confirmation:
    resource: "@SherlockodeUserConfirmationBundle/Resources/config/routing.xml"
```

Then create the configuration in `config/packages/sherlockode_user_confirmation.yaml`

```yaml
sherlockode_user_confirmation:
    from_email: no-reply@awesome.com                # From email address
    email_subject: Please confirm your account      # The subject for the confirmation email (optional)
    redirect_after_confirmation: admin_dashboard    # The route name to redirect the user after confirmation
```

## Customization

### Extend the confirmation form template

To extend the confirmation form template, just update your `sherlockode_user_confirmation.yaml`

```yaml
sherlockode_user_confirmation:
    templates:
        confirmation_form: 'Registration/confirmation.html.twig'
```

Then in your template, add a placeholder for the block `sherlockode_user_confirmation_form`

```twig
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
</head>
<body>
    <h1>My awesome app !</h1>
    <div>
        {# The form will be render here #}
        {% block sherlockode_user_confirmation_form %}{% endblock %}
    </div>
</body>
</html>
```

### Extend the confirmation email

If you want to extend the confirmation email template, you should add the path in your `config.yml`

```yaml
sherlockode_user_confirmation:
    templates:
        confirmation_email: 'Email/registration.html.twig'
```

In this template, you have access to the `user` object, and to a variable named `confirmationUrl` which contains the url to access the confirmation form.

### Send confirmation email

If you want to send the confirmation again for an existing user, use the following link : 

```twig
<a href="{{ path('sherlockode_user_confirmation_send_confirmation', {id: userId}) }}">
    Send confirmation email
</a>
```
