# Symfony tutorial

This is a writeup and code example of a course on udemy on the php-framework Symfony which can be found at https://www.udemy.com/course/symfony-php-framework/learn/lecture/21908610#content

## Starting a project

To start a new symfony project, install symfony and use

```
symfony new <name>

```

### Dev server

Symfony has a built in dev server. It can be started using

```
symfony server:start

```

standard port is 8000 so take look at [localhost:8000](localhost:8000) to see if it's working.

When rendering variables in templates, they are safe by default, but if you wish to render some html you can add {{variable | raw}}

## Basic Symfony architecture

Symfony uses an MVC system where you will have different controllers that will represent pages that will output something. That could be templates, JSON, raw HTML etc.

We will map different urls to specific functions within a class that will return something.

The model will be database entries and will be stored in src/Entity/. Symfony recommends using Doctrine module to generate these.

The View will be templates, where the standard is to use the Twig engine, but symfony is loosely coupled and it's easy to use other engines instead of Twig or Doctrine.

## Creating a Controller

Controllers are placed in the src/Controller directory and should return a response object (Symfony\Component\HttpFoundation\Response).

A basic controller would look as follows:

```php
<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FirstController
{
    /**
     * @Route("/first")
     */
    public function homepage(): Response
    {
        return new Response(
            '<html><body><h1>Welcome!<h1></body></html>'
        );
    }
}


```

This controller will return some basic html that can then be viewed at the route defined above the function definition. Routes can be set as shown above or alternatively in the config/routes.yml file as follows

```yml
index:
  path: /somepath
  controller: yourNameSpace\YourController::method
```

## Templating

The default templating engine for Symfony is called Twig and needs to be installed with `composer require twig`

Example twig syntax:

```twig
<div class="someClass">
    {% for item in list %}
    <div>
        {{item.name}} costs {{item.price}}$
    </div>
</div>
```

### Creating a template with Twig

templates are define with syntax as in the above example and could range from something really simple to something more advanced. Twig template names should end with `*.html.twig` e.g. `my-template.html.twig`.

To send data to your templates you need to choose the template in a controller, but rather than loading in the twig library and rendering ourselves we can use Symfonys AbstractController class to make it easier.

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WelcomeController extends AbstractController
{
    /**
     * @Route("/welcome")
     */
    public function homepage(): Response
    {
        return $this->render('welcome.html.twig', [
            "day" => date('l')
        ]);
    }
}
```

The first argument of the render method is the template we wish to use and the second argument is a list of data that we want the template to have access to. We can then access the "day" parameter in the template with `{{day}}`.

Note: If we want to pass raw html or scripts to the template we need to add `{{ dat | raw }}`. This is a security feature since without it it will only render the scripts as written in text.

## Layouts

To avoid rewriting things like head, header and footer etc. in every template we can integrate our templates in a layout. This can be done by creating a layout file and then add {% block content %} to both the layout where you want your template to go, and wrapping your template. By doing this and adding `{% extends "layout.html.twig" %}`to the top of your template the template will use that particular layout.

```twig
{% extends "layout.html.twig" %}

