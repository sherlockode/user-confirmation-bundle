Sherlockode User Confirmation Bundle
====================================

## Prerequisites

This version of the bundle requires Symfony 3.3+ and FosUserBundle

## Installation

### Step 1: Install SherlockodeUserConfirmationBundle

The best way to install this bundle is to rely on [Composer](https://getcomposer.org/):

``` bash
$ composer require sherlockode/user-confirmation-bundle
```

### Step 2: Enable the bundle

Enable the bundle in the kernel

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        // ...
        new Sherlockode\UserConfirmationBundle\SherlockodeUserConfirmationBundle(),
    ];
}
```

### Step 3: Configure the bundle

Import the routing in `app/config/routing.yml`

``` yaml
SherlockodeUserConfirmationBundle:
    resource: '@SherlockodeUserConfirmationBundle/Controller/'
    type: annotation
```

Then create the configuration in `app/config/config.yml`

``` yaml
sherlockode_user_confirmation:
    from_email: no-reply@awesome.com                                        # From email address
    redirect_after_confirmation: admin_dashboard                            # The route name to redirect the user
    templates:
        confirmation_form: AppBundle:Registration:confirmation.html.twig    # A wrapper template for the password confirmation form (see below)
```

### Step 4: Wrap the form

You can now set your confirmation form template (see `config.yml`) like this one

``` twig
{% extends "@FOSUser/layout.html.twig" %}

{% block body %}
    <div class="container">
        <div class="row">
            <div class="col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4">
                <div class="form-container">
                    <div>
                        <p class="text-center">Please set your password</p>
                    </div>
                    {% block sherlockode_user_confirmation_form %}{% endblock %}
                </div>
            </div>
        </div>
    </div>
{% endblock %}
```

## Usage

### Send email for a new user

When you create a new user, you can use the mail manager to send the confirmation message.

``` php
<?php

namespace AppBundle\Controller;

use Sherlockode\UserConfirmationBundle\Manager\MailManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller
{
    public function createUser(MailManager $mailManager)
    {
        // Create your user from whatever you want
        // ...
        
        // Send confirmation email
        $mailManager->sendAccountConfirmationEmail($user);
    }
}
```

### Extend the confirmation email

If you want to extend the confirmation email template, just add the path in you `config.yml`

``` yaml
sherlockode_user_confirmation:
    templates:
        confirmation_email: AppBundle:Email:registration.html.twig
```

In this template, you have access to the `user` object, and to a variable named `confirmationUrl` which contains the url to access the confirmation form.