{% block content %}
<h1>Welcome!</h1>
<p>Welcome to our shop. Today is <strong>{{day}}</strong></p>
{% endblock %}
```

For more information on twig look up the [Twig documentation](https://twig.symfony.com/).

## Static Assets

Serving static assets in symfony is done by simply placing them in the public folder. This is useful for things like images, basic css etc.

## Using sass with Symfony

To use sass with Symfony we use the Symfony/encore module, this requires a few steps.

1. Install symfony encore webpack bundle using

```
composer require symfony/webpack-encore-bundle
```

2. Webpack runs on node.js, not php so we need to install all node dependencies using

```
npm install
```

3. Enable sass on line 59 in the webpack.config.js file in the source directory by uncommenting `.enableSassLoader();`

4. In assets/app.js change the import to `app.scss`

5. Install node-sass and sass-loader using as dev dependencies using

```
npm install --save-dev node-sass sass-loader
```

Done!

Now running npm run dev (which uses encore) should compile everything to the public/assets folder.

To use your scss in a template add `{{ encore_entry_link_tags('app')}`. Notice that the file extension is not included.

Another tip to not have to compile your scss every time you make a change, run `npm run watch` while you are editing and it will continously recompile the files.

## Doctrine ORM

Object Relational Mapping - Mapping php objects to database entries, using Doctrine.

Doctrine supports pretty much any database so use any that you are comfortable with.

### Connecting the ORM to a database

Set up a new database using any tool you're comfortable with, like phpmyadmin or Sequel Pro (assuming you have a server running mysql or you're running it locally). You don't need to set up any tables, but you need to know the basic information to access it such as the password, ip/port etc.

Enter this information in the .env file in the root of the project (there are commented lines for each type of database, just uncomment it and add your information)

### Entities

Doctrine will generate Entity PHP-files that will look something like this:

```php
<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    ...
```

Tables in the database will map to these entities, and the tables rows will be instances of this class.

### Making entities with Doctrine

One way to do it is to define the as in the example in the previous section, but the easy way is to use the command

```
php bin/console make:entity
```

Where you will be asked to give the name of the entity followed by entering information about its properties. Fill out all properties you wish your Entity to have and Doctrine will generate a PHP-class and place it in the Entity folder under src.

#### Generating schemas

Now that we have our Entity class we would like to populate our database. Doctrine handles this as well.

Start off by create a migration using the terminal with command

```
php bin/console make:migration
```

This creates a migration file in the migrations directory in the root of the project. This migration file will update tables in the database and can be set up to be automated, but can also be run manually using terminal command

```
php bin/console doctrine:migrations:migrate
```

This runs the migration script and populates the database with your Entities.

### Using entity data in templates

To use the our stored data in a template we can use the repository that doctrine generated for under src/Repository when we made our entity. In a new controller we can we can add a function

```php
use App\Repository\ProductRepository;
...
/**
     * @Route("/<routename>")
     */
    public function somepage(ProductRepository $repo): Response
    {
        $products = $repo->findBy([]); //<-find by e.g. ['name' => 'ProductName1']
        return $this->render('<templatename>.html.twig', [
            "products" => $products
        ]);
    }
```

And in our templates we can do something as follows:

```twig
{% extends "layout.html.twig" %}
{% block content %}
<div>
    {% for product in products %}
    <div>
        {% if product.image %}<a href="/products/{{ product.id }}"><img src="/images/{{ product.image }} " alt=""></a>{% endif %}
        <h2><a href="/products/{{ product.id }}">{{ product.name }}</a></h2>
        ${{ product.price }}
    </div>
    {% endfor %}
{% endblock %}
```

## Symfony Routing

An extension to what we already know about routes is that we can add dynamic routing with slugs or ids e.g.

```php
@Route("/posts/{slug}")
@Route("/posts/{id}", requirements={"id"="\d+"}) //Requires id to be a number
```

We can also limit methods for a route using the methods option, and give it a name that can be used for redirect

```
@Route("/posts/{slug}", methods={"GET", "PUT"}, name="some_name")
```

### Handling not found pages

Symfony has a helpermethod called createNotFoundException that can be used in the controllers.

```php
 /**
     * @Route("/products/{id}")
     */
    public function details($id, ProductRepository $repo): Response
    {
         $product = $repo->find($id);
        if ($product === null) {
            throw $this->createNotFoundException('The product does not exist');
        }
    }
```

The 404 page can be be customized with a twig template but requires some steps.

1. Create a template called error404.html.twigcs

2. Create a folder called bundles in the templates folder

3. Create a folder called TwigBundle in the newly created bundles folder

4. Create a folder called Exception in the TwigBundle folder.

5. Place your template in the Exception folder.

When running a dev server we wont see this page by default, but we can make sure it works by going to [localhost:8000/\_error/404](localhost:8000/_error/404), where we should see the error template

## Adding items to cart

Symfony has built in session handling and its configuration can be found in config/packages/framework.yml. To use it we import the SessionInterface to which we can get and set items

```php
use Symfony\Component\HttpFoundation\Session\SessionInterface;
...
public function details(..., SessionInterface $session)
{
    ...
    $cart = $session->get("cart", []); //gets cart or empty array
    ...
}

```

To add items to the cart we can add a button in the template that makes a post request and handle it in our details function

```php
use Symfony\Component\HttpFoundation\Request;
...
    if ($request->isMethod("POST")) {
        $cart[$product->getId()] = $product;
        $session->set('cart', $cart);
    }
    $isInCart = array_key_exists($bike->getId(), $cart);

    return $this->render("details.html.twig", [
        "product" => $product,
        "inCart" => $isInCart
    ]);
```

To get an overview of the entire implementation you can have a look at these examples from the udemy course:

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\ProductRepository;

class ProductController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function homepage(ProductRepository $repo): Response
    {
        $bikes = $repo->findBy([]); //<-find by e.g. ['name' => 'ProductName1']
        return $this->render('homepage.html.twig', [
            "bikes" => $bikes
        ]);
    }
    /**
     * @Route("/products/{id}")
     */
    public function details($id, Request $request, ProductRepository $repo, SessionInterface $session): Response
    {
        $bike = $repo->find($id);
        if ($bike === null) {
            throw $this->createNotFoundException('The product does not exist');
        }

        // Add to cart logic
        $cart = $session->get("cart", []); //gets cart or empty array
        if ($request->isMethod("POST")) {
            $cart[$bike->getId()] = $bike;
            $session->set('cart', $cart);
        }

        $isInCart = array_key_exists($bike->getId(), $cart);

        return $this->render("details.html.twig", [
            "bike" => $bike,
            "inCart" => $isInCart
        ]);
    }
}
```

And the accompanying template

```twig
{% extends "layout.html.twig" %}

{% block content %}

<div class="breadcrumbs">
    <a href="/">Homepage</a>
</div>
<div class="product-details">
    <div>{% if bike.image %}<img src="/images/{{ bike.image }} " alt=""> {% endif %} </div>
    <div>
        <h1>{{bike.name}}</h1>
        <p>${{ bike.price }}</p>
        <form method="post">
        {% if inCart %}
            <button disabled>Added to basket</button>
        {% else %}
            <button>Add to cart</button>
        {% endif %}
    </div>
</div>
{% if bike.description %}
<p>{{bike.description}}</p>
{% endif %}

{% endblock %}
```

### Creating a cart page

Creating a cart page uses the same methods as we have already used but I'll leave the code here to get the full picture.

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\ProductRepository;

class CartController extends AbstractController
{
    /**
     * @Route("/cart")
     */
    public function details(Request $request, SessionInterface $session): Response
    {
        $cart = $session->get("cart", []);

        if ($request->isMethod("POST")) {
            unset($cart[$request->request->get('id')]);
            $session->set('cart', $cart);
        }

        $total = array_sum(array_map(function ($product) {
            return $product->getPrice();
        }, $cart));

        return $this->render('cart.html.twig', [
            "cart" => $cart,
            "total" => $total
        ]);
    }
}
```

And the template

```twig
{% extends "layout.html.twig" %}

{% block content %}

<div class="breadcrumbs">
    <a href="/">Homepage</a>
</div>
<h1>Cart</h1>

<div class="cart">
    {% for bike in cart %}
        <div>{% if bike.image %}<img src="/images/{{ bike.image }}" alt="">{% endif %}</div>
        <div>{{ bike.name }}</div>
        <div>${{bike.price | number_format(2) }}</div>
        <div>
            <form method="post">
                <button>Remove</button>
                <input type="hidden" name="id" value="{{ bike.id }}">
            </form>
        </div>
    {% endfor %}
    <div></div>
    <div class="total">Total</div>
    <div class="total">${{total | number_format(2) }}</div>
</div>

<form action="checkout" class="cart-form">
    <button>Proceed to Checkout</button>
</form>

{% endblock %}
```

## Forms and validation with Symfony form

Symfony has a standard library for form validation but we need first install it with `composer require symfony/form`(assuming you didn't do a full install when creating your project)

To get to know the form validation we can look at an example where we would like to create a form for a new order of some product. Given an Entity we can create a form using the createFormBuilder-method and add fields to that form

```php
 $form = $this->createFormBuilder($order)
    ->add("name", TextType::class)
    ->add("email", TextType::class)
    ->add("address", TextareaType::class)
    ->add("save", SubmitType::class, ["label" => "Confirm order"])
    ->getForm();
```

and in the return statement we also send this forms createView method to the template

```php
 return $this->render('checkout.html.twig', [
            "form" => $form->createView()
        ]);
```

Given this we can simply add `{{ form(form) }}` where we want it in out template and the form will be automatically created for us, including validation for required fields as defined in the Entity.

### Adding logic to our form

To add logic to our form we can simply handle the post requests as we like in the class definition. In the case of handling an order request it would look something like this.

```php
$form->handleRequest($request);

if ($form->isSubmitted() && $form->isValid()) {
    $order = $form->getData();
    foreach ($cart as $product) {
        //We don't want to add our product directly to the order or else Doctrine will think it's a new order
        //Instead we use find() to get it from the database
        $order->getProducts()->add($repo->find($product->getId()));
    }
    $entityManager = $this->getDoctrine()->getManager(); //gets the entitymanager used to save our order to the db
    $entityManager->persist($order); //Saves our order to the database
    $entityManager->flush(); //tells Doctrine to execute all the sql we have told it to do.

    $session->set("cart", []);

    return $this->render("confirmation.html.twig");
}
```

## Sending emails with Symfony

Symfony uses Symfony mailer, which requires some sort of email account to send your emails from. Mailer simply interacts with that email account. Alternatives for larger scales is some sort of transactional email service like mailgun.

### Configuring the mailservice

The configuration for the mailservice is done in the .env-file under symfony/mailer

After configuring with your DSN we can use the MailerInterface in our controller functions, that takes the mailerinterface as an argument.

```php
$email = (new TemplatedEmail)
    ->from("viktor@example.se")
    ->to(new Address($order->getEmail(), $order->getName()))
    ->subject("Order Confirmation")
    ->htmlTemplate("emails/order.html.twig")
    ->context(["order" => $order]);

$mailer->send($email);
```

This particular example uses Twigs TemplatedEmail method to create a templated email.

For more information on twig or Symfony visit their websites

[Twig Documentation](https://twig.symfony.com/doc/2.x/)
[Symfony](https://symfony.com/doc/current/index.html)

2021-03-26
Viktor Åsbrink
